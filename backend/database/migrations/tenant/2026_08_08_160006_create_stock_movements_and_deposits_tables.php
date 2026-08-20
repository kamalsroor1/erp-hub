<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete()->index();
            $table->string('movement_type', 50)->index(); // sales_out, purchase_in, sales_return_in, etc.
            $table->decimal('quantity', 12, 3);
            $table->decimal('stock_before', 12, 3);
            $table->decimal('stock_after', 12, 3);
            $table->decimal('unit_cost', 12, 3);
            $table->string('source_type', 255)->index();
            $table->unsignedBigInteger('source_id')->index();
            $table->string('document_number', 100)->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        Schema::create('stock_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('deposit_type', ['opening_balance', 'manual_deposit', 'adjustment'])->default('manual_deposit');
            $table->decimal('quantity', 12, 3);
            $table->decimal('cost_price', 12, 3);
            $table->string('reason')->nullable();
            $table->date('deposit_date')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_deposits');
        Schema::dropIfExists('stock_movements');
    }
};
