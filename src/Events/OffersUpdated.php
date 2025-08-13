<?php

namespace Mralston\Payment\Events;

use Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Services\Compression;

class OffersUpdated extends Event implements ShouldBroadcast
{
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * @param string $gateway
     * @param int $surveyId
     * @param Collection $offers
     */
    public function __construct(
        public string $gateway,
        public int $surveyId,
        public Collection $offers,
    ) {
        //
    }

    /**
     * Get the channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('offers.' . $this->surveyId);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $offers = $this->offers->map(function (PaymentOffer $offer) {
            // Lazy eager load the payment provider and limit its columns
            $offer->load('paymentProvider:id,name,logo');

            // Manually create an array with the only columns we need + the payment provider
            return [
                ...collect($offer->getAttributes())
                    ->except([
                        'payment_survey_id',
                        'paymentProvider',
                        'minimum_payments',
                        'preapproval_id',
                        'provider_offer_id',
                        'provider_application_id',
                        'small_print',
                        'created_at',
                        'updated_at',
                    ])
                    ->toArray(),
                'payment_provider' => $offer->paymentProvider,
            ];
        });

        $payload = [
            'gateway' => $this->gateway,
            'surveyId' => $this->surveyId,
            'offers' => $offers,
        ];

        return [
            'payload' => app(Compression::class)
                ->compress($payload)
        ];
    }
}

