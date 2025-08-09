<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentOffer extends Model
{
    protected $fillable = [
        'payment_survey_id',
        'name',
        'type',
        'amount',
        'payment_provider_id',
        'apr',
        'term',
        'deferred',
        'upfront_payment',
        'first_payment',
        'monthly_payment',
        'final_payment',
        'minimum_payments',
        'total_payable',
        'status',
        'preapproval_id',
        'priority',
        'provider_application_id',
        'provider_offer_id',
        'small_print',
    ];

    protected $hidden = [
        'minimum_payments',
        'small_print',
    ];

    public function casts(): array
    {
        return [
            'minimum_payments' => 'collection',
        ];
    }

    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }
}
