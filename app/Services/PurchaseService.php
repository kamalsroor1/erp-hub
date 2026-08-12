<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Item;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class PurchaseService
{
    public function __construct(
        protected StockService $stockService,
        protected SupplierBalanceService $supplierBalanceService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Create and confirm purchase invoice
     */
    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $subtotal = '0.000';
            $storeId = $data['store_id'] ?? Auth::user()?->getCurrentStore()?->id ?? \App\Models\Store::getMainStore()?->id;

            $purchase = Purchase::create([
                'purchase_number'      => $data['purchase_number'] ?? $this->generateUniqueNumber(),
                'supplier_id'          => $data['supplier_id'],
                'user_id'              => Auth::id() ?? 1,
                'store_id'             => $storeId,
                'purchase_date'        => $data['purchase_date'] ?? now()->toDateString(),
                'status'               => 'confirmed',
                'payment_status'       => 'unpaid',
                'subtotal'             => '0.000',
                'discount_amount'      => $data['discount_amount'] ?? '0.000',
                'net_total'            => '0.000',
                'paid_amount'          => '0.000',
                'remaining_amount'     => '0.000',
                'supplier_invoice_ref' => $data['supplier_invoice_ref'] ?? null,
                'notes'                => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $line) {
                $item = Item::where('id', $line['item_id'])->lockForUpdate()->firstOrFail();

                $quantity  = $line['quantity'];
                $costPrice = $line['cost_price'];
                $lineTotal = bcmul($quantity, $costPrice, 3);

                $purchase->items()->create([
                    'item_id'     => $item->id,
                    'quantity'    => $quantity,
                    'cost_price'  => $costPrice,
                    'total_price' => $lineTotal,
                ]);

                // Calculate weighted average cost
                $newWac = $this->calculateWeightedAverageCost(
                    currentStock: $item->current_stock,
                    currentWac: $item->weighted_avg_cost ?: $item->cost_price,
                    newQuantity: $quantity,
                    newCost: $costPrice
                );

                $item->cost_price = $costPrice;
                $item->weighted_avg_cost = $newWac;
                $item->save();

                // Add to inventory
                $this->stockService->addStock(
                    item: $item,
                    quantity: (string)$quantity,
                    unitCost: (string)$costPrice,
                    source: $purchase,
                    documentNumber: $purchase->purchase_number,
                    movementType: 'purchase_in',
                    notes: "توريد بضاعة بفاتورة شراء رقم {$purchase->purchase_number}",
                    storeId: $storeId
                );

                $subtotal = bcadd($subtotal, $lineTotal, 3);
            }

            $discountAmount = $data['discount_amount'] ?? '0.000';
            $netTotal = bcsub($subtotal, $discountAmount, 3);
            $paidAmount = $data['paid_amount'] ?? '0.000';
            $remainingAmount = bcsub($netTotal, $paidAmount, 3);

            $paymentStatus = 'unpaid';
            if (bccomp($paidAmount, '0.000', 3) > 0) {
                $paymentStatus = bccomp($paidAmount, $netTotal, 3) >= 0 ? 'paid' : 'partially_paid';
            }

            $purchase->update([
                'subtotal'         => $subtotal,
                'discount_amount'  => $discountAmount,
                'net_total'        => $netTotal,
                'paid_amount'      => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'payment_status'   => $paymentStatus,
            ]);

            // Record payment voucher if paid
            if (bccomp($paidAmount, '0.000', 3) > 0) {
                Payment::create([
                    'payment_number' => 'PAY-PUR-' . strtoupper(uniqid()),
                    'supplier_id'    => $purchase->supplier_id,
                    'purchase_id'    => $purchase->id,
                    'user_id'        => Auth::id() ?? 1,
                    'amount'         => $paidAmount,
                    'payment_date'   => $purchase->purchase_date,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'notes'          => "سداد دفعة توريد للفاتورة رقم {$purchase->purchase_number}",
                ]);
            }

            // Update supplier balance
            $this->supplierBalanceService->updateBalance($purchase->supplier_id);

            $this->auditLogService->log(
                action: 'purchase_confirmed',
                auditable: $purchase,
                oldValues: null,
                newValues: $purchase->toArray()
            );

            return $purchase;
        });
    }

    /**
     * Calculate Weighted Average Cost (WAC)
     */
    public function calculateWeightedAverageCost(
        string $currentStock,
        string $currentWac,
        string $newQuantity,
        string $newCost
    ): string {
        $existingStock = bccomp($currentStock, '0.000', 3) > 0 ? $currentStock : '0.000';
        $existingValuation = bcmul($existingStock, $currentWac ?: '0.000', 3);
        $newValuation = bcmul($newQuantity, $newCost, 3);
        $totalValuation = bcadd($existingValuation, $newValuation, 3);
        $totalQuantity = bcadd($existingStock, $newQuantity, 3);

        if (bccomp($totalQuantity, '0.000', 3) <= 0) {
            return $newCost;
        }

        return bcdiv($totalValuation, $totalQuantity, 3);
    }

    public function generateUniqueNumber(): string
    {
        $prefix = 'PUR-' . date('Ymd');
        
        $lastPurchase = Purchase::withTrashed()
            ->where('purchase_number', 'LIKE', $prefix . '-%')
            ->orderBy('purchase_number', 'desc')
            ->first();

        if ($lastPurchase) {
            $parts = explode('-', $lastPurchase->purchase_number);
            $lastSequence = (int) end($parts);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        do {
            $candidate = $prefix . '-' . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
            $exists = Purchase::withTrashed()->where('purchase_number', $candidate)->exists();
            if ($exists) {
                $nextSequence++;
            }
        } while ($exists);

        return $candidate;
    }
}
