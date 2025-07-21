<?php

namespace Mralston\Payment\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PaymentCancellation extends Model
{
    protected $fillable = [
        'payment_id',
        'user_id',
        'reason',
        'source',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class)
            ->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(config('payment.user_model'));
    }

    public static function onDate(Carbon $date): Collection
    {
        return PaymentCancellation::whereBetween(
            'created_at',
            [$date->format('Y-m-d'), $date->addDay()->format('Y-m-d')]
        )->with('payment')
        ->get()
        ->unique('payment_id');
    }
}
