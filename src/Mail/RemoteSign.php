<?php

namespace Mralston\Payment\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Mralston\Payment\Interfaces\Signable;
use Mralston\Payment\Models\Payment;

class RemoteSign extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public Payment $payment
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sign Your Agreement',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'payment::mail.remote_sign',
            with: [
                'rep' => $this->payment->parentable->user ?? null,
                'signingMethod' => $this->payment->paymentProvider->gateway() instanceof Signable ?
                    $this->payment->paymentProvider->gateway()->signingMethod() :
                    null,
            ]
        );
    }
}
