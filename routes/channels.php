<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Mralston\Payment\Models\PaymentSurvey;

Broadcast::channel('prequal.{survey}', function (User $user, PaymentSurvey $survey) {
    return true;
//    return $user->id === $survey->parent->user_id;
});
