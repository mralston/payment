<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('finance_surveys', function (Blueprint $table) {
            $table->id();
            $table->morphs('parentable');
            $table->json('customers');
            $table->json('addresses');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_surveys');
    }
};
