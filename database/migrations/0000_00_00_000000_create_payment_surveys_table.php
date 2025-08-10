<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_surveys', function (Blueprint $table) {
            $table->id();
            $table->morphs('parentable');
            $table->boolean('basic_questions_completed')->default(false);
            $table->boolean('lease_questions_completed')->default(false);
            $table->boolean('finance_questions_completed')->default(false);
            $table->json('customers');
            $table->json('addresses');
            $table->json('finance_responses')->nullable();
            $table->json('lease_responses')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_surveys');
    }
};
