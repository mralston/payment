<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentError extends Model
{
    protected $fillable = [
        'payment_id',
        'data',
        'payment_stage_id',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}