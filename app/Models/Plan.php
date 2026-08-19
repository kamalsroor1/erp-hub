<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'max_users',
        'max_stores',
        'max_items',
        'max_invoices_per_month',
        'max_storage_mb',
        'features',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'max_users' => 'integer',
        'max_stores' => 'integer',
        'max_items' => 'integer',
        'max_invoices_per_month' => 'integer',
        'max_storage_mb' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * هل الفيتشر محدد ونشط في هذه الباقة؟
     */
    public function hasFeature(string $key): bool
    {
        return isset($this->features[$key]) && $this->features[$key] === true;
    }

    /**
     * نطاق الباقات النشطة المعروضة للجمهور
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }
}
