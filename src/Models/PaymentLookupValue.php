<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLookupValue extends Model
{
    protected $fillable = [
        'name',
        'description',
        'identifier',
    ];

    public function paymentLookupField(): BelongsTo
    {
        return $this->belongsTo(PaymentLookupField::class);
    }
}
