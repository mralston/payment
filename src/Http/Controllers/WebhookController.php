<?php

namespace Mralston\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Mralston\Payment\Services\HometreeService;

class WebhookController
{
    public function __construct(
        private HometreeService $hometreeService
    ) {}
  
    public function tandem()
    {
        //
    }

    public function hometree(Request $request)
    {
        $this->hometreeService->handleWebhook($request->collect());

        return response()->json(['message' => 'success']);
    }
}
