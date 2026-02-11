<?php

namespace Mralston\Payment\Tests;

use Mralston\Payment\PaymentServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Get package service providers.
     */
    protected function getPackageProviders($app): array
    {
        return [
            PaymentServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Load .env.testing if it exists
        if (file_exists(__DIR__ . '/../.env.testing')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..', '.env.testing');
            $dotenv->load();
        }

        // Set config values after loading env file
        $app['config']->set('payment.propensio.username', env('PROPENSIO_USERNAME', ''));
        $app['config']->set('payment.propensio.password', env('PROPENSIO_PASSWORD', ''));
        $app['config']->set('payment.propensio.endpoint', env('PROPENSIO_ENDPOINT', 'testing'));

        $app['config']->set('payment.propensio.api_from_code', env('PROPENSIO_API_FROM_CODE'));
        $app['config']->set('payment.propensio.introducers_reference', env('PROPENSIO_INTRODUCERS_REFERENCE'));
        $app['config']->set('payment.propensio.goods_host_code', env('PROPENSIO_GOODS_HOST_CODE'));
        $app['config']->set('payment.propensio.loan_purpose_host_code', env('PROPENSIO_LOAN_PURPOSE_HOST_CODE'));
 
        
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
