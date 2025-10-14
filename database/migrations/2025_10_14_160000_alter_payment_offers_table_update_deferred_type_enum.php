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
            // 1. Temporarily modify the column to include new and old enum values
            DB::statement("ALTER TABLE `payment_offers` MODIFY COLUMN `deferred_type` enum('months','payments','bnpl_months','deferred_payments') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL AFTER `deferred`");

            // 2. Convert old values to new values using Eloquent/DB Builder
            DB::table('payment_offers')
                ->where('deferred_type', 'months')
                ->update(['deferred_type' => 'bnpl_months']);

            DB::table('payment_offers')
                ->where('deferred_type', 'payments')
                ->update(['deferred_type' => 'deferred_payments']);

            // 3. Permanently modify the column to only include the new enum values.
            DB::statement("ALTER TABLE `payment_offers` MODIFY COLUMN `deferred_type` enum('bnpl_months','deferred_payments') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL AFTER `deferred`");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_offers', function (Blueprint $table) {
            // 1. Temporarily modify the column back to allow the reverse update
            DB::statement("ALTER TABLE `payment_offers` MODIFY COLUMN `deferred_type` enum('months','payments','bnpl_months','deferred_payments') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL AFTER `deferred`");

            // 2. Reverse the update (new values back to old values)
            DB::table('payment_offers')
                ->where('deferred_type', 'bnpl_months')
                ->update(['deferred_type' => 'months']);

            DB::table('payment_offers')
                ->where('deferred_type', 'deferred_payments')
                ->update(['deferred_type' => 'payments']);

            // 3. Revert the column definition to its original state
            DB::statement("ALTER TABLE `payment_offers` MODIFY COLUMN `deferred_type` enum('months','payments') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL AFTER `deferred`");

        });
    }
};
