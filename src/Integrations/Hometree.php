<?php

namespace Mralston\Payment\Integrations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mralston\Payment\Interfaces\LeaseGateway;
use Mralston\Payment\Interfaces\PaymentGateway;
use Mralston\Payment\Models\Payment;

class Hometree implements PaymentGateway, LeaseGateway
{
    /**
     * Endpoints to be used based on environment.
     *
     * @var array|string[]
     */
    private array $endpoints = [
        'local' => 'https://api.preprod.hometreefinance.dev/partner/v1.0',
        'dev' => 'https://api.preprod.hometreefinance.dev/partner/v1.0',
        'testing' => 'https://api.preprod.hometreefinance.dev/partner/v1.0',
        'production' => '',
    ];

    /**
     * API endpoint to send POST requests to.
     *
     * @var string
     */
    private string $endpoint;

    public function __construct(
        private string $key,
        string $endpoint,
    ) {
        $this->endpoint = $this->endpoints[$endpoint];
    }

    public function apply(Payment $application)
    {
        $quote = $application->quote;
        $systemSavings = $quote->system_savings->show;

        $payload = [
            'customer' => [
                'first_name' => $application->first_name,
                'middle_name' => $application->middle_name,
                'last_name' => $application->last_name,
            ],
            'address' => [
                'udprn' => $application->addresses->first()->udprn,
            ],
            'order' => [
                'lines' => $quote->basket
                    ->items
                    ->reject(function ($item) {
                        return $item->option_id == Option::NO || $item->quantity == 0;
                    })
                    ->reject(function ($item) {
                        return empty($item->option->sku ?? $item->tool->sku);
                    })
                    ->values()
                    ->map(function ($item) {
                        return [
                            'product' => [
                                'id' => $item->option->sku ?? $item->tool->sku,
                            ],
                            'quantity' => $item->quantity,
                            'details' => [
                                'warranty' => min($item->option->warranty_period ?? $item->tool->warranty_period, 25),
                                'grid_backup' => false,
                            ]
                        ];
                    })->toArray(),
                'details' => [
                    'immersion_diverter' => false,
                    'bird_blocker' => $quote->basket->contains(Tool::byIdentifier(ProductEnum::BIRD_PROTECTION)) ? true : false,
                    'scaffold' => $quote->basket->contains(Tool::byIdentifier(ProductEnum::ACCESS_EQUIP)) ? true : false,
                    'generation_year_1' => round($quote->sem ?? 0, 0),
                    'savings_year_1_solar' => round($systemSavings['total']['solar_panels'] ?? 0, 0),
                    'savings_year_1_ess' => round($systemSavings['total']['battery_storage'] ?? 0, 0),
                ],
                'price' => [
                    'net_value' => $quote->basket->net,
                    'vat' => $quote->basket->vat_rate,
                    'vat_value' => $quote->basket->vat,
                ]
            ],
            'applicants' => $application
                ->additionalCustomers
                ->map(function ($customer) use ($application) {
                    return [
                        'first_name' => $customer->firstName,
                        'middle_name' => $customer->middleName,
                        'last_name' => $customer->lastName,
                        'email' => $customer->email,
                        'mobile_phone_number' => $customer->phone,
                        'dob' => $customer->dateOfBirth,
                        'address' => [
                            'udprn' => $application->addresses->first()->udprn,
                        ],
                        'previous_address' => $application->addresses
                            ->skip(1)
                            ->values()
                            ->map(function ($address) {
                                return [
                                    'udprn' => $address->udprn,
                                ];
                            }),
                        'affordability' => [
                            'gross_annual_income' => $customer->grossAnnualIncome,
                            'dependants' => $customer->dependants,
                            'employment_status' => $customer->employmentStatus,
                        ]
                    ];
                }),
            'reference' => $quote->id,
        ];

//        dd(json_encode($payload, JSON_PRETTY_PRINT));

        try {
            $response = Http::baseUrl($this->endpoint)
                ->withToken($this->key)
                ->post('/applications', $payload)
                ->throw()
                ->json();
        } catch (\Throwable $ex) {
            Log::error('Failed to retrieve products from API.');
            Log::error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            Log::error('URL: ' . $this->endpoint . '/applications');
            throw $ex;
        }
    }

    public function getProducts(): array
    {
        // TODO: Clarify API docs - is GET /products fetching loan products or solar panels, batteries, etc

        try {
            $response = Http::baseUrl($this->endpoint)
                ->withToken($this->key)
                ->get('/products')
                ->throw()
                ->json();
        } catch (\Throwable $ex) {
            Log::error('Failed to retrieve products from API.');
            Log::error('Error #' . $ex->getCode() . ': ' . $ex->getMessage());
            Log::error('URL: ' . $this->endpoint . '/products');
            throw $ex;
        }

        return $response;
    }

}
