<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Store;
use Exception;

class ExpenseController extends Controller
{
    /**
     * List expenses with filters and summary totals
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $query = Expense::with(['user', 'store'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('expense_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($category && $category !== 'all', fn($q) => $q->where('category', $category))
            ->when($fromDate, fn($q) => $q->whereDate('expense_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('expense_date', '<=', $toDate));

        $totalAmount = (clone $query)->sum('amount') ?: 0;
        $totalCount = (clone $query)->count();

        $expenses = $query->latest('expense_date')->latest('id')->paginate(30);

        $quickCategories = [
            'شنط وأكياس',
            'أكواب ورقية وبلاستيكية',
            'لاصق وشرائط تغليف',
            'بوفيه وضيافة',
            'صيانة مطاحن ومعدات',
            'إيجار وكهرباء ومرافق',
            'نثريات ومصاريف تشغيل',
        ];

        return response()->json([
            'success'          => true,
            'expenses'         => $expenses->items(),
            'total_amount'     => (string)$totalAmount,
            'total_count'      => $totalCount,
            'quick_categories' => $quickCategories,
            'pagination'       => [
                'current_page' => $expenses->currentPage(),
                'last_page'    => $expenses->lastPage(),
                'total'        => $expenses->total(),
            ]
        ]);
    }

    /**
     * Store new expense
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'       => 'required|string|max:100',
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $storeId = $request->input('store_id') 
            ?? auth()->user()?->getCurrentStore()?->id 
            ?? Store::getMainStore()?->id;

        $prefix = 'EXP-' . date('Ymd');
        $count = Expense::whereDate('created_at', now()->toDateString())->count() + 1;
        $expenseNumber = $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        try {
            $expense = Expense::create([
                'expense_number' => $expenseNumber,
                'category'       => $validated['category'],
                'title'          => $validated['title'],
                'amount'         => $validated['amount'],
                'expense_date'   => $validated['expense_date'],
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'user_id'        => auth()->id() ?? 1,
                'store_id'       => $storeId,
                'notes'          => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم تسجيل المصروف رقم {$expense->expense_number} بنجاح ✓",
                'expense' => $expense,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تسجيل المصروف: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update existing expense
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category'       => 'required|string|max:100',
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric|min:0.01',
            'expense_date'   => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $expense = Expense::findOrFail($id);

        try {
            $expense->update([
                'category'       => $validated['category'],
                'title'          => $validated['title'],
                'amount'         => $validated['amount'],
                'expense_date'   => $validated['expense_date'],
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'notes'          => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم تعديل المصروف [{$expense->title}] بنجاح ✓",
                'expense' => $expense,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تعديل المصروف: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an expense
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المصروف بنجاح ✓',
        ]);
    }
}
