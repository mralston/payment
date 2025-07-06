<?php

namespace Mralston\Finance\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Mralston\Finance\Interfaces\FinanceHelper;

class FinanceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'finance');

        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('finance.php'),
            ], 'finance-config');

            $this->publishes([
                __DIR__.'/../../public/vendor/mralston/finance/build' => public_path('vendor/mralston/finance/build'),
            ], 'finance-assets');
        }

        // Conditionally set Inertia root view based on config
        if ($rootView = config('finance.inertia_root_view')) {
            Inertia::setRootView($rootView);
        } else {
            // Default package root view
            Inertia::setRootView('finance::app');
        }

        $this->app->bind(FinanceHelper::class, config('finance.helper'));
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'finance');
    }
}
