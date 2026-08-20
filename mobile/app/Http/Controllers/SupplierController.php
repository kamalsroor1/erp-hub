<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', 'all');

        $res = ApiService::getSuppliers($search, $status);

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $res['data'] ?? [],
            'summary'   => $res['summary'] ?? [
                'total_suppliers' => 0,
                'total_payable'   => '0.000',
            ],
            'filters'   => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:50',
            'address'         => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'notes'           => 'nullable|string',
        ]);

        $res = ApiService::createSupplier($validated);

        if ($res['success'] ?? false) {
            return back()->with('success', 'تم إضافة المورد بنجاح');
        }

        return back()->with('error', $res['message'] ?? 'فشل إضافة المورد');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:255',
            'notes'        => 'nullable|string',
        ]);

        $res = ApiService::updateSupplier((int)$id, $validated);

        if ($request->header('X-Inertia')) {
            if ($res['success'] ?? false) {
                return back()->with('success', 'تم تعديل بيانات المورد بنجاح');
            }
            return back()->with('error', $res['message'] ?? 'فشل تعديل المورد');
        }

        if ($request->wantsJson()) {
            return response()->json($res);
        }

        if ($res['success'] ?? false) {
            return back()->with('success', 'تم تعديل بيانات المورد بنجاح');
        }

        return back()->with('error', $res['message'] ?? 'فشل تعديل المورد');
    }

    public function destroy(Request $request, $id)
    {
        $res = ApiService::deleteSupplier((int)$id);

        if ($request->header('X-Inertia')) {
            if ($res['success'] ?? false) {
                return back()->with('success', $res['message'] ?? 'تم حذف/تعطيل المورد بنجاح');
            }
            return back()->with('error', $res['message'] ?? 'فشل حذف المورد');
        }

        if ($request->wantsJson()) {
            return response()->json($res);
        }

        if ($res['success'] ?? false) {
            return back()->with('success', $res['message'] ?? 'تم حذف/تعطيل المورد بنجاح');
        }

        return back()->with('error', $res['message'] ?? 'فشل حذف المورد');
    }

    public function statement(Request $request, $id)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date', now()->toDateString());

        $res = ApiService::getSupplierStatement((int)$id, $fromDate, $toDate);

        return Inertia::render('Suppliers/Statement', [
            'supplier' => $res['supplier'] ?? [],
            'summary'  => $res['summary'] ?? [],
            'ledger'   => $res['ledger'] ?? [],
            'filter'   => [
                'from_date' => $fromDate,
                'to_date'   => $toDate,
            ],
        ]);
    }
}
