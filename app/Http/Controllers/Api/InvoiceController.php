<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Store;
use App\Services\InvoiceService;
use Exception;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * List Invoices for active store
     */
    public function index(Request $request)
    {
        $storeId    = (int)($request->header('X-Store-Id') ?: $request->input('store_id') ?: session('current_store_id') ?: 1);
        $search     = trim((string)$request->input('search', ''));
        $status     = $request->input('status', 'all');
        $customerId = $request->input('customer_id');
        $fromDate   = $request->input('from_date');
        $toDate     = $request->input('to_date');

        $query = Invoice::query()->with(['customer:id,name,phone,code', 'user:id,name', 'store:id,name']);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        if ($customerId) {
            $query->where('customer_id', (int)$customerId);
        }

        if ($status !== 'all' && !empty($status)) {
            $query->where('status', $status);
        }

        if ($fromDate) {
            $query->whereDate('invoice_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('invoice_date', '<=', $toDate);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->latest('id')->paginate($request->input('per_page', 20));

        // Summary calculations
        $totalSales = $query->where('status', '!=', 'cancelled')->sum('net_total');
        $totalPaid  = $query->where('status', '!=', 'cancelled')->sum('paid_amount');
        $totalDue   = bcsub((string)$totalSales, (string)$totalPaid, 3);

        return response()->json([
            'success' => true,
            'store_id' => $storeId,
            'summary' => [
                'total_invoices' => $invoices->total(),
                'total_sales'    => (string)$totalSales,
                'total_paid'     => (string)$totalPaid,
                'total_due'      => (string)$totalDue,
            ],
            'data' => $invoices->items(),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
                'per_page'     => $invoices->perPage(),
                'total'        => $invoices->total(),
            ],
        ]);
    }

    /**
     * Show single invoice details
     */
    public function show(Request $request, $id)
    {
        $invoice = Invoice::with([
            'customer',
            'user:id,name',
            'store:id,name,phone,address',
            'items.item:id,name,code,unit',
            'payments.user:id,name',
        ])->findOrFail($id);

        $waData = $this->buildWhatsAppMessage($invoice);

        return response()->json([
            'success'      => true,
            'data'         => $invoice,
            'whatsapp'     => $waData,
        ]);
    }

    /**
     * Create & confirm new Sales Invoice (POS / Mobile Order)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|integer|exists:customers,id',
            'store_id'       => 'nullable|integer|exists:stores,id',
            'payment_type'   => 'required|in:cash,credit,bank_transfer',
            'paid_amount'    => 'nullable|numeric|min:0',
            'discount_type'  => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.item_id'    => 'required|integer|exists:items,id',
            'items.*.quantity'   => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes'      => 'nullable|string',
        ]);

        $storeId = (int)($validated['store_id'] 
            ?? $request->header('X-Store-Id') 
            ?? $request->user()?->default_store_id 
            ?? 1);

        $validated['store_id'] = $storeId;

        try {
            $invoice = $this->invoiceService->confirmInvoice($validated);
            $invoice->load(['customer', 'store', 'items.item', 'user']);

            $waData = $this->buildWhatsAppMessage($invoice);

            return response()->json([
                'success'  => true,
                'message'  => 'تم حفظ واعتماد الفاتورة رقم: ' . $invoice->invoice_number,
                'data'     => $invoice,
                'whatsapp' => $waData,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل حفظ الفاتورة: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Helper to build formatted Arabic WhatsApp message and URL
     */
    protected function buildWhatsAppMessage(Invoice $invoice): array
    {
        $customer = $invoice->customer;
        $store    = $invoice->store;
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$customer?->phone);
        if (str_starts_with($cleanPhone, '01')) {
            $cleanPhone = '20' . substr($cleanPhone, 1);
        }

        $lines = [];
        $lines[] = "☕ *فاتورة مبيعات - سرور كوفي ERP*";
        $lines[] = "--------------------------------";
        $lines[] = "📄 *رقم الفاتورة:* " . $invoice->invoice_number;
        $lines[] = "📅 *التاريخ:* " . $invoice->invoice_date;
        $lines[] = "👤 *العميل:* " . ($customer?->name ?? 'عميل نقدي');
        $lines[] = "🏢 *الفرع:* " . ($store?->name ?? 'الفرع الرئيسي');
        $lines[] = "--------------------------------";
        $lines[] = "📦 *تفاصيل الأصناف:*";

        foreach ($invoice->items as $idx => $lineItem) {
            $name = $lineItem->item?->name ?? 'صنف';
            $qty  = Number_format((float)$lineItem->quantity, 2);
            $price = Number_format((float)$lineItem->unit_price, 2);
            $total = Number_format((float)$lineItem->total_price, 2);
            $lines[] = ($idx + 1) . ". {$name} × {$qty} = {$total} ج.م";
        }

        $lines[] = "--------------------------------";
        $lines[] = "💵 *الإجمالي الصافي:* " . Number_format((float)$invoice->net_total, 2) . " ج.م";
        $lines[] = "✅ *المدفوع:* " . Number_format((float)$invoice->paid_amount, 2) . " ج.م";
        $lines[] = "⚠️ *المتبقي:* " . Number_format((float)$invoice->remaining_amount, 2) . " ج.م";
        
        if ($customer) {
            $lines[] = "📊 *إجمالي رصيد الحساب الحالي:* " . Number_format((float)$customer->current_balance, 2) . " ج.م";
        }

        $lines[] = "--------------------------------";
        $lines[] = "شكراً لتعاملكم مع سرور كوفي لتوريدات خامات مطاحن البن ☕";

        $messageText = implode("\n", $lines);
        $encodedText = urlencode($messageText);
        $whatsappUrl = !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text={$encodedText}" : "https://wa.me/?text={$encodedText}";

        return [
            'phone'        => $customer?->phone,
            'clean_phone'  => $cleanPhone,
            'message_text' => $messageText,
            'whatsapp_url' => $whatsappUrl,
        ];
    }

    /**
     * Cancel Sales Invoice
     */
    public function cancel(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $reason = $request->input('reason', 'إلغاء من تطبيق الموبايل');

        try {
            $cancelled = $this->invoiceService->cancelInvoice($invoice, $reason);
            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الفاتورة رقم ' . $cancelled->invoice_number . ' بنجاح وعكس رصيد المخزن والحساب',
                'data'    => $cancelled,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إلغاء الفاتورة: ' . $e->getMessage(),
            ], 422);
        }
    }
}
