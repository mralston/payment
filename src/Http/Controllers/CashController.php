<?php

namespace Mralston\Payment\Http\Controllers;

use Inertia\Inertia;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Traits\BootstrapsPayment;

class CashController
{
    use BootstrapsPayment;

    public function __construct(
        private PaymentHelper $helper,
    ) {
        //
    }

    public function create(int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        return Inertia::render('Cash/Create', [
            'parentModel' => $parentModel,
            'totalCost' => $this->helper->getTotalCost(),
            'deposit' => $parentModel->paymentSurvey->cash_deposit,
            'canChangePaymentMethod' => $this->helper->canChangePaymentMethod(),
        ])
            ->withViewData($this->helper->getViewData());
    }
}
