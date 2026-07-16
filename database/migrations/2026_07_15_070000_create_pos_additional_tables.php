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
        Schema::create('registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('register_id')->constrained('registers')->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('opening_amount', 12, 2)->default(0);
            $table->decimal('expected_cash', 12, 2)->default(0);
            $table->decimal('counted_cash', 12, 2)->default(0);
            $table->decimal('variance', 12, 2)->default(0);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_session_id')->constrained('cash_sessions')->cascadeOnDelete();
            $table->enum('type', ['sale', 'refund', 'drop', 'payout', 'float_add']);
            $table->decimal('amount', 12, 2);
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('held_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('register_id')->constrained('registers')->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->longText('cart_json');
            $table->timestamp('held_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('sale_uuid');
            $table->foreign('sale_uuid')->references('uuid')->on('sales')->cascadeOnDelete();
            $table->string('method'); // Cash, Card, Bank Transfer, Insurance Claim, Wallet, Store Credit
            $table->decimal('amount', 12, 2);
            $table->string('reference_no')->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->unsignedBigInteger('insurance_claim_id')->nullable();
            $table->timestamps();
        });

        Schema::create('discount_approvals', function (Blueprint $table) {
            $table->id();
            $table->uuid('sale_uuid');
            $table->foreign('sale_uuid')->references('uuid')->on('sales')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('discount_percent', 5, 2);
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_reprints', function (Blueprint $table) {
            $table->id();
            $table->uuid('sale_uuid');
            $table->foreign('sale_uuid')->references('uuid')->on('sales')->cascadeOnDelete();
            $table->foreignId('reprinted_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->timestamps();
        });

        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->uuid('original_sale_uuid');
            $table->foreign('original_sale_uuid')->references('uuid')->on('sales')->cascadeOnDelete();
            $table->string('return_invoice_no')->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('reason');
            $table->decimal('total_refund', 12, 2);
            $table->string('refund_method');
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->string('exchange_group_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
        Schema::dropIfExists('invoice_reprints');
        Schema::dropIfExists('discount_approvals');
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('held_bills');
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('cash_sessions');
        Schema::dropIfExists('registers');
    }
};
