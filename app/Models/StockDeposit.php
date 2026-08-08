<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'deposit_type',
        'quantity',
        'cost_price',
        'reason',
        'deposit_date',
    ];

    protected function casts(): array
    {
        return [
            'deposit_date' => 'date',
            'quantity'     => 'decimal:3',
            'cost_price'   => 'decimal:3',
        ];
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
