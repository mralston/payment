<?php

namespace Mralston\Payment\Traits;

use Mralston\Payment\Services\PaymentErrorHandler;
use Illuminate\Support\Collection;

trait HandlesPaymentErrors
{
    protected function hasErrors(array|Collection $responseData, string $provider): bool
    {
        $errorHandler = app(PaymentErrorHandler::class);

        return !empty($errorHandler->normalizeErrors($responseData, $provider));
    }

    protected function normalizeErrors(
        array|Collection $responseData,
        string $provider,
    ): array {
        $errorHandler = app(PaymentErrorHandler::class);
        
        return $errorHandler->normalizeErrors(
            $responseData,
            $provider,
        );
    }
}

