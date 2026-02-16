<?php

namespace Mralston\Payment\Traits;

use Mralston\Payment\Services\PaymentErrorHandler;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HandlesPaymentErrors
{
    protected function hasErrors(
        array|Collection $responseData,
        string $provider
    ): bool
    {
        $className = '\Mralston\Payment\Integrations\\' . Str::title($provider) . 'ErrorHandler';
        $concrete = new $className();

        return !empty($concrete->parseErrors($responseData));
    }

    protected function normaliseErrors(
        array|Collection $responseData,
        string $provider,
    ): array {
        $className = '\Mralston\Payment\Integrations\\' . Str::title($provider) . 'ErrorHandler';
        $concrete = new $className();
        
        return $concrete->parseErrors($responseData);

    }
}
