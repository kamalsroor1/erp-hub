<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Supplier;
use App\Services\PaymentService;
use Exception;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * List payment vouchers
     */
    public function index(Request $request)
    {
        $type       = $request->input('type', 'all'); // customer, supplier, all
        $customerId = $request->input('customer_id');
        $supplierId = $request->input('supplier_id');
        $fromDate   = $request->input('from_date');
        $toDate     = $request->input('to_date');

        $query = Payment::query()->with(['customer:id,name,phone', 'supplier:id,name,phone', 'user:id,name']);

        if ($type === 'customer') {
            $query->whereNotNull('customer_id');
        } elseif ($type === 'supplier') {
            $query->whereNotNull('supplier_id');
        }

        if ($customerId) {
            $query->where('customer_id', (int)$customerId);
        }
        if ($supplierId) {
            $query->where('supplier_id', (int)$supplierId);
        }

        if ($fromDate) {
            $query->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('payment_date', '<=', $toDate);
        }

        $payments = $query->latest('id')->paginate($request->input('per_page', 20));

        $totalCollections = Payment::whereNotNull('customer_id')->sum('amount');
        $totalDisbursements = Payment::whereNotNull('supplier_id')->sum('amount');

        return response()->json([
            'success' => true,
            'summary' => [
                'total_collections'   => (string)$totalCollections,
                'total_disbursements' => (string)$totalDisbursements,
            ],
            'data' => $payments->items(),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page'    => $payments->lastPage(),
                'per_page'     => $payments->perPage(),
                'total'        => $payments->total(),
            ],
        ]);
    }

    /**
     * Create Customer Receipt Voucher (سند قبض / تحصيل)
     */
    public function customerReceipt(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|integer|exists:customers,id',
            'amount'         => 'required|numeric|min:0.01',
            'invoice_id'     => 'nullable|integer|exists:invoices,id',
            'payment_date'   => 'nullable|date',
            'payment_method' => 'nullable|in:cash,bank_transfer,check,other',
            'notes'          => 'nullable|string',
        ]);

        try {
            $payment = $this->paymentService->recordCustomerPayment($validated);
            $customer = Customer::find($validated['customer_id']);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل سند القبض وتحصيل مبلغ ' . number_format((float)$validated['amount'], 2) . ' ج.م بنجاح',
                'data'    => $payment,
                'customer_current_balance' => (string)$customer?->current_balance,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تسجيل سند القبض: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Create Supplier Disbursement Voucher (سند صرف / سداد مورد)
     */
    public function supplierVoucher(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'    => 'required|integer|exists:suppliers,id',
            'amount'         => 'required|numeric|min:0.01',
            'purchase_id'    => 'nullable|integer|exists:purchases,id',
            'payment_date'   => 'nullable|date',
            'payment_method' => 'nullable|in:cash,bank_transfer,check,other',
            'notes'          => 'nullable|string',
        ]);

        try {
            $payment = $this->paymentService->recordSupplierPayment($validated);
            $supplier = Supplier::find($validated['supplier_id']);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل سند الصرف وسداد مبلغ ' . number_format((float)$validated['amount'], 2) . ' ج.م للمورد بنجاح',
                'data'    => $payment,
                'supplier_current_balance' => (string)$supplier?->current_balance,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تسجيل سند الصرف: ' . $e->getMessage(),
            ], 422);
        }
    }
}
