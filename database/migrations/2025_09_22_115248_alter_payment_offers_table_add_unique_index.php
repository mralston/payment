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
        Schema::table('payment_offers', function (Blueprint $table) {
            $table->unique(
                ['provider_application_id', 'provider_offer_id'],
                'payment_offers_app_offer_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_offers', function (Blueprint $table) {
            $table->dropUnique('payment_offers_app_offer_unique');
        });
    }
};
