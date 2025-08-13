<?php

namespace Mralston\Payment\Http\Controllers;

use Mralston\Payment\Interfaces\Signable;
use Mralston\Payment\Models\Payment;

class FinanceSigningLinkController
{
    public function show(Payment $payment)
    {
        //test
        return [
            'success' => true,
            'error' => null,
            'url' => 'https://www.google.com'
        ];
        try {
            return [
                'success' => true,
                'error' => null,
                'url' => $payment->paymentProvider
                    ->gateway()
                    ->getSigningUrl($payment)
            ];
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'url' => null,
            ]);
        }
    }
}