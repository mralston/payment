<?php

namespace Mralston\Payment\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Mralston\Payment\Models\Payment;

class CancelManually extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $theme = 'markdown';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        protected Payment $payment)
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Cancel Finance Application ' . $this->payment->reference)
            ->markdown('mail.cancel_manually')
            ->with([
                'finance_application' => $this->payment,
                'parent' => $this->payment->parentable,
                'rep' => $this->payment->parentable->user ?? null,
                'customer' => $this->payment->parentable->customers->first(),
            ]);
    }
}
