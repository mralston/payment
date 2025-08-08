<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Traits\BootstrapsPayment;

class FinanceController
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

        return Inertia::render('Finance/Create', [
            'parentModel' => $parentModel,
            'survey' => $parentModel->paymentSurvey,
            'offer' => $offer,
            'totalCost' => $this->helper->getTotalCost(),
            'deposit' => $this->helper->getDeposit(),
            'companyDetails' => $this->helper->getCompanyDetails(),
            'lenders' => PaymentProvider::all(),
        ])
            ->withViewData($this->helper->getViewData());
    }

    public function store(Request $request, int $parent)
    {
        // TODO: Update survey

        // TODO: Submit application

        return redirect()
            ->route('payment.finance.show', [
                'parent' => $parent,
                'finance' => 1 // TODO: include application ID
            ]);
    }

    public function show(int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        return Inertia::render('Finance/Show', [
            'parentModel' => $parentModel,
            // TODO: Application info here
        ])
            ->withViewData($this->helper->getViewData());
    }
}
