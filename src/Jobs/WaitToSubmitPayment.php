<?php

namespace Mralston\Payment\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Interfaces\LeaseGateway;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Models\PaymentSurvey;
use Mralston\Payment\Services\LeaseService;

class WaitToSubmitPayment implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Payment $payment,
        protected PaymentOffer $offer,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(
        LeaseService $leaseService
    ): void {
        $gateway = $this->payment->paymentProvider->gateway();
        $parent = $this->payment->parentable;
        $survey = $parent->paymentSurvey;

        do {
            // Wait for 3 seconds before each status check.
            sleep(3);

            // Fetch the latest application status.
            $response = $gateway->getApplication($this->offer->provider_application_id);

//                    Log::debug('Raw payment status: ', [$response['status']]);

            $this->payment->update([
                'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
            ]);

            Log::debug('Payment status: ', $this->payment->paymentStatus->toArray());

        } while ($response['status'] == 'processing'); // Repeat if still processing.

        // Once the status is no longer 'processing', proceed to submit.
        $result = $leaseService->submitApplication($gateway, $this->payment, $this->offer, $survey, $parent);
    }
}
