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
        Schema::table('payment_products', function (Blueprint $table) {
            $table->decimal('monthly_rate', 8, 4)->nullable()->after('apr');
            $table->decimal('service_fee', 8, 2)->default(0)->after('deferred');
            $table->decimal('document_fee', 8, 2)->default(0)->after('service_fee');
            $table->decimal('document_fee_percentage', 8, 4)->default(0)->after('document_fee');
            $table->decimal('document_fee_minimum', 8, 2)->default(0)->after('document_fee_percentage');
            $table->decimal('document_fee_maximum', 8, 2)->default(0)->after('document_fee_minimum');
            $table->integer('document_fee_collection_month')->default(0)->after('document_fee_maximum');
            $table->decimal('min_loan', 12, 2)->default(0)->after('document_fee_collection_month');
            $table->decimal('max_loan', 12, 2)->default(0)->after('min_loan');
            $table->decimal('settlement_fee', 8, 2)->default(0)->after('max_loan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_products', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_rate',
                'service_fee',
                'document_fee',
                'document_fee_percentage',
                'document_fee_minimum',
                'document_fee_maximum',
                'document_fee_collection_month',
                'min_loan',
                'max_loan',
                'settlement_fee',
                'product_guid',
            ]);
        });
    }
};
