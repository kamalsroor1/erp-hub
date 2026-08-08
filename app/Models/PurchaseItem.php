<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'item_id',
        'quantity',
        'cost_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'    => 'decimal:3',
            'cost_price'  => 'decimal:3',
            'total_price' => 'decimal:3',
        ];
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
