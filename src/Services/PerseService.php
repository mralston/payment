<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mralston\Mug\Facades\Mug;
use Mralston\Payment\Data\AddressLookupResultData;
use Mralston\Payment\Data\AddressLookupResultsData;
use Mralston\Payment\Interfaces\PaymentAddressLookup;

class PerseService implements PaymentAddressLookup
{
    protected array $endpoints = [
        'local' => 'https://sandbox.api.thelabrador.co.uk',
        'testing' => 'https://sandbox.api.thelabrador.co.uk',
        'dev' => 'https://sandbox.api.thelabrador.co.uk',
        'staging' => 'https://sandbox.api.thelabrador.co.uk',
        'production' => 'https://api.thelabrador.co.uk',
    ];

    protected string $endpoint;
    protected string $key;

    public function __construct()
    {
        $this->endpoint = $this->endpoints[config('payment.perse.endpoint')];
        $this->key = config('payment.perse.api_key');
    }

    /**
     * Fetches addresses matching the given postcode.
     * Note this method incurs N+1 API lookups to fetch the UPRN.
     * @return Collection<AddressLookupResultData>
     */
    public function lookup(string $postCode): Collection
    {
        return Http::withHeaders([
            'x-api-key' => $this->key,
        ])->get($this->endpoint . '/meter/v2/addresses', [
            'postCode' => $postCode,
        ])->collect('data')
            ->map(function ($address) {

                $parsed = app(UkAddressParser::class)->parse($address['text']);

                $asset = Http::withHeaders([
                    'x-api-key' => $this->key,
                ])->get($this->endpoint . '/meter/v2/asset', [
                    'addressId' => $address['addressId'],
                ])->json('data');

                return new AddressLookupResultData(
                    uprn: intval($asset['uprn']),
                    latitude: null,
                    longitude: null,
                    summary: $address['text'],
                    houseNumber: $parsed['houseNumber'],
                    street: $parsed['street'],
                    address1: $parsed['address1'],
                    address2: $parsed['address2'],
                    town: $parsed['town'],
                    county: $parsed['county'],
                    postCode: $parsed['postCode'],
                );
            });
    }
}
