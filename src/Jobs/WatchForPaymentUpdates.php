<?php

namespace Mralston\Payment\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentStatus;

class WatchForPaymentUpdates implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $paymentId,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug('watching status');

        $payment = Payment::findOrFail($this->paymentId);
        $gateway = $payment->paymentProvider->gateway();

        do {
            // Wait for 3 seconds before each status check.
            sleep(3);

            // Fetch the latest application status.
            $response = $gateway->pollStatus($payment);

            Log::debug('poll status result: ', $response);

            Log::debug('status currently: ', [$response['status']]);
        } while ($response['status'] == 'processing'); // Repeat if still processing.

        Log::debug('status now: ', [$response['status']]);

        // Once the status is no longer 'processing', update the payment record
        Log::debug('updating payment');
        Log::debug('updating payment', $gateway->getResponseData() ?? []);
        $update = [
            'provider_response_data' => $gateway->getResponseData(),
            'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
        ];

        // Only overwrite provider_request_data if the gateway supplies new request data
        if (!is_null($gateway->getRequestData())) {
            $update['provider_request_data'] = $gateway->getRequestData();
        }

        $result = $payment->update($update);
        Log::debug('payment updated: ' . $result ? 'success' : 'failure');
    }
}
