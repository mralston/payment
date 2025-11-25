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
        Log::channel('payment')->debug('watching status');

        $payment = Payment::findOrFail($this->paymentId);
        $gateway = $payment->paymentProvider->gateway();

        do {
            // Wait for 3 seconds before each status check.
            sleep(3);

            // Fetch the latest application status.
            $response = $gateway->pollStatus($payment);

            Log::channel('payment')->debug('poll status result: ', $response);

            Log::channel('payment')->debug('status currently: ', [$response['status']]);
        } while ($response['status'] == 'processing' || $response['status'] == 'pending'); // Repeat if still processing.

        Log::channel('payment')->debug('status now: ', [$response['status']]);

        // Once the status is no longer 'processing', update the payment record
        Log::channel('payment')->debug('updating payment');
        Log::channel('payment')->debug('updating payment', $gateway->getResponseData() ?? []);
        $update = [
            'provider_request_data' => $gateway->getRequestData() ?? $payment->provider_request_data,
            'provider_response_data' => $gateway->getResponseData() ?? $payment->provider_response_data,
            'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
        ];

        $result = $payment->update($update);
        Log::channel('payment')->debug('payment updated: ' . $result ? 'success' : 'failure');
    }
}
