<?php

namespace Mralston\Payment\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'tokenable_type',
        'tokenable_id',
    ];

    protected $casts = [
        'abilities' => 'json',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];
}
