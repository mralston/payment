<?php

namespace Mralston\Payment\Integrations;

use Mralston\Payment\Interfaces\ErrorResponse;

class PropensioErrorHandler implements ErrorResponse
{
    public function parseErrors(array $responseData): array
    {
        $errors = [];

        if (!empty($responseData['code']) && $responseData['code'] != 200) {
            $errors[] = 'HTTP Status: ' . $responseData['code'];
        }

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

        if (
            !empty($responseData['response']) &&
            $responseData['response']['STATUS'] == 2
        ) {
            $errors[] = 'API Exception: ' . ($responseData['response']['RETURN_MESSAGE']['MESSAGE_TEXT'] ?? json_encode($responseData['response']));
        }

        return $errors;
    }
}
