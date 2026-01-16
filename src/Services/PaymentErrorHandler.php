<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Collection;
use Mralston\Payment\Data\ErrorCollectionData;
use Mralston\Payment\Data\ErrorData;
use Psr\Http\Message\ResponseInterface;

class PaymentErrorHandler
{
    /**
     * Normalize errors from any provider response
     */
    public function normalizeErrors(
        array|Collection $responseData,
        string $provider,
    ): array {
      
        // Delegate to provider-specific parser
        return match($provider) {
            'propensio' => $this->parsePropensioErrors($responseData),
        };
    }

    /**
     * Parse Propensio-specific error format
     */
    protected function parsePropensioErrors(array|Collection $responseData): array
    {
        $errors = [];
        //missing data errors
        if (!empty($responseData['results']['errors'])) {
            foreach ($responseData['results']['errors'] as $error) {
                $errors[] = $error['errorCode'] . ': ' . $error['errorMsg'];
            }
        }

        //check for api exceptions
        if (!empty($responseData['results']['message'])) {
            $errors[] = $responseData['results']['message'];
        }

        return $errors;
    }
}
