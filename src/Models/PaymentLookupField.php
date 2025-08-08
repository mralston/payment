<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Mralston\Payment\Enums\LookupField;

class PaymentLookupField extends Model
{
    protected $fillable = [
        'name',
        'description',
        'identifier',
    ];

    public function paymentLookupValues(): HasMany
    {
        return $this->hasMany(PaymentLookupValue::class);
    }

    public static function byIdentifier(string|LookupField $identifier): static
    {
        if (is_a($identifier, LookupField::class)) {
            $identifier = $identifier->value;
        }

        return Cache::remember(
            'payment-lookup-field-' . $identifier,
            60,
            function () use ($identifier) {
                return static::firstWhere('identifier', $identifier);
            }
        );
    }
}
