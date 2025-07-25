<?php

namespace Mralston\Payment\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Data\PrequalResultData;

//class PrequalComplete implements ShouldBroadcast
//{
//    use InteractsWithQueue;
//    use SerializesModels;
//
//    public function __construct(
//        public PrequalResultData $data,
//    ) {
//        Log::info('PrequalComplete event constructed.', ['data' => $data->toArray()]);
//    }
//
//    /**
//     * Get the channel the event should broadcast on.
//     */
//    public function broadcastOn(): Channel
//    {
//        Log::info('PrequalComplete broadcastOn method called.');
//        return new PrivateChannel('prequal.' . $this->data->survey->id);
//    }
//}

class PrequalComplete implements ShouldBroadcast
{
    use InteractsWithQueue;
    use SerializesModels;

    public string $testMessage;
    public int $surveyId; // To simulate the channel name part

    /**
     * Create a new event instance.
     */
    public function __construct(string $message, int $surveyId)
    {
        $this->testMessage = $message;
        $this->surveyId = $surveyId;
        Log::info('PrequalComplete event constructed (simplified).', ['message' => $message, 'survey_id' => $surveyId]);
    }

    /**
     * Get the channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        Log::info('PrequalComplete broadcastOn method called (simplified).');
        // Use a test channel name
        return new PrivateChannel('prequal.test.' . $this->surveyId);
    }

    /**
     * The event's broadcast name. (Optional, for debugging)
     */
    public function broadcastAs(): string
    {
        return 'PrequalCompleteTest';
    }

    /**
     * Get the data to broadcast. (Optional, for debugging)
     */
    public function broadcastWith(): array
    {
        return ['message' => $this->testMessage, 'survey_id' => $this->surveyId];
    }
}
