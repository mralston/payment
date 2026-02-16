<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentStage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'identifier',
    ];

    public static function byIdentifier(string $identifier): ?PaymentStage
    {
        return static::firstWhere('identifier', $identifier);
    }
}