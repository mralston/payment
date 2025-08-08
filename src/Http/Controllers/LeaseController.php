<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Mralston\Payment\Enums\LookupField;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProvider;
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

    public function store(Request $request, int $parent)
    {
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
