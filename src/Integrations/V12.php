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
use Mralston\Payment\Models\PaymentProduct;
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

    /**
     * @deprecated use calculatePaymentsForProduct())
     */
    public function calculatePayments(int $loanAmount, float $apr, int $loanTerm, ?int $deferredPeriod = null): array
    {
        // Method to satisfy stub.
        return [];
    }

    public function calculatePaymentsForProduct(int $loanAmount, PaymentProduct $product): array
    {
        return app(\Mralston\Payment\Services\PaymentCalculators\V12::class)
            ->calculate($loanAmount, $product);
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
//Log::info('No DB offers found for V12.');
                $products = $this->financeProducts();
//                Log::info('Fetched products found from V12 API', [$products]);
                $reference = $helper->getReference() . '-' . Str::of(Str::random(5))->upper();

                $offers = $products->map(function ($product) use ($survey, $paymentProvider, $reference, $totalCost, $amount, $deposit) {

                    // Create the product if it doesn't exist
                    $paymentProduct = $paymentProvider
                        ->paymentProducts()
                        ->withTrashed()
                        ->firstOrCreate([
                            'identifier' => 'v12_' . $product['apr'] . '_' . $product['months'] . ($product['deferredPeriod'] > 0 ? '+' . $product['deferredPeriod'] : ''),
                        ], [
                            'name' => 'V12 ' . $product['name'],
                            'description' => $product['description'],
                            'apr' => $product['apr'],
                            'term' => $product['months'],
                            'deferred' => $product['deferredPeriod'] > 0 ? $product['deferredPeriod'] : null,
                            'deferred_type' => $product['deferredPeriod'] > 0 ? 'bnpl_months' : null,
                            'provider_foreign_id' => $product['productGuid'],
                            'document_fee' => $product['documentFee'],
                            'document_fee_collection_month' => $product['documentFeeCollectionMonth'],
                            'document_fee_maximum' => $product['documentFeeMaximum'],
                            'document_fee_minimum' => $product['documentFeeMinimum'],
                            'document_fee_percentage' => $product['documentFeePercentage'],
                            'max_loan' => $product['maxLoan'],
                            'min_loan' => $product['minLoan'],
                            'monthly_rate' => $product['monthlyRate'],
                            'service_fee' => $product['serviceFee'],
                            'settlement_fee' => $product['settlementFee'],
                        ]);

                    $payments = V12Facade::calculatePaymentsForProduct($amount, $paymentProduct);


                    // If there are no payments, skip it
                    if (empty($payments)) {
                        Log::channel('payment')->debug('No payment calc for product', $product);
                        return null;
                    }
                    Log::info('Calculated payments for £' . $amount . ' with product #' . $paymentProduct->id. ': ' . $paymentProduct->name, $payments);

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
                            'first_payment' => $payments['firstPayment'],
                            'monthly_payment' => $payments['monthlyPayment'],
                            'final_payment' => $payments['finalPayment'],
                            'total_payable' => $payments['total'],
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
