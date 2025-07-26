<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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
        'sort_order',
    ];
}
