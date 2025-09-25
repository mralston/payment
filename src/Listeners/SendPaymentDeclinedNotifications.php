<?php

namespace Mralston\Payment\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mralston\Payment\Events\PaymentDeclined;
use Mralston\Payment\Mail\PaymentDeclined as PaymentDeclinedMailable;

class SendPaymentDeclinedNotifications implements ShouldQueue
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
    public function handle(PaymentDeclined $event): void
    {
        Log::debug('Payment #' . $event->payment->id . ' declined.');

        // Notify rep
        Mail::to($event->payment->parentable?->user)
            ->send(new PaymentDeclinedMailable($event->payment));
    }
}
