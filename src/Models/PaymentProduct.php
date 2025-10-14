<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'identifier',
        'provider_foreign_id',
        'apr',
        'term',
        'deferred',
        'deferred_type',
        'sort_order',
    ];
}
