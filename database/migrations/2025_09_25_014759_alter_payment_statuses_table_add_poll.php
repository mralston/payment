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
        Schema::table('payment_statuses', function (Blueprint $table) {
            $table->boolean('poll')->default(false)->after('unlisted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_statuses', function (Blueprint $table) {
            $table->dropColumn('poll');
        });
    }
};
