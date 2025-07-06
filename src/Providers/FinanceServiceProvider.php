<?php

namespace Mralston\Finance\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class FinanceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'finance');

        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

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

        // Share root view data if provided
        if ($rootViewData = config('finance.inertia_root_view_data')) {
            Inertia::share($rootViewData);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'finance');
    }
}
