<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\ReturnDocument;
use Illuminate\Support\Facades\DB;

class SupplierBalanceService
{
    /**
     * Atomically recalculate and save the exact balance of a supplier.
     * Balance = (Total Confirmed Purchases) - (Total Supplier Payments) - (Total Purchase Returns)
     */
    public function updateBalance(int $supplierId): string
    {
        return DB::transaction(function () use ($supplierId) {
            $supplier = Supplier::where('id', $supplierId)->lockForUpdate()->firstOrFail();

            // 1. Total Confirmed Purchases
            $totalPurchases = Purchase::where('supplier_id', $supplier->id)
                ->where('status', 'confirmed')
                ->sum('net_total') ?: '0.000';

            // 2. Total Payments made to this supplier
            $totalPayments = Payment::where('supplier_id', $supplier->id)
                ->sum('amount') ?: '0.000';

            // 3. Total Purchase Returns
            $totalReturns = '0.000';
            if (class_exists(ReturnDocument::class)) {
                $totalReturns = ReturnDocument::where('supplier_id', $supplier->id)
                    ->where('return_type', 'purchase_return')
                    ->sum('total_amount') ?: '0.000';
            }

            // Running calculation: (Purchases - Payments) - Returns
            $balance = bcsub((string)$totalPurchases, (string)$totalPayments, 3);
            $finalBalance = bcsub($balance, (string)$totalReturns, 3);

            $supplier->current_balance = $finalBalance;
            $supplier->save();

            return $finalBalance;
        });
    }
}
