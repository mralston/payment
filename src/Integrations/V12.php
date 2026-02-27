<?php

namespace Mralston\Payment\Integrations;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mralston\Payment\Data\PrequalData;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Events\OffersReceived;
use Mralston\Payment\Facades\V12 as V12Facade;
use Mralston\Payment\Interfaces\FinanceGateway;
use Mralston\Payment\Interfaces\PaymentGateway;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PrequalifiesCustomer;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentProvider;
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
        return [
            'term' => $loanTerm,
            'firstPayment' => 0,
            'monthlyPayment' => 0,
            'finalPayment' => 0,
            'total' => 0,
            'apr' => $apr,
            'amt' => round($loanAmount, 2),
            'interest' => round(0, 2)
        ];
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
        dispatch(function () use ($survey, $totalCost) {
            $helper = app(PaymentHelper::class)
                ->setParentModel($survey->parentable);

            $paymentProvider = PaymentProvider::byIdentifier('v12');

            $deposit = $survey->finance_deposit;
            $amount = $totalCost - $deposit;

            // See if there are already offers
            $offers = $survey
                ->paymentOffers()
                ->where('payment_provider_id', $paymentProvider->id)
                ->where('total_cost', $totalCost)
                ->where('amount', $amount)
                ->where('deposit', $deposit)
                ->where('monthly_payment', '>', 0)
                ->where('selected', false);

            $offers = $offers->get();

            // If there aren't any offers...
            if ($offers->isEmpty()) {

                $products = $this->financeProducts();

                $reference = $helper->getReference() . '-' . Str::of(Str::random(5))->upper();

                $offers = $products->map(function ($product) use ($survey, $paymentProvider, $reference, $totalCost, $amount, $deposit) {
                    $payments = V12Facade::calculatePayments($amount, $product['apr'], $product['months'], $product['deferredPeriod']);

                    // If there are no payments, skip it
                    if (empty($payments)) {
                        Log::channel('payment')->debug('No payment calc for product', $product);
                        return null;
                    }

                    // Create the product if it doesn't exist
                    $paymentProduct = $paymentProvider
                        ->paymentProducts()
                        ->withTrashed()
                        ->firstOrCreate([
                            'identifier' => 'v12_' . $product['apr'] . '_' . $product['months'] . ($product['deferredPeriod'] > 0 ? '+' . $product['deferredPeriod'] : ''),
                        ], [
                            'name' => 'V12 ' . $product['name'],
                            'apr' => $product['apr'],
                            'term' => $product['months'],
                            'deferred' => $product['deferredPeriod'] > 0 ? $product['deferredPeriod'] : null,
                            'deferred_type' => $product['deferredPeriod'] > 0 ? 'bnpl_months' : null,
                        ]);

                    // If the product has been soft deleted, don't store the offer
                    // This allows us to disable products we don't want to offer to customers
                    if ($paymentProduct->trashed()) {
                        Log::channel('payment')->debug('V12 product soft deleted', $product);
                        return null;
                    }

                    // Create the offer
                    return $survey->parentable
                        ->paymentOffers()
                        ->create([
                            'payment_survey_id' => $survey->id,
                            'payment_product_id' => $paymentProduct->id,
                            'payment_provider_id' => $paymentProvider->id,
                            'name' => 'V12 ' . $product['name'],
                            'type' => 'finance',
                            'reference' => $reference,
                            'total_cost' => $totalCost,
                            'amount' => $amount,
                            'deposit' => $deposit,
                            'apr' => $product['apr'],
                            'term' => $product['months'],
                            'deferred' => $product['deferredPeriod'] > 0 ? $product['deferredPeriod'] : null,
                            'deferred_type' => $product['deferredPeriod'] > 0 ? 'bnpl_months' : null,
                            'first_payment' => 0, //$payments['RepaymentDetails']['FirstRepaymentAmount'],
                            'monthly_payment' => 0, //$payments['RepaymentDetails']['MonthlyRepayment'],
                            'final_payment' => 0, //$payments['RepaymentDetails']['FinalRepaymentAmount'],
                            'total_payable' => 0, //$payments['FinancialDetails']['TotalPayable'],
                            'status' => 'final',
                        ]);
                })
                    ->reject(fn ($offer) => is_null($offer));
            }

            event(new OffersReceived(
                gateway: static::class,
                type: 'finance',
                surveyId: $survey->id,
                offers: $offers,
            ));
        });

        return new PrequalPromiseData(
            gateway: static::class,
            type: 'finance',
            surveyId: $survey->id,
        );
    }

    public function cancelOffer(PaymentOffer $paymentOffer): void
    {
        // Stub to satisfy interface, no action required.
    }
}
