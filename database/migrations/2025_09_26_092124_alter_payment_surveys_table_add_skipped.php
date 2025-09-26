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
        Schema::table('payment_surveys', function (Blueprint $table) {
            $table->boolean('skipped')->nullable()->after('parentable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_surveys', function (Blueprint $table) {
            $table->dropColumn('skipped');
        });
    }
};
