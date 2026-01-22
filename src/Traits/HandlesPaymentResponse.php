<?php

namespace Mralston\Payment\Traits;

use Mralston\Payment\Services\PaymentResponseHandler;
use Mralston\Payment\Data\NormalisedResponseData;
use Illuminate\Support\Collection;

trait HandlesPaymentResponse
{
    protected function normalizeResponse(
        array|Collection $responseData,
        string $provider,
    ): NormalisedResponseData {
        $responseHandler = app(PaymentResponseHandler::class);
        
        return $responseHandler->normalizeResponse(
            $responseData,
            $provider,
        );
    }
}

