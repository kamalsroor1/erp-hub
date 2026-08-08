<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\StockDeposit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Exception;

class StockService
{
    /**
     * Check if item has enough available stock
     */
    public function hasAvailableStock(Item $item, string $requestedQuantity): bool
    {
        return bccomp($item->current_stock, $requestedQuantity, 3) >= 0;
    }

    /**
     * Deduct stock securely with row locking
     */
    public function deductStock(
        Item $item,
        string $quantity,
        Model $source,
        string $documentNumber,
        string $movementType = 'sales_out',
        ?string $notes = null
    ): StockMovement {
        // Lock row for update
        $lockedItem = Item::where('id', $item->id)->lockForUpdate()->firstOrFail();

        if (bccomp($lockedItem->current_stock, $quantity, 3) < 0) {
            throw new Exception("رصيد الصنف [{$lockedItem->name}] غير كافٍ. المتوفر حالياً: {$lockedItem->current_stock}");
        }

        $stockBefore = $lockedItem->current_stock;
        $stockAfter = bcsub($stockBefore, $quantity, 3);

        $lockedItem->current_stock = $stockAfter;
        $lockedItem->save();

        return StockMovement::create([
            'item_id'         => $lockedItem->id,
            'movement_type'   => $movementType,
            'quantity'        => $quantity,
            'stock_before'    => $stockBefore,
            'stock_after'     => $stockAfter,
            'unit_cost'       => $lockedItem->cost_price,
            'source_type'     => get_class($source),
            'source_id'       => $source->getKey(),
            'document_number' => $documentNumber,
            'user_id'         => Auth::id() ?? 1,
            'notes'           => $notes ?? "صرف مخزني للمستند {$documentNumber}",
        ]);
    }

    /**
     * Add stock securely
     */
    public function addStock(
        Item $item,
        string $quantity,
        string $unitCost,
        Model $source,
        string $documentNumber,
        string $movementType = 'purchase_in',
        ?string $notes = null
    ): StockMovement {
        $lockedItem = Item::where('id', $item->id)->lockForUpdate()->firstOrFail();

        $stockBefore = $lockedItem->current_stock;
        $stockAfter = bcadd($stockBefore, $quantity, 3);

        $lockedItem->current_stock = $stockAfter;
        $lockedItem->save();

        return StockMovement::create([
            'item_id'         => $lockedItem->id,
            'movement_type'   => $movementType,
            'quantity'        => $quantity,
            'stock_before'    => $stockBefore,
            'stock_after'     => $stockAfter,
            'unit_cost'       => $unitCost,
            'source_type'     => get_class($source),
            'source_id'       => $source->getKey(),
            'document_number' => $documentNumber,
            'user_id'         => Auth::id() ?? 1,
            'notes'           => $notes ?? "إضافة مخزنية للمستند {$documentNumber}",
        ]);
    }

    /**
     * Manual deposit / opening balance / adjustment
     */
    public function depositStock(
        Item $item,
        string $quantity,
        string $costPrice,
        string $depositType = 'manual_deposit',
        ?string $reason = null,
        ?string $depositDate = null
    ): StockDeposit {
        $lockedItem = Item::where('id', $item->id)->lockForUpdate()->firstOrFail();

        $stockBefore = $lockedItem->current_stock;
        $stockAfter = bcadd($stockBefore, $quantity, 3);

        $lockedItem->current_stock = $stockAfter;
        if (bccomp($costPrice, '0.000', 3) > 0) {
            $lockedItem->cost_price = $costPrice;
        }
        $lockedItem->save();

        $deposit = StockDeposit::create([
            'item_id'      => $lockedItem->id,
            'user_id'      => Auth::id() ?? 1,
            'deposit_type' => $depositType,
            'quantity'     => $quantity,
            'cost_price'   => $costPrice,
            'reason'       => $reason,
            'deposit_date' => $depositDate ?? now()->toDateString(),
        ]);

        StockMovement::create([
            'item_id'         => $lockedItem->id,
            'movement_type'   => 'stock_deposit_in',
            'quantity'        => $quantity,
            'stock_before'    => $stockBefore,
            'stock_after'     => $stockAfter,
            'unit_cost'       => $costPrice,
            'source_type'     => StockDeposit::class,
            'source_id'       => $deposit->id,
            'document_number' => "DEP-{$deposit->id}",
            'user_id'         => Auth::id() ?? 1,
            'notes'           => $reason ?? "إيداع مخزني يدوي",
        ]);

        return $deposit;
    }
}
