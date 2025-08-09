<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Mralston\Payment\Models\PaymentStatus;

class PaymentStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentStatus::create([
            'identifier' => 'new',
            'name' => 'New'
        ]);

        PaymentStatus::create([
            'identifier' => 'declined',
            'name' => 'Declined'
        ]);

        PaymentStatus::create([
            'identifier' => 'pending',
            'name' => 'Pending'
        ]);

        PaymentStatus::create([
            'identifier' => 'referred',
            'name' => 'Referred'
        ]);

        PaymentStatus::create([
            'identifier' => 'conditional_accept',
            'name' => 'Conditional Accept'
        ]);

        PaymentStatus::create([
            'identifier' => 'accepted',
            'name' => 'Accepted'
        ]);

        PaymentStatus::create([
            'identifier' => 'documents_received',
            'name' => 'Documents Received'
        ]);

        PaymentStatus::create([
            'identifier' => 'snagged',
            'name' => 'Snagged'
        ]);

        PaymentStatus::create([
            'identifier' => 'parked',
            'name' => 'Parked'
        ]);

        PaymentStatus::create([
            'identifier' => 'customer_cancelled',
            'name' => 'Customer Cancelled'
        ]);

        PaymentStatus::create([
            'identifier' => 'payout_requested',
            'name' => 'Payout Requested'
        ]);

        PaymentStatus::create([
            'identifier' => 'active',
            'name' => 'Active'
        ]);

        PaymentStatus::create([
            'identifier' => 'live',
            'name' => 'Live'
        ]);

        PaymentStatus::create([
            'identifier' => 'expired',
            'name' => 'Expired'
        ]);

        PaymentStatus::create([
            'identifier' => 'NotFound',
            'name' => 'Not Found'
        ]);

        PaymentStatus::create([
            'identifier' => 'error',
            'name' => 'Error'
        ]);

        PaymentStatus::create([
            'identifier' => 'cancelled',
            'name' => 'Cancelled'
        ]);
    }
}
