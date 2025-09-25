<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Mralston\Payment\Events\SatNoteUploaded;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Models\Payment;

class SatNoteController
{
    public function __construct(
        private PaymentHelper $helper,
    ) {
        //
    }

    public function uploadSatNote(Request $request, Payment $payment)
    {
        $parentModel = $payment->parentable;

        $this->helper->setParentModel($parentModel);

        // Fetch the uploaded file
        $upload = $request->file('sat_note');

        // Ask the parent application to store it
        $file = $this->helper->storeFile(
            'sat_note',
            $upload->getClientOriginalName(),
            $upload->getContent()
        );

        // Add reference to file in payment record
        $payment->update([
            'sat_note_file_id' => $file->id
        ]);

        event(new SatNoteUploaded($payment, $file));

        return response()
            ->json($file, 200);
    }
}
