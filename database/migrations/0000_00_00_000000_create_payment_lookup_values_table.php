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
        Schema::create('payment_lookup_values', function (Blueprint $table) {
            $table->id();
            $table->integer('payment_lookup_field_id')->index();
            $table->string('name');
            $table->string('value');
            $table->json('payment_provider_values')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_lookup_values');
    }
};
