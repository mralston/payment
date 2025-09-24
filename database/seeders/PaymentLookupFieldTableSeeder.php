<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Mralston\Payment\Models\PaymentLookupField;

class PaymentLookupFieldTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentLookupField::create([
            'name' => 'Marital Status',
            'identifier' => 'marital_status',
        ])->paymentLookupValues()
            ->createMany([
                ['name' => 'Married', 'value' => 'married', 'payment_provider_values' => [
                    'tandem' => 'married',
                    'propensio' => 'M',
                ]],
                ['name' => 'Single', 'value' => 'single', 'payment_provider_values' => [
                    'tandem' => 'single',
                    'propensio' => 'S',
                ]],
                ['name' => 'Divorced', 'value' => 'divorced', 'payment_provider_values' => [
                    'tandem' => 'divorced',
                    'propensio' => 'D',
                ]],
                ['name' => 'Widowed', 'value' => 'widowed', 'payment_provider_values' => [
                    'tandem' => 'widowed',
                    'propensio' => 'W',
                ]],
                ['name' => 'Cohabiting', 'value' => 'cohabiting', 'payment_provider_values' => [
                    'tandem' => 'living_together',
                    'propensio' => 'N',
                ]],
                ['name' => 'Separated', 'value' => 'separated', 'payment_provider_values' => [
                    'tandem' => 'separated',
                    'propensio' => 'S',
                ]],
                ['name' => 'Other', 'value' => 'other', 'payment_provider_values' => [
                    'tandem' => 'other',
                    'propensio' => 'X',
                ]],
            ]);

        PaymentLookupField::create([
            'name' => 'Residential Status',
            'identifier' => 'residential_status',
        ])->paymentLookupValues()
            ->createMany([
                ['name' => 'Owned Outright', 'value' => 'owner', 'payment_provider_values' => [
                    'tandem' => 'homeowner_no_mortgage',
                    'propensio' => 'OWO',
                ]],
                ['name' => 'Owned with Mortgage', 'value' => 'mortgage', 'payment_provider_values' => [
                    'tandem' => 'homeowner_with_mortgage',
                    'propensio' => 'OWN',
                ]],
                ['name' => 'Tenant', 'value' => 'tenant', 'payment_provider_values' => [
                    'tandem' => 'tenant',
                    'propensio' => 'TEN',
                ]],
            ]);

        PaymentLookupField::create([
            'name' => 'Employment Status',
            'identifier' => 'employment_status',
        ])->paymentLookupValues()
            ->createMany([
                ['name' => 'Full Time Employed', 'value' => 'full_time_employed', 'payment_provider_values' => [
                    'tandem' => 'employed',
                    'hometree' => 100,
                    'propensio' => 'Employed',
                ]],
                ['name' => 'Part Time Employed', 'value' => 'part_time_employed', 'payment_provider_values' => [
                    'tandem' => 'part_time_employed',
                    'hometree' => 200,
                    'propensio' => 'Employed',
                ]],
                ['name' => 'Casual Worker', 'value' => 'casual_worker', 'payment_provider_values' => [
                    'tandem' => 'part_time_employed',
                    'hometree' => 300,
                    'propensio' => 'Employed',
                ]],
                ['name' => 'Self Employed', 'value' => 'self_employed', 'payment_provider_values' => [
                    'tandem' => 'self-employed',
                    'hometree' => 400,
                    'propensio' => 'Self-Employed',
                ]],
                ['name' => 'Household Duties or Carer', 'value' => 'household_carer', 'payment_provider_values' => [
                    'tandem' => 'unemployed',
                    'hometree' => 500,
                    'propensio' => 'Unemployed',
                ]],
                ['name' => 'Retired', 'value' => 'retired', 'payment_provider_values' => [
                    'tandem' => 'retired',
                    'hometree' => 600,
                    'propensio' => 'Retired',
                ]],
                ['name' => 'PIP', 'value' => 'pip', 'payment_provider_values' => [
                    'tandem' => 'pip',
                    'hometree' => 700,
                    'propensio' => 'Unemployed',
                ]],
                ['name' => 'Unemployed', 'value' => 'unemployed', 'payment_provider_values' => [
                    'tandem' => 'unemployed',
                    'hometree' => 700,
                    'propensio' => 'Unemployed',
                ]],
            ]);

        PaymentLookupField::create([
            'name' => 'Nationality',
            'identifier' => 'nationality',
        ])->paymentLookupValues()
            ->createMany([
                ['name' => 'British', 'value' => 'british', 'payment_provider_values' => [
                    'tandem' => 'british',
                ]],
                ['name' => 'Foreign', 'value' => 'foreign', 'payment_provider_values' => [
                    'tandem' => 'non-british',
                ]],
            ]);

        PaymentLookupField::create([
            'name' => 'Bankrupt or IVA',
            'identifier' => 'bankrupt_or_iva',
        ])->paymentLookupValues()
            ->createMany([
                ['name' => 'Yes', 'value' => 'yes', 'payment_provider_values' => [
                    'propensio' => 1,
                ]],
                ['name' => 'No', 'value' => 'no', 'payment_provider_values' => [
                    'propensio' => 0,
                ]],
            ]);

        PaymentLookupField::create([
            'name' => 'Personal Current Account for Business',
            'identifier' => 'current_account_for_business',
        ])->paymentLookupValues()
            ->createMany([
                ['name' => 'Yes', 'value' => 'yes', 'payment_provider_values' => [
                    'hometree' => 1,
                ]],
                ['name' => 'No', 'value' => 'no', 'payment_provider_values' => [
                    'hometree' => 0,
                ]],
            ]);

        PaymentLookupField::create([
            'name' => 'Title',
            'identifier' => 'title',
        ])->paymentLookupValues()
            ->createMany([
                ['name' => 'Mr', 'value' => 'Mr'],
                ['name' => 'Mrs', 'value' => 'Mrs'],
                ['name' => 'Ms', 'value' => 'Ms'],
                ['name' => 'Miss', 'value' => 'Miss'],
                ['name' => 'Mx', 'value' => 'Mx'],
                ['name' => 'Dr', 'value' => 'Dr'],
                ['name' => 'Lady', 'value' => 'Lady'],
                ['name' => 'Prof', 'value' => 'Prof'],
                ['name' => 'Rev', 'value' => 'Rev'],
                ['name' => 'Sir', 'value' => 'Sir'],
                ['name' => 'Prefer not to say', 'value' => 'Other'],
            ]);
    }
}
