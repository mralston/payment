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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer('payment_type_id')->index();
            $table->nullableMorphs('parentable');
            $table->string('uuid', 36)->index();
            $table->string('reference', 50)->nullable()->index();
            $table->decimal('total_cost', 8, 2)->nullable();
            $table->decimal('amount', 8, 2)->nullable();
            $table->decimal('deposit', 8, 2)->nullable();
            $table->decimal('subsidy', 8, 2)->nullable();
            $table->integer('payment_offer_id')->nullable()->index();
            $table->integer('payment_provider_id')->nullable()->index();
            $table->integer('payment_product_id')->nullable()->index();
            $table->decimal('apr', 8, 1)->nullable();
            $table->integer('term')->nullable();
            $table->integer('deferred')->nullable();
            $table->decimal('upfront_payment', 8, 2)->default(0);
            $table->decimal('first_payment')->nullable();
            $table->decimal('monthly_payment', 8, 2)->nullable();
            $table->decimal('final_payment')->nullable();
            $table->decimal('total_payable', 8, 2)->nullable();
            $table->json('payments_breakdown')->nullable();
            $table->tinyInteger('eligible')->nullable();
            $table->tinyInteger('gdpr_opt_in')->nullable();
            $table->string('title')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('residential_status')->nullable();
            $table->string('nationality')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('dependants')->nullable();
            $table->boolean('bankrupt_or_iva')->default(0);
            $table->string('email_address')->nullable();
            $table->string('primary_telephone')->nullable();
            $table->string('secondary_telephone')->nullable();
            $table->json('addresses')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('employer_ibc_ref')->nullable();
            $table->string('employer_name')->nullable();
            $table->string('employer_telephone')->nullable();
            $table->json('employer_address')->nullable();
            $table->string('employer_company_type')->nullable();
            $table->date('employer_company_reg_date')->nullable();
            $table->string('occupation')->nullable();
            $table->integer('time_with_employer')->nullable()->comment("in months");
            $table->decimal('gross_income_individual', 10, 2)->nullable();
            $table->decimal('gross_income_household', 10, 2)->nullable();
            $table->decimal('net_monthly_income_individual', 10, 2)->nullable();
            $table->decimal('mortgage_monthly', 8, 2)->nullable();
            $table->decimal('rent_monthly', 8, 2)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_holder_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_sort_code')->nullable();
            $table->tinyInteger('read_terms_conditions')->nullable();
            $table->boolean('was_referred')->default(false);
            $table->integer('payment_status_id')->default(1)->index();
            $table->string('provider_foreign_id')->nullable();
            $table->text('provider_request_data')->nullable();
            $table->json('provider_response_data')->nullable();
            $table->date('offer_expiration_date')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decision_received_at')->nullable()->index();
            $table->timestamp('signed_at')->nullable();
            $table->integer('sat_note_file_id')->nullable()->index();
            $table->integer('credit_agreement_file_id')->nullable()->index();
            $table->tinyInteger('prevent_payment_changes')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Start new payment records from an ID safely clear of number of finance_application
        // records that will be populated from the old finance journey
        DB::statement('ALTER TABLE `payments` AUTO_INCREMENT = 100000');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
