<?php

namespace Mralston\Payment\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentSurvey;

trait HasPayments
{
    public function paymentSurvey(): MorphOne
    {
        return $this->morphOne(PaymentSurvey::class, 'parentable');
    }

    public function paymentOffers(): MorphMany
    {
        return $this->morphMany(PaymentOffer::class, 'parentable');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'parentable');
    }

    public function selectedPaymentOffer(): MorphOne
    {
        return $this->morphOne(PaymentOffer::class, 'parentable')
            ->where('selected', true)
            ->latest('payment_offers.created_at');
    }

    public function activePayment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'parentable')
            ->whereHas('paymentStatus', function ($query) {
                $query->where('active', true);
            })
            ->latest('payments.created_at');
    }

    public function paymentIsCash(): bool
    {
        return $this->selectedPaymentOffer?->type === 'cash'; // TODO: Should we say !$this->paymentIsLoan() && !$this->paymentIsLease() ?
    }

    public function paymentIsNotCash(): bool
    {
        return ! $this->paymentIsCash;
    }

    public function paymentIsLoan(): bool
    {
        return $this->activePayment?->paymentProvider?->paymentType?->identifier === 'finance' ||
            $this->selectedPaymentOffer?->type === 'finance';
    }

    public function paymentIsNotLoan(): bool
    {
        return ! $this->paymentIsLoan();
    }

    public function paymentIsLease(): bool
    {
        return $this->activePayment?->paymentProvider?->paymentType?->identifier === 'lease' ||
            $this->selectedPaymentOffer?->type === 'lease';
    }

    public function paymentIsNotLease(): bool
    {
        return ! $this->paymentIsLease();
    }
}
