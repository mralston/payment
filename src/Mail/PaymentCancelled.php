<?php

namespace Mralston\Payment\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\Payment;

class PaymentCancelled extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected PaymentHelper $helper;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public Payment $payment,
    ) {
        $this->helper = app(PaymentHelper::class);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Cancelled',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'payment::mail.payment_cancelled',
            with: [
                'cancellation' => $this->payment->last_cancellation,
                'parent' => $this->payment->parentable,
                'rep' => $this->payment->parentable->user ?? null,
                'parentModelDescription' => config('payment.parent_model_description'),
                'parentRouteName' => $this->helper->getParentRouteName(),
            ]
        );
    }
}
