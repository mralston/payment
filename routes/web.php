<?php

use Illuminate\Support\Facades\Route;
use Mralston\Payment\Http\Controllers\AddressLookupController;
use Mralston\Payment\Http\Controllers\CashController;
use Mralston\Payment\Http\Controllers\FinanceController;
use Mralston\Payment\Http\Controllers\LeaseController;
use Mralston\Payment\Http\Controllers\PaymentController;
use Mralston\Payment\Http\Controllers\PaymentOptionsController;
use Mralston\Payment\Http\Controllers\SurveyController;
use Mralston\Payment\Http\Controllers\PrequalController;
use Mralston\Payment\Http\Controllers\FinanceSigningLinkController;
use Mralston\Payment\Http\Controllers\WebhookController;

Route::group(['middleware' => ['web', 'auth']], function () {

    Route::resource('payments', PaymentController::class);

    Route::prefix('payment')
        ->name('payment.')
        ->group(function () {


            /**
             * Entrypoint to the payment journey. Serves as a standard jumping in point for the parent
             * application, which won't necessarily understand the inner workings of the payment journey's routes.
             */
            Route::get('{parent}', [PaymentController::class, 'start'])
                ->name('start');

            Route::get('{parent}/locked', [PaymentController::class, 'locked'])
                ->name('locked');

            Route::get('{parent}/surveys/{survey}/lease', [SurveyController::class, 'lease'])
                ->name('surveys.lease');

            Route::get('{parent}/surveys/{survey}/finance', [SurveyController::class, 'finance'])
                ->name('surveys.finance');

            Route::resource('{parent}/surveys', SurveyController::class)
                ->names('surveys');

            Route::get('{parent}/options', [PaymentOptionsController::class, 'options'])
                ->name('options');

            Route::post('{parent}/change-desposit/{paymentType}', [PaymentOptionsController::class, 'changeDeposit'])
                ->name('change-deposit');

            Route::post('{parent}/select', [PaymentOptionsController::class, 'select'])
                ->name('select');

            Route::post('{parent}/unselect', [PaymentOptionsController::class, 'unselect'])
                ->name('unselect');

            Route::post('{parent}/cancel/{payment}', [PaymentController::class, 'cancel'])
                ->name('cancel');

            Route::post('{parent}/prequal', PrequalController::class)
                ->name('prequal');

            Route::resource('{parent}/cash', CashController::class)
                ->names('cash');

            Route::resource('{parent}/finance', FinanceController::class)
                ->names('finance');

            Route::resource('{parent}/lease', LeaseController::class)
                ->names('lease');

            Route::get('address/lookup/{postCode}', [AddressLookupController::class, 'lookup'])
                ->name('address.lookup');

            Route::get('finance/{payment}/signing-link', [FinanceSigningLinkController::class, 'show'])
                ->name('finance.signing-link');

            Route::post('webhook/tandem', [WebhookController::class, 'tandem'])
                ->name('webhook.tandem');
    });
});
