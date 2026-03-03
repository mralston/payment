<?php

namespace Mralston\Payment\Integrations;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mralston\Payment\Data\AddressData;
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
        $helper = app(PaymentHelper::class)
            ->setParentModel($payment->parentable);

        $currentAddress = AddressData::from($payment->addresses->first());

        $this->requestData = [
            'Customer' => [
                'EmailAddress' => $payment->email_address,
                'Title' => '3',
                'FirstName' => $payment->first_name,
                'LastName' => $payment->last_name,
                'Addresses' => [
                    [
//                        'FlatNumber' => '',
//                        'BuildingName' => '',
                        'BuildingNumber' => $currentAddress->houseNumber,
                        'Street' => $currentAddress->street,
//                        'Locality' => null,
                        'TownOrCity' => $currentAddress->town,
                        'County' => $currentAddress->county,
                        'Postcode' => $currentAddress->postCode
                    ]
                ],
                'HomeTelephone' => [
                    'Code' => $this->phonePrefix($this->landline($payment)),
                    'Number' => $this->phoneSuffix($this->landline($payment))
                ],
                'MobileTelephone' => [
                    'Code' => $this->phonePrefix($this->mobile($payment)),
                    'Number' => $this->phoneSuffix($this->mobile($payment))
                ]
            ],
            'Order' => [
                'Lines' => [
                    [
                        'Qty' => '1',
                        'SKU' => 'testSKU1',
                        'Item' => 'testItemName1',
                        'Price' => '600.00'
                    ],
                    [
                        'Qty' => '1',
                        'SKU' => 'testSKU2',
                        'Item' => 'testItemName2',
                        'Price' => '400.00'
                    ]
                ],
                'CashPrice' => $payment->amount,
                'Deposit' => $payment->deposit,
                'DuplicateSalesReferenceMethod' => 'ShowError',
                'ProductId' => $payment->paymentProduct->provider_foreign_id,
                'ProductGuid' => $payment->paymentProduct->provider_foreign_uuid,
                'SalesReference' => $payment->reference,
                'vLink' => false
            ],
            'Retailer' => [
                'AuthenticationKey' => $this->key,
                'RetailerGuid' => $this->retailerGuid,
                'RetailerId' => $this->retailerId
            ]
        ];

        dd($this->requestData);
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

                $products = $this->financeProducts();

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
                            'provider_foreign_id' => $product['productId'],
                            'provider_foreign_uuid' => $product['productGuid'],
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

    protected function phonePrefix(?string $phoneNumber): ?string
    {
        if (is_null($phoneNumber)) {
            return null;
        }

        if (preg_match('/^(01\d1)/', $phoneNumber, $matches)) {
            return Str::of($phoneNumber)->substr(0, 4);
        }

        return Str::of($phoneNumber)->substr(0, 5);
    }

    protected function phoneSuffix(?string $phoneNumber): ?string
    {
        if (empty($phoneNumber)) {
            return null;
        }

        if (preg_match('/^(01\d1)/', $phoneNumber, $matches)) {
            return Str::of($phoneNumber)->substr(4);
        }

        return Str::of($phoneNumber)->substr(5);
    }

    protected function landline(Payment $payment)
    {
        if (
            !empty($payment->primary_telephone) &&
            preg_match('/^0[^7]/', $payment->primary_telephone)
        ) {
            return $payment->primary_telephone;
        }

        if (
            !empty($payment->secondary_telephone) &&
            preg_match('/^0[^7]/', $payment->secondary_telephone)
        ) {
            return $payment->secondary_telephone;
        }
    }

    protected function mobile(Payment $payment)
    {
        if (
            !empty($payment->primary_telephone) &&
            preg_match('/^07/', $payment->primary_telephone)
        ) {
            return $payment->primary_telephone;
        }

        if (
            !empty($payment->secondary_telephone) &&
            preg_match('/^07/', $payment->secondary_telephone)
        ) {
            return $payment->secondary_telephone;
        }
    }
}
