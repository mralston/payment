<?php

namespace Mralston\Payment\Integrations;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mralston\Payment\Data\PrequalData;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Interfaces\FinanceGateway;
use Mralston\Payment\Interfaces\PaymentGateway;
use Mralston\Payment\Interfaces\PrequalifiesCustomer;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentSurvey;

class V12 implements PaymentGateway, FinanceGateway, PrequalifiesCustomer
{
    private string $endpoint = 'https://apply.v12finance.com/latest/retailerapi';

    private $requestData = null;
    private $responseData = null;

    public function __construct(
        protected string $key,
        protected string $retailerId,
        protected string $retailerGuid,
    ) {
        //
    }

    public function apply(Payment $payment): Payment
    {
        // TODO: Implement apply() method.
    }

    public function cancel(Payment $payment, ?string $reason = null): bool
    {
        // TODO: Implement cancel() method.
    }

    public function pollStatus(Payment $payment): array
    {
        // TODO: Implement pollStatus() method.
    }

    public function calculatePayments(int $loanAmount, float $apr, int $loanTerm, ?int $deferredPeriod = null): array
    {
        // TODO: Implement calculatePayments() method.
    }

    public function financeProducts(): Collection
    {
        try {
            $url = $this->endpoint . '/GetRetailerFinanceProducts';

            // Fetch loan products from the API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->get($url, [
                    'retailerId' => $this->retailerId,
                    'retailerguid' => $this->retailerGuid,
                ])
                ->throw();

            $this->requestData = null;
            $this->responseData = $response->json();

            // Return camel cased array
            return $response->collect('FinanceProducts')
                ->map(function ($product) {
                    return Arr::mapWithKeys($product, function ($value, $key) {
                        if ($key === 'APR') {
                            return ['apr' => $value];
                        }

                        return [Str::camel($key) => $value];
                    });
                });

        } catch (\Throwable $ex) {
            Log::channel('payment')->error('Failed to retrieve payments from API.');
            Log::channel('payment')->error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            Log::channel('payment')->error('URL: ' . $url);
//            Log::channel('payment')->error('Data: ' . print_r($data, true));
            throw $ex;
        }
    }

    public function getRequestData(): ?array
    {
        return $this->requestData;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    public function healthCheck(): bool
    {
        $url = $this->endpoint . '/GetRetailerFinanceProducts';

        // Fetch loan products from the API
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->get($url, [
            'retailerId' => $this->retailerId,
            'retailerguid' => $this->retailerGuid,
        ]);

        $this->requestData = null;
        $this->responseData = $response->json();

        return $response->ok();
    }

    public function prequal(PaymentSurvey $survey, float $totalCost): PrequalPromiseData|PrequalData
    {
        // TODO: Implement prequal() method.
    }

    public function cancelOffer(PaymentOffer $paymentOffer): void
    {
        // Stub to satisfy interface, no action required.
    }
}
