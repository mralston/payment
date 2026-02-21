<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_cancellations', function (Blueprint $table) {
            $table->json('lender_response_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payment_cancellations', function (Blueprint $table) {
            $table->dropColumn('lender_response_data');
        });
    }
};
