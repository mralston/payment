<?php

namespace Mralston\Payment\Http\Controllers;

use Inertia\Inertia;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Traits\BootstrapsPayment;
use Mralston\Payment\Traits\RedirectsOnActivePayment;

class CashController
{
    use BootstrapsPayment;
    use RedirectsOnActivePayment;

    public function __construct(
        private PaymentHelper $helper,
    ) {
        //
    }

    public function create(int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $this->redirectToActivePayment($parentModel);

        return Inertia::render('Cash/Create', [
            'totalCost' => $this->helper->getTotalCost(),
            'deposit' => $this->helper->getDeposit(),
        ])
            ->withViewData($this->helper->getViewData());
    }
}
