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
            $table->enum('deferred_type', ['bnpl_months', 'deferred_payments'])->nullable()->after('deferred');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_offers', function (Blueprint $table) {
            $table->dropColumn('deferred_type');
        });
    }
};
