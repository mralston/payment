<?php

namespace Mralston\Payment\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mralston\Payment\Events\PaymentExpired;

class SendPaymentExpiredNotifications implements ShouldQueue
{
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
    public function handle(PaymentExpired $event): void
    {
        Log::debug('Payment #' . $event->payment->id . ' expired.');

//        Mail::to($event->payment->parentable->user)
//            ->send(new PaymentExpired($event->payment));
    }
}
