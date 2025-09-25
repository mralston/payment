<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Mralston\Payment\Mail\RemoteSign;
use Mralston\Payment\Models\Payment;

class RemoteSignController
{
    public function remoteSign(Request $request, string $uuid)
    {
        $payment = Payment::firstWhere('uuid', $uuid);

        if (empty($payment)) {
            abort(404);
        }

//        // finance_applications.post_signing route fetches updated status and redirects to complete page
//        // TODO: Show public complete page as customer won't be authenticated
//        $return_url = route('finance_applications.post_signing', [
//            'finance_application' => $financeApplication
//        ]);

        try {
            return redirect($payment->paymentProvider->gateway()->getSigningUrl($payment /*, $return_url*/));
        } catch (\Exception $ex) {
            return '<h1>Sorry, we weren\'t able to process your request.</h1><p>' . $ex->getMessage() . '</p>';
        }
    }

    public function sendRemoteSignLink(Request $request, Payment $payment)
    {
        // Prep customer object
        $customer = (object)[
            'name' => $payment->first_name . ' ' . $payment->last_name,
            'email' => $payment->email_address,
        ];

        // Send signing link to customer
        Mail::to($customer)
            ->send(new RemoteSign($payment));

        return response()->json(['status' => 'sent']);
    }
}
