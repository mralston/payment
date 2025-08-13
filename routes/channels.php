<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentSurvey;

Broadcast::channel('offers.{survey}', function ($user, PaymentSurvey $survey) {
    return true;

    // TODO: Need to relax this so that staff senior to reps can see it
    // We should ask the helper class whether the user is allowed to see the parent
    return $user->id === $survey->parentable?->user_id;
});

Broadcast::channel('payments.{payment}', function ($user, Payment $payment) {
    return true;

    // TODO: Need to relax this so that staff senior to reps can see it
    // We should ask the helper class whether the user is allowed to see the parent
    return $user->id === $payment->parentable?->user_id;
});
