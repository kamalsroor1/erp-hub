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
        // 1. Stock Transfers (Between Warehouses, Branches, and Vans)
        if (!Schema::hasTable('stock_transfers')) {
            Schema::create('stock_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_number', 50)->unique()->index();
                $table->foreignId('from_store_id')->constrained('stores')->restrictOnDelete()->index();
                $table->foreignId('to_store_id')->constrained('stores')->restrictOnDelete()->index();
                $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
                $table->date('transfer_date')->index();
                $table->string('status', 50)->default('pending')->index(); // pending, confirmed, cancelled
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Stock Transfer Items
        if (!Schema::hasTable('stock_transfer_items')) {
            Schema::create('stock_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
                $table->decimal('quantity', 12, 3);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
    }
};
