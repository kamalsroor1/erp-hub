<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number', 50)->unique()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('purchase_date')->index();
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft')->index();
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid')->index();
            $table->decimal('subtotal', 12, 3)->default(0.000);
            $table->decimal('discount_amount', 12, 3)->default(0.000);
            $table->decimal('net_total', 12, 3)->default(0.000);
            $table->decimal('paid_amount', 12, 3)->default(0.000);
            $table->decimal('remaining_amount', 12, 3)->default(0.000);
            $table->string('supplier_invoice_ref', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('cost_price', 12, 3);
            $table->decimal('total_price', 12, 3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
