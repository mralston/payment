<?php

namespace Mralston\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentProvider extends Model
{
    protected $fillable = [
        'name',
        'identifier',
    ];

    public function gateway()
    {
        if (empty($this->gateway)) {
            return null;
        }

        return app($this->gateway);
    }
}
