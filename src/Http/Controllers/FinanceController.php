<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Mralston\Payment\Enums\LookupField;
use Mralston\Payment\Enums\PaymentType as PaymentTypeEnum;
use Mralston\Payment\Http\Requests\SubmitFinanceApplicationRequest;
use Mralston\Payment\Interfaces\FinanceGateway;
use Mralston\Payment\Interfaces\LeaseGateway;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProduct;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Models\PaymentSurvey;
use Mralston\Payment\Models\PaymentType;
use Mralston\Payment\Traits\BootstrapsPayment;
use Mralston\Payment\Traits\RedirectsOnActivePayment;

class FinanceController
{
    use BootstrapsPayment;
    use RedirectsOnActivePayment;

    public function __construct(
        private PaymentHelper $helper,
    ) {
        //
    }

    public function create(Request $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $this->redirectToActivePayment($parentModel);

        $offer = PaymentOffer::findOrFail($request->get('offerId'));
        $survey = $parentModel->paymentSurvey;

        if (!$survey->finance_questions_completed) {
            return redirect()
                ->route('payment.surveys.finance', ['parent' => $parentModel, 'survey' => $survey, 'offerId' => $request->get('offerId')]);
        }

        return Inertia::render('Finance/Create', [
            'parentModel' => $parentModel,
            'survey' => $survey,
            'offer' => $offer->load('paymentProvider'),
            'totalCost' => $this->helper->getTotalCost(),
            'deposit' => $survey->finance_deposit,
            'companyDetails' => $this->helper->getCompanyDetails(),
            'paymentProviders' => PaymentProvider::query()
                ->whereHas('paymentType', function ($query) {
                    $query->whereIdentifier(PaymentTypeEnum::FINANCE->value);
                })
            ->get(),
            'maritalStatuses' => PaymentLookupField::byIdentifier(LookupField::MARITAL_STATUS)
                ->paymentLookupValues,
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
            'residentialStatuses' => PaymentLookupField::byIdentifier(LookupField::RESIDENTIAL_STATUS)
                ->paymentLookupValues,
            'nationalities' => PaymentLookupField::byIdentifier(LookupField::NATIONALITY)
                ->paymentLookupValues,
        ])
            ->withViewData($this->helper->getViewData());
    }

    public function store(SubmitFinanceApplicationRequest $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $this->redirectToActivePayment($parentModel);

        $survey = $parentModel->paymentSurvey;
        $offer = PaymentOffer::findOrFail($request->get('offerId'));

        // Update survey
        $survey
            ->setCustomerProperty(0, 'maritalStatus', $request->get('maritalStatus'))
            ->setCustomerProperty(0, 'homeowner', $request->get('homeowner'))
            ->setCustomerProperty(0, 'mortgage', $request->get('mortgage'))
            ->setCustomerProperty(0, 'britishCitizen', $request->get('britishCitizen'))
            ->save();

        // Create payment
        $payment = Payment::make()
            ->withPaymentType(PaymentType::byIdentifier(PaymentTypeEnum::FINANCE))
            ->withPaymentProduct(PaymentProduct::find($offer->payment_product_id))
            ->withSurvey($survey)
            ->withOffer($offer)
            ->setParent($parentModel)
            ->fill([
                'deposit' => $survey->finance_deposit,
                'eligible' => $request->get('eligible'),
                'gdpr_opt_in' => $request->get('gdprOptIn'),
                'read_terms_conditions' => $request->get('readTermsConditions'),
            ]);
        $payment->save();

        $gateway = $payment->paymentProvider->gateway();

        // Submit the application
        $result = $this->submitApplication($gateway, $payment, $offer, $survey, $parent);

        // Watch the status in the background for a little while and see if it updates
        dispatch(function () use ($payment, $gateway, $offer, $survey, $parent) {
            Log::debug('watching status');
            do {
                // Wait for 3 seconds before each status check.
                sleep(3);

                // Fetch the latest application status.
                $response = $gateway->pollStatus($payment);

                Log::debug('status currently: ', [$response['status']]);

            } while ($response['status'] == 'processing'); // Repeat if still processing.

            Log::debug('status now: ', [$response['status']]);

            // Once the status is no longer 'processing', update the payment record
            Log::debug('updating payment');
            $result = $payment->update([
                'provider_request_data' => $gateway->getRequestData(),
                'provider_response_data' => $gateway->getResponseData(),
                'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
            ]);
            Log::debug('payment updated: ' . $result ? 'success' : 'failure');
        });

        return redirect()
            ->route('payment.finance.show', [
                'parent' => $parent,
                'finance' => $payment
            ]);
    }

    public function show(int $parent, Payment $finance)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);
        $payment = $finance;

        return Inertia::render('Finance/Show', [
            'parentModel' => $parentModel,
            'payment' => $payment->load([
                'paymentProvider',
                'paymentStatus',
            ]),
            'survey' => $payment->paymentOffer->paymentSurvey,
            'offer' => $payment->paymentOffer,
        ])
            ->withViewData($this->helper->getViewData());
    }

    private function submitApplication(FinanceGateway $gateway, Payment $payment, PaymentOffer $offer, PaymentSurvey $survey, int $parent): bool
    {
        try {
            $response = $gateway->apply($payment);
            Log::debug('Apply Response: ', [$response]);
        } catch (\Exception $e) {
            Log::error('Error submitting application: ' . $e->getMessage());
            $payment->update([
                'provider_request_data' => $gateway->getRequestData(),
                'provider_response_data' => $gateway->getResponseData(),
                'submitted_at' => now(),
                'payment_status_id' => PaymentStatus::byIdentifier('error')?->id,
            ]);

            return false;
        }

        if (!$payment->paymentStatus->error) {
            return false;
        }

        // Mark selected offer as submitted (cannot resubmit once an offer has been selected)
        $offer->update([
            'selected' => true,
        ]);

//        // Delete other offers
//        $survey->paymentOffers()
//            ->where('id', '!=', $offer->id)
//            ->where('selected', false)
//            ->delete();

        return true;
    }
}
