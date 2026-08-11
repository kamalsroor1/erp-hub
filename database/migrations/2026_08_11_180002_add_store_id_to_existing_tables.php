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
        // 1. Invoices
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'store_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('user_id')->constrained('stores')->nullOnDelete()->index();
            });
        }

        // 2. Purchases
        if (Schema::hasTable('purchases') && !Schema::hasColumn('purchases', 'store_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('user_id')->constrained('stores')->nullOnDelete()->index();
            });
        }

        // 3. Expenses
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'store_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('user_id')->constrained('stores')->nullOnDelete()->index();
            });
        }

        // 4. Returns
        if (Schema::hasTable('returns') && !Schema::hasColumn('returns', 'store_id')) {
            Schema::table('returns', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('user_id')->constrained('stores')->nullOnDelete()->index();
            });
        }

        // 5. Cash Shifts
        if (Schema::hasTable('cash_shifts') && !Schema::hasColumn('cash_shifts', 'store_id')) {
            Schema::table('cash_shifts', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('user_id')->constrained('stores')->nullOnDelete()->index();
            });
        }

        // 6. Stock Movements
        if (Schema::hasTable('stock_movements') && !Schema::hasColumn('stock_movements', 'store_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreignId('store_id')->nullable()->after('user_id')->constrained('stores')->nullOnDelete()->index();
            });
        }

        // 7. Users (Default assigned store)
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'default_store_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('default_store_id')->nullable()->after('is_active')->constrained('stores')->nullOnDelete()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'default_store_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('default_store_id');
            });
        }

        if (Schema::hasTable('stock_movements') && Schema::hasColumn('stock_movements', 'store_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropConstrainedForeignId('store_id');
            });
        }

        if (Schema::hasTable('cash_shifts') && Schema::hasColumn('cash_shifts', 'store_id')) {
            Schema::table('cash_shifts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('store_id');
            });
        }

        if (Schema::hasTable('returns') && Schema::hasColumn('returns', 'store_id')) {
            Schema::table('returns', function (Blueprint $table) {
                $table->dropConstrainedForeignId('store_id');
            });
        }

        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'store_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('store_id');
            });
        }

        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'store_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropConstrainedForeignId('store_id');
            });
        }

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'store_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropConstrainedForeignId('store_id');
            });
        }
    }
};
