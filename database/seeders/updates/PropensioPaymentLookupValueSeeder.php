<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Mralston\Payment\Models\PaymentLookupField;
use Mralston\Payment\Models\PaymentLookupValue;

class PropensioPaymentLookupValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $updates = [
            'marital_status' => [
                'married' => 'M',
                'single' => 'S',
                'divorced' => 'D',
                'widowed' => 'W',
                'cohabiting' => 'N',
                'separated' => 'S',
                'other' => 'X',
            ],
            'residential_status' => [
                'owner' => 'H',
                'mortgage' => 'M',
                'tenant' => 'T',
            ],
            'employment_status' => [
                'full_time_employed' => 'E',
                'part_time_employed' => 'P',
                'casual_worker' => 'U',
                'self_employed' => 'S',
                'household_carer' => 'U',
                'retired' => 'R',
                'pip' => 'U',
                'unemployed' => 'U',
            ],
            'bankrupt_or_iva' => [
                'yes' => 1,
                'no' => 0,
            ],
            'title' => [
                'Mr' => 'MR',
                'Mrs' => 'MRS',
                'Ms' => 'MS',
                'Miss' => 'MISS',
                'Mx' => 'MX',
                'Dr' => 'DR',
                'Lady' => 'MRS',
                'Prof' => 'PROF',
                'Rev' => 'REV',
                'Sir' => 'MR',
            ],
        ];

        foreach ($updates as $identifier => $values) {
            $field = PaymentLookupField::where('identifier', $identifier)->first();

            if (! $field) {
                continue;
            }

            foreach ($values as $value => $propensioValue) {
                $lookupValue = PaymentLookupValue::where('payment_lookup_field_id', $field->id)
                    ->where('value', $value)
                    ->first();

                if ($lookupValue) {
                    $paymentProviderValues = $lookupValue->payment_provider_values ?? collect();
                    $paymentProviderValues['propensio'] = $propensioValue;

                    $lookupValue->payment_provider_values = $paymentProviderValues;
                    $lookupValue->save();
                }
            }
        }
    }
}