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

            Log::debug('status currently: ', [$response['status']]);
        } while ($response['status'] == 'processing'); // Repeat if still processing.

        Log::debug('status now: ', [$response['status']]);

        // Once the status is no longer 'processing', update the payment record
        Log::debug('updating payment');
        $result = $payment->update([
            'provider_request_data' => $gateway->getRequestData(),
            'provider_response_data' => $gateway->getResponseData(),
            'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
        ]);
        Log::debug('payment updated: ' . $result ? 'success' : 'failure');
    }
}
