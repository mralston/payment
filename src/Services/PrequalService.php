<?php

namespace Mralston\Payment\Services;

use Mralston\Payment\Models\PaymentSurvey;

class PrequalService
{
    public function run(PaymentSurvey $survey)
    {
        // TODO: Submit prequal requests to all gateways
        // Return some sort of promise ID

        // The individual gateway prequal function should run in background jobs and fire events containing the results as they come in
        // The front end will listen for these events using Echo
    }
}
