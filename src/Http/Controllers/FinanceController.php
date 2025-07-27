<?php

namespace Mralston\Payment\Http\Controllers;

use Inertia\Inertia;
use Mralston\Payment\Interfaces\PaymentHelper;

class FinanceController
{
    public function __construct(
        private PaymentHelper $helper,
    ) {
        //
    }

    public function create()
    {
        return Inertia::render('Finance/Create')
            ->withViewData($this->helper->getViewData());
    }
}
