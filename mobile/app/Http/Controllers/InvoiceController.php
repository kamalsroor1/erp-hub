<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search     = $request->input('search', '');
        $status     = $request->input('status', 'all');
        $fromDate   = $request->input('from_date');
        $toDate     = $request->input('to_date');
        $customerId = $request->input('customer_id');

        $res = ApiService::getInvoices($search, $status, $fromDate, $toDate, $customerId);

        return Inertia::render('Invoices/Index', [
            'invoices'   => $res['data'] ?? [],
            'summary'    => $res['summary'] ?? [],
            'pagination' => $res['pagination'] ?? [],
            'filters'    => [
                'search'      => $search,
                'status'      => $status,
                'from_date'   => $fromDate,
                'to_date'     => $toDate,
                'customer_id' => $customerId,
            ],
            'activeStore' => ApiService::getStore(),
        ]);
    }

    public function show($id)
    {
        $res = ApiService::getInvoice((int)$id);

        if (!$res['success'] || empty($res['data'])) {
            return redirect()->route('invoices.index')->with('error', 'الفاتورة غير موجودة');
        }

        return Inertia::render('Invoices/Show', [
            'invoice'  => $res['data'],
            'whatsapp' => $res['whatsapp'] ?? [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|integer',
            'payment_type'   => 'required|in:cash,credit,bank_transfer',
            'paid_amount'    => 'nullable|numeric|min:0',
            'discount_type'  => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.item_id'    => 'required|integer',
            'items.*.quantity'   => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes'      => 'nullable|string',
        ]);

        $res = ApiService::createInvoice($validated);

        if ($res['success'] ?? false) {
            $invoiceId = $res['data']['id'] ?? null;
            return redirect()->route('invoices.show', $invoiceId)->with('success', $res['message'] ?? 'تم إنشاء الفاتورة بنجاح');
        }

        return back()->with('error', $res['message'] ?? 'فشل إنشاء الفاتورة');
    }

    public function cancel(Request $request, $id)
    {
        $reason = $request->input('reason', 'إلغاء من تطبيق الموبايل');
        $res = ApiService::cancelInvoice((int)$id, $reason);

        if ($request->header('X-Inertia')) {
            if ($res['success'] ?? false) {
                return back()->with('success', $res['message'] ?? 'تم إلغاء الفاتورة بنجاح وعكس المخزن');
            }
            return back()->with('error', $res['message'] ?? 'فشل إلغاء الفاتورة');
        }

        if ($request->wantsJson()) {
            return response()->json($res);
        }

        if ($res['success'] ?? false) {
            return back()->with('success', $res['message'] ?? 'تم إلغاء الفاتورة بنجاح وعكس المخزن');
        }

        return back()->with('error', $res['message'] ?? 'فشل إلغاء الفاتورة');
    }

    public function printThermal($id)
    {
        $res = ApiService::getInvoice((int)$id);
        if (!$res['success'] || empty($res['data'])) {
            return redirect()->route('invoices.index')->with('error', 'الفاتورة غير موجودة');
        }

        return view('layouts.print-thermal', ['invoice' => $res['data']]);
    }

    public function printA4($id)
    {
        $res = ApiService::getInvoice((int)$id);
        if (!$res['success'] || empty($res['data'])) {
            return redirect()->route('invoices.index')->with('error', 'الفاتورة غير موجودة');
        }

        return view('layouts.print-a4', ['invoice' => $res['data']]);
    }
}
