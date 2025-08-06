<?php

namespace Mralston\Payment\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Data\OfferData;
use Mralston\Payment\Data\Offers;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentSurvey;

class OffersUpdated implements ShouldBroadcast
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
        return [
            'gateway' => $this->gateway,
            'surveyId' => $this->surveyId,
            'offers' => $this
                ->offers
                ->map(fn (PaymentOffer $offer) => $offer->load('paymentProvider:id,name')),
        ];
    }
}

