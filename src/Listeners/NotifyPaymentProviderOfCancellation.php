<?php

namespace Mralston\Payment\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Mralston\Payment\Events\PaymentCancelled;

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
    }
}
