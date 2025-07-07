<?php

namespace Mralston\Finance\Http\Controllers;

use Inertia\Inertia;
use Mralston\Finance\Interfaces\FinanceHelper;
use Mralston\Finance\Interfaces\FinanceParentModel;
use Mralston\Finance\Traits\BootstrapsFinance;

class FinanceController
{
    use BootstrapsFinance;

    public function index()
    {
        return Inertia::render('Finance/Index');
    }

    public function choosePaymentOption(int $parent, FinanceHelper $helper)
    {
        $parentModel = $this->bootstrap($parent, $helper);

        return Inertia::render('Finance/ChoosePaymentOption', [
            'parentModel' => $parentModel,
            'customers' => $helper->getCustomers(),
        ])->withViewData($helper->getViewData());
    }
}
