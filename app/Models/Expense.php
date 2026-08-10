<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'expense_number',
        'category',
        'title',
        'amount',
        'expense_date',
        'payment_method',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:3',
        'expense_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
