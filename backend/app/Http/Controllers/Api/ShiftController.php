<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ShiftService;
use App\Models\CashShift;
use Exception;

class ShiftController extends Controller
{
    public function __construct(
        protected ShiftService $shiftService
    ) {}

    /**
     * Get Current Active Shift & Live Shift Metrics
     */
    public function current(Request $request)
    {
        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? \App\Models\Store::getMainStore()?->id;

        $shift = $this->shiftService->getActiveShift(storeId: $storeId);

        if (!$shift) {
            return response()->json([
                'success'      => true,
                'has_active'   => false,
                'active_shift' => null,
                'metrics'      => null,
            ]);
        }

        $metrics = $this->shiftService->calculateShiftTotals($shift);

        return response()->json([
            'success'      => true,
            'has_active'   => true,
            'active_shift' => [
                'id'                   => $shift->id,
                'shift_number'         => $shift->shift_number,
                'status'               => $shift->status,
                'opened_at'            => $shift->opened_at->format('Y-m-d H:i'),
                'opening_cash_balance' => (string)$shift->opening_cash_balance,
                'notes'                => $shift->notes,
                'store_name'           => $shift->store?->name ?? 'الفرع الحالي',
                'cashier_name'         => $shift->user?->name ?? 'الكاشير',
            ],
            'metrics'      => $metrics,
        ]);
    }

    /**
     * Open a new cashier shift
     */
    public function open(Request $request)
    {
        $validated = $request->validate([
            'opening_cash_balance' => 'required|numeric|min:0',
            'notes'                => 'nullable|string|max:500',
        ]);

        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? \App\Models\Store::getMainStore()?->id;

        try {
            $shift = $this->shiftService->openShift(
                openingCash: (string)$validated['opening_cash_balance'],
                notes: $validated['notes'] ?? null,
                storeId: $storeId
            );

            return response()->json([
                'success' => true,
                'message' => "تم فتح وردية العمل رقم {$shift->shift_number} بنجاح ✓",
                'shift'   => $shift,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Close the active cashier shift and generate Z-Report
     */
    public function close(Request $request)
    {
        $validated = $request->validate([
            'shift_id'            => 'required|integer',
            'actual_cash_balance' => 'required|numeric|min:0',
            'notes'               => 'nullable|string|max:500',
        ]);

        $shift = CashShift::findOrFail($validated['shift_id']);

        if ($shift->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'هذه الوردية مغلقة بالفعل أو غير نشطة',
            ], 422);
        }

        try {
            $closedShift = $this->shiftService->closeShift(
                shift: $shift,
                actualCash: (string)$validated['actual_cash_balance'],
                notes: $validated['notes'] ?? null
            );

            $diff = (float)$closedShift->cash_difference;
            $diffStatus = $diff == 0 ? 'مطابقة للدرج' : ($diff > 0 ? "زيادة بمقدار " . number_format($diff, 2) . " ج.م" : "عجز بمقدار " . number_format(abs($diff), 2) . " ج.م");

            return response()->json([
                'success'     => true,
                'message'     => "تم إغلاق وتقفيل الوردية رقم {$closedShift->shift_number} بنجاح ({$diffStatus}) ✓",
                'shift'       => $closedShift,
                'diff_status' => $diffStatus,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إغلاق الوردية: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get Z-Report data for thermal print
     */
    public function zReport($id)
    {
        $shift = CashShift::with(['user', 'store'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'report'  => [
                'id'                      => $shift->id,
                'shift_number'            => $shift->shift_number,
                'status'                  => $shift->status,
                'store_name'              => $shift->store?->name ?? 'سرور كوفي',
                'cashier_name'            => $shift->user?->name ?? 'الكاشير',
                'opened_at'               => $shift->opened_at?->format('Y-m-d H:i:s'),
                'closed_at'               => $shift->closed_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                'opening_cash_balance'    => (string)$shift->opening_cash_balance,
                'total_cash_sales'        => (string)$shift->total_cash_sales,
                'total_credit_sales'      => (string)$shift->total_credit_sales,
                'total_payments_collected'=> (string)$shift->total_payments_collected,
                'total_expenses'          => (string)($shift->total_expenses ?? '0.000'),
                'total_refunds'           => (string)$shift->total_refunds,
                'expected_cash_balance'   => (string)$shift->expected_cash_balance,
                'actual_cash_balance'     => (string)$shift->actual_cash_balance,
                'cash_difference'         => (string)$shift->cash_difference,
                'notes'                   => $shift->notes,
            ]
        ]);
    }
}
