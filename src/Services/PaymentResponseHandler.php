<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Collection;

class PaymentResponseHandler
{
    public function normalizeResponse(
        array|Collection $responseData,
        string $provider,
    ): array {
        return match($provider) {
            'propensio' => $this->parsePropensioResponse($responseData),
        };
    }

    protected function parsePropensioResponse(array|Collection $responseData): array
    {
        return [
            'request_id' => $responseData['results']['requestReqId'] ?? null,
            'application_id' => $responseData['results']['data']['applicationId'] ?? null,
            'application_number' => $responseData['results']['data']['applicationNumber'] ?? null,
            'status_code' => $responseData['results']['data']['applicationStatusCode'] ?? null,
            'portal_url' => $responseData['results']['data']['portalConfirmationUrl'] ?? null,
            'applicant_name' => $responseData['results']['data']['applicantName'] ?? null,
            'created_at' => $responseData['results']['data']['applicationCreatedAt'] ?? null,
            'checklist_items' => $responseData['results']['checklistItems'] ?? null,
            'status' => $responseData['results']['loan']['applicationStatusCode'] ?? null,
        ];
    }
}
