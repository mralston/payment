<?php

namespace Mralston\Payment\Http\Controllers;

use Inertia\Inertia;
use Mralston\Payment\Interfaces\PaymentHelper;

class LeaseController
{
    public function __construct(
        private PaymentHelper $helper,
    ) {
        //
    }

    public function create()
    {
        return Inertia::render('Lease/Create')
            ->withViewData($this->helper->getViewData());
    }
}
