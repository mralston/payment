<?php

namespace Mralston\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FinanceSurvey extends Model
{
    protected $fillable = [
        'customers',
    ];

    protected $casts = [
        'customers' => 'collection',
    ];

    public function parentable(): MorphTo
    {
        return $this->morphTo();
    }
}
