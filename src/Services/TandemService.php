<?php

namespace Mralston\Payment\Services;

use Illuminate\Http\Request;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentStatus;
use Illuminate\Support\Facades\Log;

class TandemService
{
    public function handleWebhook(Request $request, string $uuid)
    {
        // Handle webhook verification callback
        if ($request->has('echo')) {
            return $request->query('echo');
        }

        // Load payment. Route model binding on UUID field was
        // failing, so we're doing it manually
        $payment = Payment::firstWhere('uuid', $uuid);
        if (empty($payment)) {
            abort(404);
        }

        // Fetch ID of webhook request
        $webhook_request_id = $request->input('Id');

        Log::channel('payment')->debug(
            'Payment Webhook #' . $webhook_request_id . " received\n" .
            "Lender: Tandem\n" .
            'Payment #' . $payment->id . ': ' . $payment->reference
        );

        // Loop though notifications
        foreach ($request->input('Notifications') as $notification) {
            Log::channel('payment')->debug(print_r($notification, true));

            // Check applicationId is correct
            if ($notification['applicationId'] != $payment->provider_foreign_id) {
                Log::channel('payment')->warning('applicationId Mismatch');
                Log::channel('payment')->info('URI: ' . $request->fullUrl());
                Log::channel('payment')->info(print_r($notification, true));
                Log::channel('payment')->info($payment);
                abort(409, 'applicationId Mismatch');
            }

            // Poll API for updated status
            $gateway = $payment->paymentProvider->gateway();
            $response = $gateway->pollStatus($payment);
            $payment->update([
                'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
            ]);
        }

        return 'thanks';
    }
}
