<?php

use Illuminate\Support\Facades\Route;
use Mralston\Payment\Http\Controllers\PaymentController;
use Mralston\Payment\Http\Controllers\PaymentSurveyController;
use Mralston\Payment\Http\Controllers\PrequalController;

Route::group(['middleware' => ['web', 'auth']], function () {

    Route::resource('payments', PaymentController::class);

    Route::prefix('payment')
        ->name('payment.')
        ->group(function () {

        Route::resource('{parent}/surveys', PaymentSurveyController::class)
            ->names('surveys');

        Route::get('{parent}/choose-payment-option', [PaymentController::class, 'choosePaymentOption'])
            ->name('choose-payment-option');

        Route::post('{parent}/prequal', PrequalController::class)
            ->name('prequal');
    });

});
