<?php

namespace Mralston\Payment\Observers;

use Illuminate\Support\Str;
use Mralston\Payment\Events\PaymentAccepted;
use Mralston\Payment\Events\PaymentActivated;
use Mralston\Payment\Events\PaymentCancelled;
use Mralston\Payment\Events\PaymentConditionallyAccepted;
use Mralston\Payment\Events\PaymentCustomerCancelled;
use Mralston\Payment\Events\PaymentDeclined;
use Mralston\Payment\Events\PaymentDocumentsReceived;
use Mralston\Payment\Events\PaymentExpired;
use Mralston\Payment\Events\PaymentLived;
use Mralston\Payment\Events\PaymentParked;
use Mralston\Payment\Events\PaymentPayoutRequested;
use Mralston\Payment\Events\PaymentReferred;
use Mralston\Payment\Events\PaymentSnagged;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentStatus;

class PaymentObserver
{
    public function creating(Payment $payment): void
    {
        $payment->uuid = Str::uuid();
    }

    public function updated(Payment $payment): void
    {
        if ($payment->wasChanged('payment_status_id')) {
            $previousStatus = PaymentStatus::find($payment->getOriginal('payment_status_id'));

            switch ($payment->paymentStatus?->identifier) {
                case 'accepted':
                    event(new PaymentAccepted($payment, $previousStatus));
                    break;
                case 'conditional_accept':
                    event(new PaymentConditionallyAccepted($payment, $previousStatus));
                    break;
                case 'referred':
                    event(new PaymentReferred($payment, $previousStatus));
                    break;
                case 'documents_received':
                    event(new PaymentDocumentsReceived($payment, $previousStatus));
                    break;
                case 'snagged':
                    event(new PaymentSnagged($payment, $previousStatus));
                    break;
                case 'declined':
                    event(new PaymentDeclined($payment, $previousStatus));
                    break;
                case 'parked':
                    event(new PaymentParked($payment, $previousStatus));
                    break;
                case 'customer_cancelled':
                    event(new PaymentCustomerCancelled($payment, $previousStatus));
                    break;
                case 'payout_requested':
                    event(new PaymentPayoutRequested($payment, $previousStatus));
                    break;
                case 'active':
                    event(new PaymentActivated($payment, $previousStatus));
                    break;
                case 'live':
                    event(new PaymentLived($payment, $previousStatus));
                    break;
                case 'expired':
                    event(new PaymentExpired($payment, $previousStatus));
                    break;
                // TODO: Implement cancelled once notification to lender is no longer event-based
//                case 'cancelled':
//                    event(new PaymentCancelled($payment, $previousStatus));
//                    break;
            }
        }
    }
}
