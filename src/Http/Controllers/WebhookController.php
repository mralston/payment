<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Http\Requests\HometreeWebhookRequest;
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

    public function hometree(HometreeWebhookRequest $request)
    {
        Log::channel('payment')->debug('Hometree webhook received');
        Log::channel('payment')->debug('Headers:', $request->headers->all());
        Log::channel('payment')->debug('Body:', $request->all());
        Log::channel('payment')->debug($request->getContent());


        $this->hometreeService->handleWebhook(collect($request->validated()));

        return response()->json(['message' => 'success']);
    }
}
