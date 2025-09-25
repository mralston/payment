<?php

namespace Mralston\Payment\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mralston\Payment\Events\PaymentAccepted;
use Mralston\Payment\Events\PaymentConditionallyAccepted;
use Mralston\Payment\Mail\PaymentAccepted as PaymentAcceptedMailable;
use Mralston\Payment\Mail\RemoteSign;

class SendPaymentAcceptedNotifications implements ShouldQueue
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
    public function handle(PaymentAccepted|PaymentConditionallyAccepted $event): void
    {
        Log::debug('Payment #' . $event->payment->id . ' accepted.');

        // Notify rep
        Mail::to($event->payment->parentable->user)
            ->send(new PaymentAcceptedMailable($event->payment));

        // Prep customer object
        $customer = (object)[
            'name' => $event->payment->first_name . ' ' . $event->payment->last_name,
            'email' => $event->payment->email_address,
        ];

        // Send signing link to customer
        Mail::to($customer)
            ->send(new RemoteSign($event->payment));
    }
}
