<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;

class PaymentStatus extends Model
{
    protected $fillable = [
        'name',
        'description',
        'identifier',
    ];

    public static function byIdentifier(?string $identifier = null): ?PaymentStatus
    {
        if (is_null($identifier)) {
            return null;
        }

        return Cache::remember(
            key: 'payment-status-'.$identifier,
            ttl: 60 * 60 * 24,
            callback: function () use ($identifier) {
                return PaymentStatus::firstWhere('identifier', $identifier);
            }
        );
    }
}
