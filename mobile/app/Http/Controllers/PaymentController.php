<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $type       = $request->input('type', 'all');
        $fromDate   = $request->input('from_date');
        $toDate     = $request->input('to_date');

        $res = ApiService::getPayments($type, $fromDate, $toDate);
        $customersRes = ApiService::getCustomers('', 'all', 1);
        $suppliersRes = ApiService::getSuppliers('', 'all', 1);

        return Inertia::render('Payments/Index', [
            'payments'   => $res['data'] ?? [],
            'summary'    => $res['summary'] ?? [],
            'customers'  => $customersRes['data'] ?? [],
            'suppliers'  => $suppliersRes['data'] ?? [],
            'filters'    => [
                'type'      => $type,
                'from_date' => $fromDate,
                'to_date'   => $toDate,
            ],
            'activeStore'=> ApiService::getStore(),
        ]);
    }

    public function customerReceipt(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|integer',
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'nullable|date',
            'payment_method' => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $res = ApiService::createCustomerReceipt($validated);

        if ($res['success'] ?? false) {
            return back()->with('success', $res['message'] ?? 'تم تحصيل المبلغ وتسجيل سند القبض بنجاح');
        }

        return back()->with('error', $res['message'] ?? 'فشل تسجيل سند القبض');
    }

    public function supplierVoucher(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'    => 'required|integer',
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'nullable|date',
            'payment_method' => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $res = ApiService::createSupplierVoucher($validated);

        if ($res['success'] ?? false) {
            return back()->with('success', $res['message'] ?? 'تم صرف المبلغ وسداد المورد بنجاح');
        }

        return back()->with('error', $res['message'] ?? 'فشل تسجيل سند الصرف');
    }
}
