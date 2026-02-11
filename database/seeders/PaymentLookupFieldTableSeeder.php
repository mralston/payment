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
                    'propensio' => 'H',
                ]],
                ['name' => 'Owned with Mortgage', 'value' => 'mortgage', 'payment_provider_values' => [
                    'tandem' => 'homeowner_with_mortgage',
                    'propensio' => 'M',
                ]],
                ['name' => 'Tenant', 'value' => 'tenant', 'payment_provider_values' => [
                    'tandem' => 'tenant',
                    'propensio' => 'T',
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
                    'propensio' => 'E',
                ]],
                ['name' => 'Part Time Employed', 'value' => 'part_time_employed', 'payment_provider_values' => [
                    'tandem' => 'part_time_employed',
                    'hometree' => 200,
                    'propensio' => 'P',
                ]],
                ['name' => 'Casual Worker', 'value' => 'casual_worker', 'payment_provider_values' => [
                    'tandem' => 'part_time_employed',
                    'hometree' => 300,
                    'propensio' => 'U',
                ]],
                ['name' => 'Self Employed', 'value' => 'self_employed', 'payment_provider_values' => [
                    'tandem' => 'self-employed',
                    'hometree' => 400,
                    'propensio' => 'S',
                ]],
                ['name' => 'Household Duties or Carer', 'value' => 'household_carer', 'payment_provider_values' => [
                    'tandem' => 'unemployed',
                    'hometree' => 500,
                    'propensio' => 'U',
                ]],
                ['name' => 'Retired', 'value' => 'retired', 'payment_provider_values' => [
                    'tandem' => 'retired',
                    'hometree' => 600,
                    'propensio' => 'R',
                ]],
                ['name' => 'PIP', 'value' => 'pip', 'payment_provider_values' => [
                    'tandem' => 'pip',
                    'hometree' => 700,
                    'propensio' => 'U',
                ]],
                ['name' => 'Unemployed', 'value' => 'unemployed', 'payment_provider_values' => [
                    'tandem' => 'unemployed',
                    'hometree' => 700,
                    'propensio' => 'U',
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
                ['name' => 'Mr', 'value' => 'Mr', 'payment_provider_values' => [
                    'propensio' => 'MR',
                ]],
                ['name' => 'Mrs', 'value' => 'Mrs', 'payment_provider_values' => [
                    'propensio' => 'MRS',
                ]],
                ['name' => 'Ms', 'value' => 'Ms', 'payment_provider_values' => [
                    'propensio' => 'MS',
                ]],
                ['name' => 'Miss', 'value' => 'Miss', 'payment_provider_values' => [
                    'propensio' => 'MISS',
                ]],
                ['name' => 'Mx', 'value' => 'Mx', 'payment_provider_values' => [
                    'propensio' => 'MX',
                ]],
                ['name' => 'Dr', 'value' => 'Dr', 'payment_provider_values' => [
                    'propensio' => 'DR',
                ]],
                ['name' => 'Lady', 'value' => 'Lady', 'payment_provider_values' => [
                    'propensio' => 'MRS',
                ]],
                ['name' => 'Prof', 'value' => 'Prof', 'payment_provider_values' => [
                    'propensio' => 'PROF',
                ]],
                ['name' => 'Rev', 'value' => 'Rev', 'payment_provider_values' => [
                    'propensio' => 'REV',
                ]],
                ['name' => 'Sir', 'value' => 'Sir', 'payment_provider_values' => [
                    'propensio' => 'MR',
                ]],
                ['name' => 'Prefer not to say', 'value' => 'Other'],
            ]);

        PaymentLookupField::create([
            'name' => 'Status',
            'identifier' => 'status',
        ])->paymentLookupValues()
            ->createMany([
                ['name' => 'Cancelled', 'value' => 'cancelled', 'payment_provider_values' => [
                    'propensio' => ['WSFcustomernpw', 'WSFcancelledpostpayo', 'WSFwithdrawnpostpayo'],
                ]],
                ['name' => 'Pending', 'value' => 'pending', 'payment_provider_values' => [
                    'propensio' => ['WSFpendingfurtherinf'],
                ]],
                //not sure if ive translated this one correctly
                ['name' => 'Accepted', 'value' => 'accepted', 'payment_provider_values' => [
                    'propensio' => ['WSFexecuted', 'WSFautoaccept', 'WSFmanualaccept'],
                ]],
                ['name' => 'Referred', 'value' => 'referred', 'payment_provider_values' => [
                    'propensio' => ['WSFsanction', 'WSFmanualrefer'],
                ]],
                ['name' => 'Declined', 'value' => 'declined', 'payment_provider_values' => [
                    'propensio' => ['WSFmanualdecline', 'WSFautodecline'],
                ]],
                ['name' => 'Live', 'value' => 'live', 'payment_provider_values' => [
                    'propensio' => ['WSFpaidout'],
                ]],
                ['name' => 'Expired', 'value' => 'expired', 'payment_provider_values' => [
                    'propensio' => ['WSFquoteexpired', 'WSFappexpired'],
                ]],
            ]);
    }
}
