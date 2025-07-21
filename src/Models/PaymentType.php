<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'identifier',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
