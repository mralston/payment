<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Mralston\Payment\Models\PaymentSurvey;

Broadcast::channel('offers.{survey}', function ($user, PaymentSurvey $survey) {
    return true;
//    return $user->id === $survey->parent->user_id;
});
