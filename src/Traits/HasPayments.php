<?php

namespace Mralston\Payment\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentSurvey;

trait HasPayments
{
    public function paymentSurvey(): MorphOne
    {
        return $this->morphOne(PaymentSurvey::class, 'parentable');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'parentable');
    }

    public function activePayment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'parentable')
            ->whereHas('paymentStatus', function ($query) {
                $query->where('active', true);
            })
            ->latest();
    }
}
