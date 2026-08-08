<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'unit',
        'current_stock',
        'cost_price',
        'weighted_avg_cost',
        'selling_price',
        'min_stock_level',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_stock'     => 'decimal:3',
            'cost_price'        => 'decimal:3',
            'weighted_avg_cost' => 'decimal:3',
            'selling_price'     => 'decimal:3',
            'min_stock_level'   => 'decimal:3',
            'is_active'         => 'boolean',
        ];
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function stockDeposits()
    {
        return $this->hasMany(StockDeposit::class);
    }

    public function returnItems()
    {
        return $this->hasMany(ReturnItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock_level')
                     ->where('is_active', true);
    }

    public function isLowStock(): bool
    {
        return bccomp($this->current_stock, $this->min_stock_level, 3) <= 0;
    }
}
