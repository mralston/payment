<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class PaymentProvider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'identifier',
    ];

    protected $casts = [
        'selling_points' => 'array',
    ];

    public static function byIdentifier(?string $identifier = null): ?PaymentProvider
    {
        return Cache::remember(
            'payment-provider-' . $identifier,
            60,
            function () use ($identifier) {
                return static::firstWhere('identifier', $identifier);
            }
        );
    }

    public function gateway()
    {
        if (empty($this->gateway)) {
            return null;
        }

        return app($this->gateway);
    }

    public function paymentProducts()
    {
        return $this->hasMany(PaymentProduct::class);
    }
}
