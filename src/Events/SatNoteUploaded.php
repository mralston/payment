<?php

namespace Mralston\Payment\Events;

use App\File;
use App\FinanceApplication;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SatNoteUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public FinanceApplication $finance_application;
    public File $sat_note;

    /**
     * Create a new event instance.
     *
     * @param FinanceApplication $finance_application
     * @param File $sat_note
     */
    public function __construct(FinanceApplication $finance_application, File $sat_note)
    {
        $this->finance_application = $finance_application;
        $this->sat_note = $sat_note;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
