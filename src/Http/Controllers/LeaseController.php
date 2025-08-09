<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Mralston\Payment\Enums\LookupField;
use Mralston\Payment\Enums\PaymentType as PaymentTypeEnum;
use Mralston\Payment\Http\Requests\SubmitLeaseApplicationRequest;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProduct;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentStatus;
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

        $this->checkForActivePayment($parentModel);

        $offer = PaymentOffer::findOrFail($request->get('offerId'));

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

        $this->checkForActivePayment($parentModel);

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

        // Submit payment to provider
        try {
            $response = $gateway->apply($payment);
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

        // Update payment with response
        $payment->update([
            'provider_request_data' => $gateway->getRequestData(),
            'provider_response_data' => $gateway->getResponseData(),
            'submitted_at' => now(),
            'payment_status_id' => PaymentStatus::byIdentifier('pending')?->id,
            ...(
                empty($payment->reference) ? ['reference' => $gateway->getResponseData()['reference'] ?? null] :
                []
            ),
        ]);

        // Mark offers as submitted (cannot resubmit once an offer has been selected)

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
            'response' => $payment->provider_response_data,
            'survey' => $payment->paymentOffer->paymentSurvey,
            'offer' => $payment->paymentOffer,
        ])
            ->withViewData($this->helper->getViewData());
    }
}
