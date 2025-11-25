<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Facades\Log;
use Mralston\Payment\Interfaces\LeaseGateway;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Models\PaymentSurvey;

class LeaseService
{
    public function submitApplication(LeaseGateway $gateway, Payment $payment, PaymentOffer $offer, PaymentSurvey $survey, int $parent): bool
    {
        try {
            $response = $gateway->apply($payment);
            Log::channel('payment')->debug('Select Response: ', $response);
        } catch (\Exception $e) {
            Log::channel('payment')->error('Error submitting application: ' . $e->getMessage());
            $payment->update([
                'provider_request_data' => $gateway->getRequestData() ?? $payment->provider_request_data,
                'provider_response_data' => $gateway->getResponseData() ?? $payment->provider_response_data,
                'submitted_at' => now(),
                'payment_status_id' => PaymentStatus::byIdentifier('error')?->id,
            ]);

            return false;
        }

        // Update payment with response
        $result = $payment->update([
            'provider_request_data' => $gateway->getRequestData() ?? $payment->provider_request_data,
            'provider_response_data' => $gateway->getResponseData() ?? $payment->provider_response_data,
            'submitted_at' => now(),
            'payment_status_id' => PaymentStatus::byIdentifier($response['status'])?->id,
            ...(
            empty($payment->reference) ?
                ['reference' => $response['reference'] ?? null] :
                []
            ),
        ]);

        Log::channel('payment')->debug('update after submit result: ' . $result ? 'success' : 'failure');

        // Mark selected offer as submitted (cannot resubmit once an offer has been selected)
        $offer->update([
            'selected' => true,
        ]);

//        // Delete other offers
//        $survey->paymentOffers()
//            ->where('id', '!=', $offer->id)
//            ->where('selected', false)
//            ->delete();

        return true;
    }
}
