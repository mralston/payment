<?php

namespace Mralston\Finance\Http\Controllers;

use Inertia\Inertia;
use Mralston\Finance\Interfaces\FinanceParentModel;

class FinanceController
{
    public function index()
    {
        return Inertia::render('Finance/Index');
    }

    public function chooseMethod(int $parent)
    {
        $parentModel = app(config('finance.parent_model'))->findOrFail($parent);

        return Inertia::render('Finance/ChooseMethod', [
            'parentModel' => $parentModel,
        ]);
    }
}
