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
use Mralston\Payment\Http\Middleware\RedirectToActivePayment;

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
                ->middleware(RedirectToActivePayment::class)
                ->name('start');

            /**
             * Shows a 'locked' page if the payment process is blocked for some reason
             */
            Route::get('{parent}/locked', [PaymentController::class, 'locked'])
                ->name('locked');

            Route::get('{parent}/surveys/{survey}/lease', [SurveyController::class, 'lease'])
                ->middleware(RedirectToActivePayment::class)
                ->name('surveys.lease');

            Route::get('{parent}/surveys/{survey}/finance', [SurveyController::class, 'finance'])
                ->middleware(RedirectToActivePayment::class)
                ->name('surveys.finance');

            Route::resource('{parent}/surveys', SurveyController::class)
                ->middlewareFor(['create', 'store', 'edit', 'finance', 'update'], RedirectToActivePayment::class)
                ->names('surveys');

            Route::get('{parent}/options', [PaymentOptionsController::class, 'options'])
                ->middleware(RedirectToActivePayment::class)
                ->name('options');

            Route::post('{parent}/change-desposit/{paymentType}', [PaymentOptionsController::class, 'changeDeposit'])
                ->name('change-deposit');

            Route::post('{parent}/select', [PaymentOptionsController::class, 'select'])
                ->middleware(RedirectToActivePayment::class)
                ->name('select');

            Route::post('{parent}/unselect', [PaymentOptionsController::class, 'unselect'])
                ->middleware(RedirectToActivePayment::class)
                ->name('unselect');

            Route::post('{parent}/cancel/{payment}', [PaymentController::class, 'cancel'])
                ->name('cancel');

            Route::post('{parent}/prequal', PrequalController::class)
                ->name('prequal');

            Route::resource('{parent}/cash', CashController::class)
                ->middlewareFor(['create'], RedirectToActivePayment::class)
                ->names('cash');

            Route::resource('{parent}/finance', FinanceController::class)
                ->middlewareFor(['store'], RedirectToActivePayment::class)
                ->names('finance');

            Route::resource('{parent}/lease', LeaseController::class)
                ->middlewareFor(['create', 'store'], RedirectToActivePayment::class)
                ->names('lease');

            Route::get('address/lookup/{postCode}', [AddressLookupController::class, 'lookup'])
                ->name('address.lookup');

            Route::get('finance/{payment}/signing-link', [FinanceSigningLinkController::class, 'show'])
                ->name('finance.signing-link');

            Route::post('webhook/tandem', [WebhookController::class, 'tandem'])
                ->name('webhook.tandem');
    });
});
