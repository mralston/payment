<?php

namespace Mralston\Payment\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Mralston\Payment\Events\PaymentParked;
use Mralston\Payment\Mail\PaymentParked as PaymentParkedMailable;

class SendPaymentParkedNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentParked $event): void
    {
        // Notify rep
        Mail::to($event->payment->parentable->user)
            ->send(new PaymentParkedMailable($event->payment));
    }
}
