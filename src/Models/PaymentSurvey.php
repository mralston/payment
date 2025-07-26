<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentSurvey extends Model
{
    protected $fillable = [
        'customers',
        'addresses',
    ];

    protected $casts = [
        'customers' => 'collection',
        'addresses' => 'collection',
    ];

    public function parentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function paymentOffers(): HasMany
    {
        return $this->hasMany(PaymentOffer::class);
    }
}
