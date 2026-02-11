<?php

namespace Mralston\Payment\Integrations;

use Mralston\Payment\Data\NormalisedResponseData;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Interfaces\Response;

class PropensioResponse implements Response
{
    public function parseResponse(array $responseData): NormalisedResponseData
    {
        $key = isset($responseData['results']['loan']) ?
            'loan' : 'data';

        $statusLookupValue = PaymentLookupField::byIdentifier('status')
            ->paymentLookupValues()
            ->whereJsonContains('payment_provider_values->propensio', $responseData['results'][$key]['applicationStatusCode'])
            ->first();

        if (is_null($statusLookupValue)) {
            return new NormalisedResponseData(
                httpStatus: $responseData['code'] ?? null,
                requestId: $responseData['results']['requestReqId'] ?? null,
                applicationId: $responseData['results'][$key]['applicationId'] ?? null,
                applicationNumber: $responseData['results'][$key]['applicationNumber'] ?? null,
                statusId: null,
                statusName: null,
            );
        }

        $paymentStatus = PaymentStatus::byIdentifier($statusLookupValue->value);

        return new NormalisedResponseData(
            httpStatus: $responseData['code'] ?? null,
            requestId: $responseData['results']['requestReqId'] ?? null,
            applicationId: $responseData['results'][$key]['applicationId'] ?? null,
            applicationNumber: $responseData['results'][$key]['applicationNumber'] ?? null,
            statusId: $paymentStatus->id,
            statusName: $paymentStatus->name,
            monthlyPayment: $responseData['results'][$key]['monthlyRepaymentAmount'] ?? null,
            aprRate: $responseData['results'][$key]['aprRate'] ?? null,
            termInMonths: $responseData['results'][$key]['repaymentTermInMonths'] ?? null,
        );
    }
}
