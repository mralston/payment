<?php

use Illuminate\Support\Facades\Route;
use Mralston\Payment\Http\Controllers\PaymentController;
use Mralston\Payment\Http\Controllers\PaymentSurveyController;

Route::group(['middleware' => ['web', 'auth']], function () {

    Route::resource('payment.surveys', PaymentSurveyController::class)
        ->parameters(['payment' => 'parent']);

    Route::prefix('payment')->group(function () {

        Route::get('/', [PaymentController::class, 'index']);

        Route::get('{parent}/choose-payment-option', [PaymentController::class, 'choosePaymentOption'])
            ->name('payment.choose-payment-option');

        Route::post('{parent}/prequal', [PaymentController::class, 'prequal']);

    });

    Route::resource('payments', PaymentController::class);
});
