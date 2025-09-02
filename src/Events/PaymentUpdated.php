<?php

namespace Mralston\Payment\Events;

use Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Services\Compression;

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
        // Load the paymentStatus relation
        $payment = $this
            ->payment
            ->load([
                'paymentStatus',
                'paymentProvider',
            ])
            ->unsetRelation('parentable')
            ->unsetRelation('paymentOffer');

        // Remove the offers array, if present (it's too big for Pusher)
        $payment->provider_response_data = $payment->provider_response_data?->except('offers');

        $payload = $payment->toArray();

//        if ($payment->paymentStatus->error) {
//            $payload['errorData'] = $payment
//                ->paymentProvider
//                ->gateway()
//                ->parseErrors($payment->provider_response_data);
//        }

        Log::debug('broadcasting', ['payment' => $payload]);

        return [
            'payload' => app(Compression::class)
                ->compress($payload),
        ];
    }
}
