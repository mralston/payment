<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Mralston\Payment\Models\PaymentStage;

class PaymentStageTableSeeder extends Seeder
{
    public function run(): void
    {
        PaymentStage::create([
            'name' => 'Prequal',
            'description' => 'The prequal stage',
            'identifier' => 'prequal',
        ]);

        PaymentStage::create([
            'name' => 'Apply',
            'description' => 'The apply stage',
            'identifier' => 'apply',
        ]);

        PaymentStage::create([
            'name' => 'Cancel',
            'description' => 'The cancel stage',
            'identifier' => 'cancel',
        ]);

        PaymentStage::create([
            'name' => 'Status Poll',
            'description' => 'The status poll stage',
            'identifier' => 'status_poll',
        ]);

        PaymentStage::create([
            'name' => 'Sat Note Upload',
            'description' => 'The sat note upload stage',
            'identifier' => 'sat_note_upload',
        ]);
    }
}
