<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Collection;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Data\Offers;
use Mralston\Payment\Interfaces\PrequalifiesCustomer;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentSurvey;

class PrequalService
{
    /**
     * Performs prequalification against all payment providers which support it.
     * Returns a collection of immediate results and promises.
     * When promises resolve, they will fire a PrequalComplete event
     *
     * @param PaymentSurvey $survey
     * @return Collection<Offers|PrequalPromiseData>
     */
    public function run(PaymentSurvey $survey): Collection
    {
        // Loop through payment providers with a gateway
        return PaymentProvider::query()
            ->whereNotNull('gateway')
            ->get()
            ->map(function (PaymentProvider $provider) use ($survey) {
                // Grab the gateway
                $gateway = $provider->gateway();

                // Check it supports prequalification
                if (!in_array(PrequalifiesCustomer::class, class_implements($gateway))) {
                    return null;
                }

                // Run the prequalification and return the result or promise
                return $gateway->prequal($survey);
            })
            ->reject(fn ($result) => $result === null);
    }
}
