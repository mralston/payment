<?php

namespace Mralston\Payment\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Mralston\Payment\Events\PaymentAccepted;
use Mralston\Payment\Events\PaymentCancelled;
use Mralston\Payment\Events\PaymentConditionallyAccepted;
use Mralston\Payment\Events\PaymentDeclined;
use Mralston\Payment\Events\PaymentExpired;
use Mralston\Payment\Events\PaymentParked;
use Mralston\Payment\Events\PriceChanged;
use Mralston\Payment\Events\SatNoteUploaded;
use Mralston\Payment\Listeners\NotifyPaymentProviderOfCancellation;
use Mralston\Payment\Listeners\SendPaymentAcceptedNotifications;
use Mralston\Payment\Listeners\SendPaymentCancelledNotifications;
use Mralston\Payment\Listeners\SendPaymentDeclinedNotifications;
use Mralston\Payment\Listeners\SendPaymentExpiredNotifications;
use Mralston\Payment\Listeners\SendPaymentParkedNotifications;
use Mralston\Payment\Listeners\SendSatNoteToPaymentProvider;
use Mralston\Payment\Listeners\UpdateCashDeposit;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentCancelled::class => [
            // TODO: send payment cancellation to lender synchronously, so they can reject
            NotifyPaymentProviderOfCancellation::class,
            // TODO: then implement this to send notifications
//            SendPaymentCancelledNotifications::class,
        ],
        PriceChanged::class => [
            UpdateCashDeposit::class,
        ],
        PaymentAccepted::class => [
            SendPaymentAcceptedNotifications::class,
        ],
        PaymentConditionallyAccepted::class => [
            SendPaymentAcceptedNotifications::class,
        ],
        PaymentDeclined::class => [
            SendPaymentDeclinedNotifications::class,
        ],
        PaymentExpired::class => [
            SendPaymentExpiredNotifications::class,
        ],
        PaymentParked::class => [
            SendPaymentParkedNotifications::class,
        ],
        SatNoteUploaded::class => [
            SendSatNoteToPaymentProvider::class,
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
