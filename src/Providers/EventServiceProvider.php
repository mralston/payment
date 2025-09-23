<?php

namespace Mralston\Payment\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Mralston\Payment\Events\PaymentCancelled;
use Mralston\Payment\Events\PriceChanged;
use Mralston\Payment\Listeners\NotifyPaymentProviderOfCancellation;
use Mralston\Payment\Listeners\UpdateCashDeposit;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentCancelled::class => [
            NotifyPaymentProviderOfCancellation::class,
        ],
        PriceChanged::class => [
            UpdateCashDeposit::class,
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
