<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // === جدول الباقات (Plans) ===
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                 // مجاني / أساسي / احترافي / مؤسسي
            $table->string('slug')->unique();                       // free / basic / pro / enterprise
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->integer('max_users')->default(1);
            $table->integer('max_stores')->default(1);
            $table->integer('max_items')->default(50);
            $table->integer('max_invoices_per_month')->default(100);
            $table->integer('max_storage_mb')->default(500);
            $table->json('features');                               // {"pos.access": true, "reports.advanced": false, ...}
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);          // لتمييز الباقة المميزة في صفحة التسعير
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // === جدول تعريف الفيتشرز (Plan Features Registry) ===
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();                        // pos.access, reports.advanced, blender.access
            $table->string('name');                                  // كاشير نقاط البيع
            $table->string('description')->nullable();
            $table->string('module');                                // sales, inventory, reports, finance, system
            $table->enum('type', ['boolean', 'limit', 'quota'])->default('boolean');
            $table->string('default_value')->default('false');
            $table->string('icon')->nullable();                     // 🛒
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('plans');
    }
};
