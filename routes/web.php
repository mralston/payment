<?php

use Illuminate\Support\Facades\Route;
use Mralston\Finance\Http\Controllers\FinanceController;

Route::prefix('finance')->group(function () {

    Route::get('/', [FinanceController::class, 'index']);

});


