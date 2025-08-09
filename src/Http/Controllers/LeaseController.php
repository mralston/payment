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
use Mralston\Payment\Models\PaymentType;
use Mralston\Payment\Traits\BootstrapsPayment;

class LeaseController
{
    use BootstrapsPayment;

    public function __construct(
        private PaymentHelper $helper,
    ) {
        //
    }

    public function create(Request $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

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
        $survey = $parentModel->paymentSurvey;
        $offer = PaymentOffer::findOrFail($request->get('offerId'));

        dump($request->all());

        // TODO: Create payment
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

            ])
            ->save();

        dd($payment);

        // TODO: Submit payment to provider

        return redirect()
            ->route('payment.finance.show', [
                'parent' => $parent,
                'finance' => $payment
            ]);







        // TODO: Update survey

        // TODO: Submit application

        return redirect()
            ->route('payment.lease.show', [
                'parent' => $parent,
                'lease' => 1 // TODO: include application ID
            ]);
    }

    public function show(int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        return Inertia::render('Lease/Show', [
            'parentModel' => $parentModel,
            // TODO: Application info here
        ])
            ->withViewData($this->helper->getViewData());
    }
}
