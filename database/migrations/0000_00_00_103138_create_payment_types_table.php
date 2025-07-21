<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mralston\Payment\Models\PaymentType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('payment_types');

        Schema::create('payment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('identifier')->unique();
            $table->timestamps();
        });

        PaymentType::create([
            'name' => 'Cash',
            'identifier' => 'cash',
        ]);

        PaymentType::create([
            'name' => 'Finance',
            'identifier' => 'finance',
        ]);

        PaymentType::create([
            'name' => 'Leads',
            'identifier' => 'lease',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_types');
    }
};
