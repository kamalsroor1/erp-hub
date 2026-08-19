<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Store;
use App\Models\CashShift;

class TreasuryController extends Controller
{
    /**
     * Treasury & Financial Summary for active store
     */
    public function summary(Request $request)
    {
        $storeId = (int)($request->header('X-Store-Id') ?: $request->input('store_id') ?: session('current_store_id') ?: 1);
        $today = now()->toDateString();

        // Today's Sales
        $todaySalesQuery = Invoice::query()->where('status', '!=', 'cancelled')->whereDate('invoice_date', $today);
        if ($storeId) {
            $todaySalesQuery->where('store_id', $storeId);
        }
        $todaySalesTotal = (string)$todaySalesQuery->sum('net_total');
        $todayCashCollected = (string)$todaySalesQuery->sum('paid_amount');

        // Today's Customer Receipts
        $todayReceipts = (string)Payment::whereNotNull('customer_id')->whereDate('payment_date', $today)->sum('amount');

        // Today's Supplier Payments
        $todaySupplierPaid = (string)Payment::whereNotNull('supplier_id')->whereDate('payment_date', $today)->sum('amount');

        // Today's Expenses
        $todayExpensesQuery = Expense::query()->whereDate('expense_date', $today);
        if ($storeId) {
            $todayExpensesQuery->where('store_id', $storeId);
        }
        $todayExpensesTotal = (string)$todayExpensesQuery->sum('amount');

        // Net Cash Flow Today = (Cash from sales + Customer receipts) - (Supplier paid + Expenses)
        $totalInflow = bcadd($todayCashCollected, $todayReceipts, 3);
        $totalOutflow = bcadd($todaySupplierPaid, $todayExpensesTotal, 3);
        $netCashToday = bcsub($totalInflow, $totalOutflow, 3);

        // All Time Receivables & Payables
        $totalReceivable = (string)Customer::where('current_balance', '>', 0)->sum('current_balance');
        $totalPayable    = (string)Supplier::where('current_balance', '>', 0)->sum('current_balance');

        // Active Shift
        $activeShift = CashShift::where('store_id', $storeId)->where('status', 'open')->latest('id')->first();

        return response()->json([
            'success'  => true,
            'store_id' => $storeId,
            'today'    => [
                'date'             => $today,
                'sales_total'      => $todaySalesTotal,
                'cash_collected'   => $todayCashCollected,
                'customer_receipts'=> $todayReceipts,
                'total_inflow'     => $totalInflow,
                'supplier_paid'    => $todaySupplierPaid,
                'expenses_total'   => $todayExpensesTotal,
                'total_outflow'    => $totalOutflow,
                'net_cash'         => $netCashToday,
            ],
            'balances' => [
                'total_receivable' => $totalReceivable,
                'total_payable'    => $totalPayable,
            ],
            'active_shift' => $activeShift ? [
                'id'              => $activeShift->id,
                'shift_number'    => $activeShift->shift_number,
                'opening_balance' => (string)$activeShift->opening_balance,
                'opened_at'       => $activeShift->opened_at,
            ] : null,
        ]);
    }
}
