<?php

namespace Mralston\Finance\Http\Controllers;

use Inertia\Inertia;

class FinanceController
{
    public function index()
    {
        return Inertia::render('Finance/Index');
    }
}
