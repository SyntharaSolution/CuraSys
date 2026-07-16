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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('phone')->nullable()->index();
            $table->string('secondary_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('NIC_Passport')->nullable()->index();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('customer_type')->default('retail'); // retail, wholesale, corporate, insurance_linked
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->integer('credit_terms_days')->default(0);
            $table->string('price_tier_discount_group')->nullable();
            $table->string('loyalty_card_no')->nullable()->index();
            $table->integer('loyalty_points_balance')->default(0);
            $table->foreignId('preferred_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('communication_opt_in')->default('sms'); // sms, email, whatsapp, none
            $table->text('tags')->nullable(); // comma-separated or json
            $table->text('notes')->nullable();
            $table->string('status')->default('active'); // active, blocked
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Bronze, Silver, Gold
            $table->decimal('min_spend_threshold', 12, 2);
            $table->decimal('points_multiplier', 5, 2)->default(1.00);
            $table->text('perk_description')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_loyalty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->integer('points_balance')->default(0);
            $table->foreignId('tier_id')->nullable()->constrained('loyalty_tiers')->nullOnDelete();
            $table->date('tier_effective_date')->nullable();
            $table->timestamps();
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->uuid('sale_uuid')->nullable();
            $table->string('type'); // earn, redeem, expire, adjust
            $table->integer('points');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('transaction_type'); // sale, payment, return, adjustment
            $table->string('reference_id')->nullable();
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamp('transaction_date')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method');
            $table->string('reference_no')->nullable();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('insurance_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact')->nullable();
            $table->text('contract_terms')->nullable();
            $table->decimal('co_pay_percent', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('customer_insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('insurance_company_id')->constrained('insurance_companies')->cascadeOnDelete();
            $table->string('policy_no');
            $table->string('member_no')->nullable();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->string('coverage_type')->nullable();
            $table->timestamps();
        });

        Schema::create('insurance_claims', function (Blueprint $table) {
            $table->id();
            $table->uuid('sale_uuid');
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('insurance_company_id')->constrained('insurance_companies')->cascadeOnDelete();
            $table->decimal('claim_amount', 12, 2);
            $table->decimal('co_pay_amount', 12, 2);
            $table->string('status')->default('pending'); // pending, submitted, approved, rejected, paid
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('customer_insurance_policies');
        Schema::dropIfExists('insurance_companies');
        Schema::dropIfExists('customer_payments');
        Schema::dropIfExists('customer_ledger');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('customer_loyalty');
        Schema::dropIfExists('loyalty_tiers');
        Schema::dropIfExists('customers');
    }
};
