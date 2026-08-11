<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'address',
        'current_balance',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_balance' => 'decimal:3',
            'is_active'       => 'boolean',
        ];
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class)->latest('purchase_date');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'supplier_id')->latest('payment_date');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
