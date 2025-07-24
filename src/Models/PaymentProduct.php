<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentProduct extends Model
{
    protected $fillable = [
        'name',
        'description',
        'identifier',
    ];
}
