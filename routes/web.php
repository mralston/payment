<?php

use Illuminate\Support\Facades\Route;
use Mralston\Payment\Http\Controllers\CashController;
use Mralston\Payment\Http\Controllers\FinanceController;
use Mralston\Payment\Http\Controllers\LeaseController;
use Mralston\Payment\Http\Controllers\PaymentController;
use Mralston\Payment\Http\Controllers\SurveyController;
use Mralston\Payment\Http\Controllers\PrequalController;

Route::group(['middleware' => ['web', 'auth']], function () {

    Route::resource('payments', PaymentController::class);

    Route::prefix('payment')
        ->name('payment.')
        ->group(function () {

        Route::resource('{parent}/surveys', SurveyController::class)
            ->names('surveys');

        Route::get('{parent}/options', [PaymentController::class, 'options'])
            ->name('options');

        Route::post('{parent}/prequal', PrequalController::class)
            ->name('prequal');

        Route::resource('{parent}/cash', CashController::class)
            ->names('cash');

        Route::resource('{parent}/finance', FinanceController::class)
            ->names('finance');

        Route::resource('{parent}/lease', LeaseController::class)
            ->names('lease');
    });

});
