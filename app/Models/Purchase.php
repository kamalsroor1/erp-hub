<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'user_id',
        'store_id',
        'purchase_date',
        'status',
        'payment_status',
        'subtotal',
        'discount_amount',
        'net_total',
        'paid_amount',
        'remaining_amount',
        'supplier_invoice_ref',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date'    => 'date',
            'subtotal'         => 'decimal:3',
            'discount_amount'  => 'decimal:3',
            'net_total'        => 'decimal:3',
            'paid_amount'      => 'decimal:3',
            'remaining_amount' => 'decimal:3',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'purchase_id');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}
