<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Collection;
use Mralston\Payment\Data\NormalisedResponseData;

class PaymentResponseHandler
{
    public function normalizeResponse(
        array|Collection $responseData,
        string $provider,
    ): NormalisedResponseData {
        return match($provider) {
            'propensio' => $this->parsePropensioResponse($responseData),
        };
    }

    protected function parsePropensioResponse(array|Collection $responseData): NormalisedResponseData
    {
        return new NormalisedResponseData(
            httpStatus: $responseData['code'] ?? null,
            requestId: $responseData['results']['requestReqId'] ?? null,
            applicationId: $responseData['results']['data']['applicationId'] ?? null,
            applicationNumber: $responseData['results']['data']['applicationNumber'] ?? null,
            statusCode: $responseData['results']['data']['applicationStatusCode'] ?? null,
        );
    }
}
