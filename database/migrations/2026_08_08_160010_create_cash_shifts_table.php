<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('shift_number', 50)->unique()->index();
            $table->enum('status', ['open', 'closed'])->default('open')->index();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_cash_balance', 12, 3)->default(0.000);
            $table->decimal('total_cash_sales', 12, 3)->default(0.000);
            $table->decimal('total_credit_sales', 12, 3)->default(0.000);
            $table->decimal('total_payments_collected', 12, 3)->default(0.000);
            $table->decimal('total_refunds', 12, 3)->default(0.000);
            $table->decimal('expected_cash_balance', 12, 3)->default(0.000);
            $table->decimal('actual_cash_balance', 12, 3)->default(0.000);
            $table->decimal('cash_difference', 12, 3)->default(0.000);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_shifts');
    }
};
