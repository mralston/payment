<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Mralston\Payment\Enums\LookupField;
use Mralston\Payment\Enums\PaymentType as PaymentTypeEnum;
use Mralston\Payment\Http\Requests\SubmitLeaseApplicationRequest;
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

class LeaseController
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

        if (!$survey->basic_questions_completed) {
            return redirect()
                ->route('payment.surveys.lease', ['parent' => $parentModel, 'survey' => $survey, 'offerId' => $request->get('offerId')]);
        }

        return Inertia::render('Lease/Create', [
            'parentModel' => $parentModel,
            'survey' => $parentModel->paymentSurvey,
            'offer' => $offer->load('paymentProvider'),
            'totalCost' => $this->helper->getTotalCost(),
            'deposit' => $this->helper->getDeposit(),
            'companyDetails' => $this->helper->getCompanyDetails(),
            'lenders' => PaymentProvider::all(),
            'employmentStatuses' => PaymentLookupField::byIdentifier(LookupField::EMPLOYMENT_STATUS)
                ->paymentLookupValues,
        ])
            ->withViewData($this->helper->getViewData());
    }

    public function store(SubmitLeaseApplicationRequest $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $this->redirectToActivePayment($parentModel);

        $survey = $parentModel->paymentSurvey;
        $offer = PaymentOffer::findOrFail($request->get('offerId'));

        // Create payment
        $payment = Payment::make()
            ->withPaymentType(PaymentType::byIdentifier(PaymentTypeEnum::LEASE))
            ->withPaymentProduct(PaymentProduct::find($offer->payment_product_id))
            ->withSurvey($survey)
            ->withOffer($offer)
            ->setParent($parentModel)
            ->fill([
                'deposit' => $this->helper->getDeposit(),
                'eligible' => $request->get('eligible'),
                'gdpr_opt_in' => $request->get('gdprOptIn'),
                'read_terms_conditions' => $request->get('readTermsConditions'),
                'bank_account_number' => $request->get('accountNumber'),
                'bank_account_sort_code' => $request->get('sortCode'),
            ]);
        $payment->save();

        $gateway = $payment->paymentProvider->gateway();

        try {
            // Fetch the application from the provider
            $application = $gateway->getApplication($offer->provider_application_id);

            // See whether we need to send applicants (this happens when the survey is skipped)
            if ($application['status'] == 'pending-applicants') {
                // Send the applicants
                $response = $gateway->updateApplication($survey, $offer->provider_application_id);

                $result = $payment->update([
                    'provider_request_data' => $gateway->getRequestData(),
                    'provider_response_data' => $gateway->getResponseData(),
                    'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
                ]);

                Log::debug('update result: ' . $result ? 'success' : 'failure');
            }
        } catch (\Exception $e) {
            $payment->update([
                'provider_request_data' => $gateway->getRequestData(),
                'provider_response_data' => $gateway->getResponseData(),
                'submitted_at' => now(),
                'payment_status_id' => PaymentStatus::byIdentifier('error')?->id,
            ]);

            return redirect()
                ->route('payment.lease.show', [
                    'parent' => $parent,
                    'lease' => $payment,
                ]);
        }

        // Check the status
        $response = $gateway->getApplication($offer->provider_application_id);
        $payment->update([
            'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
        ]);

        // Decide whether to submit immediately or wait in the background until ready
        if ($response['status'] == 'processing') {
            Log::debug('backgrounding');
            // Need to wait until it's ready. We'll do that in the background
            dispatch(function () use ($payment, $gateway, $offer, $survey, $parent) {
                do {
                    // Wait for 3 seconds before each status check.
                    sleep(3);

                    // Fetch the latest application status.
                    $response = $gateway->getApplication($offer->provider_application_id);

//                    Log::debug('Raw payment status: ', [$response['status']]);

                    $payment->update([
                        'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
                    ]);

                    Log::debug('Payment status: ', $payment->paymentStatus->toArray());

                } while ($response['status'] == 'processing'); // Repeat if still processing.

                // Once the status is no longer 'processing', proceed to submit.
                $result = $this->submitApplication($gateway, $payment, $offer, $survey, $parent);
            });
        } else {
            Log::debug('foregrounding');
            // Application is ready. Submit it now
            $result = $this->submitApplication($gateway, $payment, $offer, $survey, $parent);
        }

        // TODO: Figure out why the background status check isn't firing an event for Echo

        // Watch the status in the background for a little while and see if it updates
        dispatch(function () use ($payment, $gateway, $offer, $survey, $parent) {
            do {
                // Wait for 3 seconds before each status check.
                sleep(3);

                // Fetch the latest application status.
                $response = $application = $gateway->getApplication($offer->provider_application_id);

            } while ($response['status'] == 'processing'); // Repeat if still processing.

            // Once the status is no longer 'processing', update the payment record
            $payment->update([
                'provider_request_data' => $gateway->getRequestData(),
                'provider_response_data' => $gateway->getResponseData(),
                'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
            ]);
        });

        return redirect()
            ->route('payment.lease.show', [
                'parent' => $parent,
                'lease' => $payment,
            ]);
    }

    public function show(int $parent, Payment $lease)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);
        $payment = $lease;

        return Inertia::render('Lease/Show', [
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

    private function submitApplication(LeaseGateway $gateway, Payment $payment, PaymentOffer $offer, PaymentSurvey $survey, int $parent): bool
    {
        try {
            $response = $gateway->apply($payment);
            Log::debug('Select Response: ', $response);
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

        // Update payment with response
        $result = $payment->update([
            'provider_request_data' => $gateway->getRequestData(),
            'provider_response_data' => $gateway->getResponseData(),
            'submitted_at' => now(),
            'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
            ...(
            empty($payment->reference) ?
                ['reference' => $response['reference'] ?? null] :
                []
            ),
        ]);

        Log::debug('update after submit result: ' . $result ? 'success' : 'failure');

        // Mark selected offer as submitted (cannot resubmit once an offer has been selected)
        $offer->update([
            'selected' => true,
        ]);

        // Delete other offers
        $survey->paymentOffers()
            ->where('id', '!=', $offer->id)
            ->where('selected', false)
            ->delete();

        return true;
    }
}
