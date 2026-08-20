<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique()->index();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('invoice_date')->index();
            $table->enum('payment_type', ['cash', 'credit', 'partial'])->default('cash');
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft')->index();
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid')->index();
            $table->decimal('subtotal', 12, 3)->default(0.000);
            $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('discount_value', 12, 3)->default(0.000);
            $table->decimal('discount_amount', 12, 3)->default(0.000);
            $table->decimal('net_total', 12, 3)->default(0.000)->index();
            $table->decimal('paid_amount', 12, 3)->default(0.000);
            $table->decimal('remaining_amount', 12, 3)->default(0.000);
            $table->decimal('total_cost', 12, 3)->default(0.000);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('cost_price', 12, 3);
            $table->decimal('unit_price', 12, 3);
            $table->decimal('discount_amount', 12, 3)->default(0.000);
            $table->decimal('total_price', 12, 3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
