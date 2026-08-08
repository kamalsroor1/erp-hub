<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_id',
        'quantity',
        'cost_price',
        'unit_price',
        'discount_amount',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'decimal:3',
            'cost_price'      => 'decimal:3',
            'unit_price'      => 'decimal:3',
            'discount_amount' => 'decimal:3',
            'total_price'     => 'decimal:3',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getProfitAttribute(): string
    {
        $lineCost = bcmul($this->quantity, $this->cost_price, 3);
        return bcsub($this->total_price, $lineCost, 3);
    }
}
