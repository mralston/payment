<?php

namespace Mralston\Payment\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Mralston\Payment\Events\PaymentCancelled;
use Mralston\Payment\Interfaces\Apiable;

class NotifyPaymentProviderOfCancellation implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentCancelled $event): void
    {
        $gateway = $event->payment->paymentProvider->gateway();

        $gateway->cancel($event->payment, $event->reason);

        if ($gateway instanceof Apiable) {
            $event->payment->last_cancellation->update([
                'lender_response_data' => $gateway->getCancellationResponse(),
            ]);
        }
    }
}
