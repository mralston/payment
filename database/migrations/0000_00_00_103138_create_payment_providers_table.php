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
        Schema::create('payment_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('identifier')->unique();
            $table->string('gateway')->nullable();
            $table->string('logo')->nullable();
            $table->string('intro', 1000)->nullable();
            $table->integer('sort_order')->nullable();
            $table->string('telephone')->nullable();
            $table->string('website')->nullable();
            $table->string('underwriter_email')->nullable();
            $table->string('sat_note_email')->nullable();
            $table->string('privacy_policy')->nullable();
            $table->boolean('epvs_validated')->default(false);
            $table->integer('payment_type_id')->nullable();
            $table->integer('epvs_finance_lender_id')->nullable();
            $table->decimal('max_deposit_percent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_providers');
    }
};
