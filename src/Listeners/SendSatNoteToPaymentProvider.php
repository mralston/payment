<?php

namespace Mralston\Payment\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Mralston\Payment\Events\PriceChanged;
use Mralston\Payment\Events\SatNoteUploaded;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\WantsSatNote;
use Mralston\Payment\Traits\BootstrapsPayment;

class SendSatNoteToPaymentProvider implements ShouldQueue
{
    use BootstrapsPayment;

    /**
     * Create the event listener.
     */
    public function __construct(
        private PaymentHelper $helper,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SatNoteUploaded $event): void
    {
        if (!$event->payment->paymentProvider->gateway() instanceof WantsSatNote) {
            return;
        }

        $event
            ->payment
            ->paymentProvider
            ->gateway()
            ->sendSatNote($event->payment);
    }
}
