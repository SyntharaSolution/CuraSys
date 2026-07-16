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
        // Alter sales table
        Schema::table('sales', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->index();
            $table->foreignId('register_id')->nullable()->constrained('registers')->nullOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained('cash_sessions')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('prescription_id')->nullable();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('round_off', 5, 2)->default(0);
            $table->decimal('net_total', 12, 2)->default(0);
            $table->string('sale_type')->default('walk_in'); // walk_in, prescription, insurance
            // Change status to string for sqlite compatibility
            $table->string('status')->default('completed')->change();
        });

        // Alter sale_items table
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->integer('dispensed_qty')->default(0);
            $table->integer('return_qty')->default(0);
        });

        // Alter medicine_batches table
        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('grn_id')->nullable()->constrained('goods_received_notes')->nullOnDelete();
            $table->date('mfg_date')->nullable();
            $table->integer('qty_received')->default(0);
            $table->integer('qty_available')->default(0);
            $table->decimal('landed_unit_cost', 12, 2)->default(0);
            $table->string('status')->default('active'); // active, quarantine, rejected, expired
        });

        // Alter purchase_items table
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->integer('received_qty_cumulative')->default(0);
            $table->string('status')->default('pending'); // pending, partial, fulfilled, closed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite doesn't easily support dropping columns in older laravel versions,
        // but we'll do our best for down method.
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['received_qty_cumulative', 'status']);
        });

        Schema::table('medicine_batches', function (Blueprint $table) {
            $table->dropColumn(['supplier_id', 'grn_id', 'mfg_date', 'qty_received', 'qty_available', 'landed_unit_cost', 'status']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['discount', 'tax', 'line_total', 'verified_by', 'verified_at', 'dispensed_qty', 'return_qty']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_no', 'register_id', 'cash_session_id', 'customer_id', 
                'doctor_id', 'prescription_id', 'cashier_id', 'subtotal', 
                'discount_amount', 'tax_amount', 'round_off', 'net_total', 'sale_type'
            ]);
        });
    }
};
