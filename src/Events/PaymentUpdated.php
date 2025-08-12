<?php

namespace Mralston\Payment\Events;

use Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mralston\Payment\Models\Payment;

class PaymentUpdated extends Event implements ShouldBroadcast
{
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(
        public Payment $payment
    ) {
        //
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('payments.' . $this->payment->id);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'payment' => $this
                ->payment
                ->load('paymentStatus'),
        ];
    }
}
