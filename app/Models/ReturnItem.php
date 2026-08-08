<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'item_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'    => 'decimal:3',
            'unit_price'  => 'decimal:3',
            'total_price' => 'decimal:3',
        ];
    }

    public function returnDocument()
    {
        return $this->belongsTo(ReturnDocument::class, 'return_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
