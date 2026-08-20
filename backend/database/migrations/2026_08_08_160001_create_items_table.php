<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->index();
            $table->string('name')->index();
            $table->string('category', 100)->nullable()->index();
            $table->string('unit', 50)->default('كجم');
            $table->decimal('current_stock', 12, 3)->default(0.000);
            $table->decimal('cost_price', 12, 3)->default(0.000);
            $table->decimal('weighted_avg_cost', 12, 3)->default(0.000);
            $table->decimal('selling_price', 12, 3)->default(0.000);
            $table->decimal('min_stock_level', 12, 3)->default(5.000);
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
