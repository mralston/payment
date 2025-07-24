<?php

namespace Mralston\Payment\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\InteractsWithQueue;
use Mralston\Payment\Data\PrequalResultData;
use Mralston\Payment\Models\PaymentSurvey;

class PrequalComplete implements ShouldBroadcast
{
    use InteractsWithQueue;

    public function __construct(
        public PrequalResultData $data,
    ) {
        //
    }

    /**
     * Get the channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('prequal.' . $this->data->survey->id);
    }
}
