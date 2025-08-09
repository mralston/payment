<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Mralston\Payment\Enums\PaymentType as PaymentTypeEnum;

class PaymentType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'identifier',
    ];

    public static function byIdentifier(string|PaymentTypeEnum $identifier): static
    {
        if (is_a($identifier, PaymentTypeEnum::class)) {
            $identifier = $identifier->value;
        }

        return Cache::remember(
            'payment-type-' . $identifier,
            60,
            function () use ($identifier) {
                return static::firstWhere('identifier', $identifier);
            }
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
