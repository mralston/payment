<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Mralston\Payment\Models\PaymentSurvey;

Broadcast::channel('offers.{survey}', function ($user, PaymentSurvey $survey) {
    return $user->id === $survey->parentable?->user_id;
});
