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
            $table->decimal('apr', 8, 1);
            $table->integer('term_min');
            $table->integer('term_max');
            $table->integer('term_step');
            $table->integer('term_default');
            $table->integer('deferred_min')->nullable();
            $table->integer('deferred_max')->nullable();
            $table->integer('deferred_step')->nullable();
            $table->integer('deferred_default')->nullable();
            $table->integer('sort_order')->nullable();
            $table->json('payment_options')->nullable();
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
