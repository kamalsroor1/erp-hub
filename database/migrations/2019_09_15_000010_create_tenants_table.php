<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // === بيانات المستأجر الأساسية ===
            $table->string('name');                     // اسم الشركة / المحل
            $table->string('slug')->unique();           // الـ Subdomain: ahmed.sroor-erp.com
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->string('commercial_register')->nullable();
            $table->string('tax_number')->nullable();

            // === الباقة والاشتراك ===
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->enum('status', ['active', 'trial', 'suspended', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            // === إعدادات مخصصة ===
            $table->json('settings')->nullable();           // إعدادات المستأجر (ثيم، طباعة، إلخ)
            $table->json('enabled_features')->nullable();   // فيتشرز مفعلة يدوياً (Override)

            $table->timestamps();
            $table->json('data')->nullable();               // مطلوب من stancl/tenancy
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
