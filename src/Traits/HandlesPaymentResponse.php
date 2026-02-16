<?php

namespace Mralston\Payment\Traits;

use Mralston\Payment\Services\PaymentResponseHandler;
use Mralston\Payment\Data\NormalisedResponseData;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HandlesPaymentResponse
{
    protected function normaliseResponse(
        array|Collection $responseData,
        string $provider,
    ): NormalisedResponseData {
        $className = '\Mralston\Payment\Integrations\\' . Str::title($provider) . 'Response';
        $concrete = new $className();
        
        return $concrete->parseResponse($responseData);
    }
}

