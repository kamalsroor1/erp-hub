<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treasury_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique();
            $table->string('from_method', 30); // cash, instapay, e_wallet, visa, bank_transfer
            $table->string('to_method', 30);   // cash, instapay, e_wallet, visa, bank_transfer
            $table->decimal('amount', 12, 3);
            $table->decimal('transfer_fee', 12, 3)->default(0.000);
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['transfer_date', 'store_id']);
            $table->index('from_method');
            $table->index('to_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasury_transfers');
    }
};
