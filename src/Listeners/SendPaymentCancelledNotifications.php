<?php

namespace Mralston\Payment\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mralston\Payment\Events\PaymentCancelled;

class SendPaymentCancelledNotifications implements ShouldQueue
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
    public function handle(PaymentCancelled $event): void
    {
        Log::debug('Payment #' . $event->payment->id . ' cancelled.');

//        // Notify Customer Care
//        Mail::to([
//            // TODO: Address here...
//        ])->send(new PaymentCancelled($event->payment));
    }
}
