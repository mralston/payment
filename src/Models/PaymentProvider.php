<?php

namespace Mralston\Payment\Models;

use App\PaymentProductCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Mralston\Epvs\Models\FinanceLender as EpvsFinanceLender;
use Mralston\Payment\Interfaces\PaymentGateway;

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

    public function gateway(): ?PaymentGateway
    {
        if (empty($this->gateway)) {
            return null;
        }

        return app($this->gateway);
    }

    public function paymentProducts(): HasMany
    {
        return $this->hasMany(PaymentProduct::class);
    }

    public function paymentProductCodes()
    {
        return $this->hasMany(PaymentProductCode::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function epvsFinanceLender(): BelongsTo
    {
        return $this->belongsTo(EpvsFinanceLender::class);
    }
}
