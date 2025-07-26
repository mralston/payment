<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_offers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('type');
            $table->integer('payment_survey_id')->index();
            $table->integer('amount');
            $table->integer('payment_provider_id')->index();
            $table->decimal('apr', 8, 1)->nullable();
            $table->integer('term');
            $table->integer('deferred')->nullable();
            $table->decimal('first_payment', 8, 2);
            $table->decimal('monthly_payment', 8, 2);
            $table->decimal('final_payment', 8, 2);
            $table->json('minimum_payments')->nullable();
            $table->string('status');
            $table->string('preapproval_id')->nullable();
            $table->integer('priority')->nullable();
            $table->string('provider_foreign_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_offers');
    }
};
