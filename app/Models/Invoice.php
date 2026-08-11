<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'user_id',
        'store_id',
        'invoice_date',
        'payment_type',
        'status',
        'payment_status',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'net_total',
        'paid_amount',
        'remaining_amount',
        'total_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date'     => 'date',
            'subtotal'         => 'decimal:3',
            'discount_value'   => 'decimal:3',
            'discount_amount'  => 'decimal:3',
            'net_total'        => 'decimal:3',
            'paid_amount'      => 'decimal:3',
            'remaining_amount' => 'decimal:3',
            'total_cost'       => 'decimal:3',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnDocument::class, 'invoice_id');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'source');
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePendingDebts(Builder $query): Builder
    {
        return $query->where('status', 'confirmed')
                     ->whereIn('payment_status', ['unpaid', 'partially_paid']);
    }

    public function getProfitAttribute(): string
    {
        return bcsub($this->net_total, $this->total_cost, 3);
    }

    public function getProfitMarginPercentageAttribute(): string
    {
        if (bccomp($this->net_total, '0.000', 3) <= 0) {
            return '0.0';
        }
        return bcmul(bcdiv($this->profit, $this->net_total, 4), '100', 1);
    }
}
