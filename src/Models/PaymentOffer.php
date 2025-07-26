<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentOffer extends Model
{
    protected $fillable = [
        'name',
        'type',
        'amount',
        'payment_provider_id',
        'apr',
        'term',
        'deferred',
        'first_payment',
        'monthly_payment',
        'final_payment',
        'minimum_payments',
        'status',
        'preapproval_id',
        'priority',
        'provider_foreign_id',
    ];
}
