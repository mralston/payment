<?php

namespace Mralston\Payment\Http\Controllers;

use App\FinanceApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentStatus;
use Mralston\Payment\Services\HometreeService;
use Mralston\Payment\Services\TandemService;

class WebhookController
{
    public function __construct(
        protected HometreeService $hometreeService,
        protected TandemService $tandemService,
    ) {}

    public function tandem(Request $request, string $uuid)
    {
        return $this->tandemService->handleWebhook($request, $uuid);
    }

    public function hometree(Request $request)
    {
        $this->hometreeService->handleWebhook($request->collect());

        return response()->json(['message' => 'success']);
    }
}
