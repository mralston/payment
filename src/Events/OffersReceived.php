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
use Mralston\Payment\Models\PaymentSurvey;

class OffersReceived implements ShouldBroadcast
{
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * @param string $gateway
     * @param PaymentSurvey $survey
     * @param Collection<OfferData> $offers
     */
    public function __construct(
        public string $gateway,
        public PaymentSurvey $survey,
        public Collection $offers,
    ) {
        //
    }

    /**
     * Get the channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('offers.' . $this->survey->id);
    }
}

