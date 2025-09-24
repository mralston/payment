<?php

namespace Mralston\Payment\Http\Controllers;

use Exception;
use Illuminate\Http\Client\RequestException;
use Mralston\Payment\Models\Payment;

class FinanceSigningLinkController
{
    public function show(Payment $payment)
    {
        try {
            return [
                'success' => true,
                'error' => null,
                'url' => $payment->paymentProvider
                    ->gateway()
                    ->getSigningUrl($payment)
            ];
        } catch (RequestException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->response->body(),
                'url' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'url' => null,
            ]);
        }
    }
}
