<?php

namespace Mralston\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Services\PrequalService;
use Mralston\Payment\Traits\BootstrapsPayment;

class PrequalController extends Controller
{
    use BootstrapsPayment;

    public function __construct(
        private PaymentHelper $helper,
        private PrequalService $prequalService
    ) {
        //
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $parent)
    {
        $parentModel = $this->bootstrap($parent, $this->helper);

        $survey = $parentModel->paymentSurvey;

        $this->prequalService->run($survey);
    }
}
