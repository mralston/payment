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
        Schema::create('payment_products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('identifier')->unique();
            $table->integer('payment_provider_id')->index();
            $table->string('provider_foreign_id')->nullable();
            $table->decimal('apr', 8, 1);
            $table->integer('term');
            $table->integer('deferred')->nullable();
            $table->integer('sort_order')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_products');
    }
};
