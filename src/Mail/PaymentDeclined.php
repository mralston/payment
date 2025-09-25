<?php

namespace Mralston\Payment\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\Payment;

class PaymentDeclined extends Mailable
{
    use Queueable;
    use SerializesModels;

    private PaymentHelper $helper;

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
            subject: 'Payment Declined',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'payment::mail.payment_declined',
            with: [
                'parent' => $this->payment->parentable,
                'rep' => $this->payment->parentable->user ?? null,
            ]
        );
    }
}
