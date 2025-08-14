<?php

namespace Mralston\Payment\Mail;

use App\FinanceApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Mralston\Payment\Models\Payment;

class SatNoteUpload extends Mailable implements ShouldQueue
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
        protected Payment $payment
    ) {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject('Sat Note Upload for Finance Application ' . $this->payment->provider_foreign_id)
            ->markdown('mail.sat_note_upload')
            ->with([
                'payment' => $this->payment,
                'parent' => $this->payment->parentable,
                'rep' => $this->payment->parentable->user ?? null,
                'customer' => $this->payment->parentable->customers->first(),
            ]);

        if (!empty($this->payment->sat_note))
        {
            $mail->attach($this->payment->sat_note->dir, [
                'as' => 'Sat Note ' . $this->payment->provider_foreign_id . '.pdf',
                'mime' => 'application/pdf'
            ]);
        }

        return $mail;
    }
}
