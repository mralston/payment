<?php

namespace Mralston\Payment\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Data\PrequalResultData;

class OfferReceived implements ShouldBroadcast
{
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(
        public PrequalResultData $data,
    ) {
        Log::info('OfferReceived event constructed.', ['data' => $data->toArray()]);
    }

    /**
     * Get the channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        Log::info('OfferReceived broadcastOn method called.');
        return new PrivateChannel('offers.' . $this->data->survey->id);
    }
}

