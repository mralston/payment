<?php

use Illuminate\Support\Facades\Route;
use Mralston\Finance\Http\Controllers\FinanceController;
use Mralston\Finance\Http\Controllers\FinanceSurveyController;

Route::group(['middleware' => ['web', 'auth']], function () {

    Route::resource('finance.surveys', FinanceSurveyController::class)
        ->parameters(['finance' => 'parent']);

    Route::prefix('finance')->group(function () {

        Route::get('/', [FinanceController::class, 'index']);

        Route::get('{parent}/choose-method', [FinanceController::class, 'chooseMethod'])->name('finance.choose-method');

    });

});
