<?php

namespace Mralston\Payment;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Mralston\Payment\Integrations\Hometree;
use Mralston\Payment\Integrations\Propensio;
use Mralston\Payment\Integrations\Tandem;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Providers\EventServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'payment');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/channels.php');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('payment.php'),
            ], 'payment-config');

            $this->publishes([
                __DIR__ . '/../database/seeders' => database_path('seeders'),
            ], 'payment-seeders');

            $this->publishes([
                __DIR__.'/../public/vendor/mralston/payment/build' => public_path('vendor/mralston/payment/build'),
            ], 'payment-assets');
        }

        // Conditionally set Inertia root view based on config
        if ($rootView = config('payment.inertia_root_view')) {
            Inertia::setRootView($rootView);
        } else {
            // Default package root view
            Inertia::setRootView('payment::app');
        }

        $this->app->bind(PaymentHelper::class, config('payment.helper'));
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'payment');

        $this->app->register(EventServiceProvider::class);

        $this->app->singleton(Tandem::class, function ($app) {
            return new Tandem(
                config('payment.tandem.api_key'),
                config('payment.tandem.endpoint'),
            );
        });

        $this->app->singleton(Propensio::class, function ($app) {
            return new Propensio(
                config('payment.propensio.ibc_ref'),
                config('payment.propensio.endpoint'),
            );
        });

        $this->app->singleton(Hometree::class, function ($app) {
            return new Hometree(
                config('payment.hometree.api_key'),
                config('payment.hometree.endpoint'),
            );
        });

        $this->app->singleton(PaymentHelper::class, function ($app) {
            return app(config('payment.helper'));
        });
    }
}
