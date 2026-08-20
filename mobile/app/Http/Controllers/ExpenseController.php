<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class ExpenseController extends Controller
{
    /**
     * Display the list of Expenses
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category', 'from_date', 'to_date', 'page']);
        $data = ApiService::getExpenses($filters);

        return Inertia::render('Expenses/Index', [
            'expenses'         => $data['expenses'] ?? [],
            'total_amount'     => $data['total_amount'] ?? '0.000',
            'total_count'      => $data['total_count'] ?? 0,
            'quick_categories' => $data['quick_categories'] ?? [],
            'pagination'       => $data['pagination'] ?? [],
            'filters'          => $filters,
        ]);
    }

    /**
     * Store new Expense
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

        $res = ApiService::createExpense($validated);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم تسجيل المصروف بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل تسجيل المصروف');
    }

    /**
     * Update existing Expense
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

        $res = ApiService::updateExpense((int)$id, $validated);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم تعديل المصروف بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل تعديل المصروف');
    }

    /**
     * Delete an Expense
     */
    public function destroy(Request $request, $id)
    {
        $res = ApiService::deleteExpense((int)$id);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم حذف المصروف بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل حذف المصروف');
    }
}
