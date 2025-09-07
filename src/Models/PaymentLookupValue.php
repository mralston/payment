<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Enums\LookupField;

class PaymentLookupValue extends Model
{
    protected $fillable = [
        'name',
        'description',
        'identifier',
        'value',
        'payment_provider_values',
    ];

    public $casts = [
        'payment_provider_values' => 'collection',
    ];

    public static function byValue(string $value): static
    {
        return Cache::remember(
            'payment-lookup-value-' . $value,
            60,
            function () use ($value) {
                return static::firstWhere('value', $value);
            }
        );
    }

    public function paymentLookupField(): BelongsTo
    {
        return $this->belongsTo(PaymentLookupField::class);
    }
}
