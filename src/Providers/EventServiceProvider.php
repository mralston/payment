<?php

namespace Mralston\Payment\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Mralston\Payment\Events\PaymentCancelled;
use Mralston\Payment\Listeners\NotifyPaymentProviderOfCancellation;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentCancelled::class => [
            NotifyPaymentProviderOfCancellation::class,
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
