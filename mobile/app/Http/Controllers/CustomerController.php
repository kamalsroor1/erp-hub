<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', 'all');

        $res = ApiService::getCustomers($search, $status);

        if ($res['unauthorized'] ?? false) {
            return redirect()->route('login')->with('error', 'انتهت صلاحية الجلسة، يرجى إعادة تسجيل الدخول');
        }

        return Inertia::render('Customers/Index', [
            'customers' => $res['data'] ?? [],
            'summary'   => $res['summary'] ?? [
                'total_customers'  => 0,
                'total_receivable' => '0.000',
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
            'credit_limit'    => 'nullable|numeric',
            'opening_balance' => 'nullable|numeric',
            'notes'           => 'nullable|string',
        ]);

        $res = ApiService::createCustomer($validated);

        if ($request->wantsJson() || $request->ajax() || $request->header('X-Inertia') === null) {
            return response()->json($res);
        }

        if ($res['success'] ?? false) {
            return back()->with('success', 'تم إضافة العميل بنجاح');
        }

        return back()->with('error', $res['message'] ?? 'فشل إضافة العميل');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric',
            'notes'        => 'nullable|string',
        ]);

        $res = ApiService::updateCustomer((int)$id, $validated);

        if ($request->header('X-Inertia')) {
            if ($res['success'] ?? false) {
                return back()->with('success', 'تم تعديل بيانات العميل بنجاح');
            }
            return back()->with('error', $res['message'] ?? 'فشل تعديل العميل');
        }

        if ($request->wantsJson()) {
            return response()->json($res);
        }

        if ($res['success'] ?? false) {
            return back()->with('success', 'تم تعديل بيانات العميل بنجاح');
        }

        return back()->with('error', $res['message'] ?? 'فشل تعديل العميل');
    }

    public function destroy(Request $request, $id)
    {
        $res = ApiService::deleteCustomer((int)$id);

        if ($request->header('X-Inertia')) {
            if ($res['success'] ?? false) {
                return back()->with('success', $res['message'] ?? 'تم حذف/تعطيل العميل بنجاح');
            }
            return back()->with('error', $res['message'] ?? 'فشل حذف العميل');
        }

        if ($request->wantsJson()) {
            return response()->json($res);
        }

        if ($res['success'] ?? false) {
            return back()->with('success', $res['message'] ?? 'تم حذف/تعطيل العميل بنجاح');
        }

        return back()->with('error', $res['message'] ?? 'فشل حذف العميل');
    }

    public function statement(Request $request, $id)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date', now()->toDateString());

        $res = ApiService::getCustomerStatement((int)$id, $fromDate, $toDate);

        if ($res['unauthorized'] ?? false) {
            return redirect()->route('login')->with('error', 'انتهت صلاحية الجلسة، يرجى إعادة تسجيل الدخول');
        }

        return Inertia::render('Customers/Statement', [
            'customer' => $res['customer'] ?? [],
            'summary'  => $res['summary'] ?? [],
            'ledger'   => $res['ledger'] ?? [],
            'filter'   => [
                'from_date' => $fromDate,
                'to_date'   => $toDate,
            ],
        ]);
    }

    public function printStatement(Request $request, $id)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date', now()->toDateString());

        $res = ApiService::getCustomerStatement((int)$id, $fromDate, $toDate);

        return view('layouts.print-customer-statement-a4', [
            'customer' => $res['customer'] ?? [],
            'summary'  => $res['summary'] ?? [],
            'ledger'   => $res['ledger'] ?? [],
            'filter'   => [
                'from_date' => $fromDate,
                'to_date'   => $toDate,
            ],
        ]);
    }
}
