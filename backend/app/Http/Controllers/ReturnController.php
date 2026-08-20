<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ReturnDocument;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Store;
use App\Services\ReturnService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ReturnController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string)$request->input('search', ''));
        $type = $request->input('type', 'all');
        $dateFrom = $request->input('from');
        $dateTo = $request->input('to');

        $query = ReturnDocument::with(['customer', 'supplier', 'items.item', 'user', 'store']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($type !== 'all') {
            $query->where('return_type', $type);
        }

        if ($dateFrom) {
            $query->whereDate('return_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('return_date', '<=', $dateTo);
        }

        $returns = $query->latest('id')->paginate(15)->withQueryString();

        $totalReturns = (float)ReturnDocument::sum('net_total');
        $salesReturnsCount = ReturnDocument::where('return_type', 'sales_return')->count();
        $purchaseReturnsCount = ReturnDocument::where('return_type', 'purchase_return')->count();

        return Inertia::render('Returns/Index', [
            'returns' => $returns->through(fn($r) => [
                'id' => $r->id,
                'return_number' => $r->return_number,
                'return_type' => $r->return_type,
                'party_name' => $r->return_type === 'sales_return' ? ($r->customer?->name ?: 'عميل نقدي') : ($r->supplier?->name ?: 'مورد عام'),
                'return_date' => $r->return_date ? $r->return_date->toDateString() : $r->created_at->toDateString(),
                'net_total' => (float)$r->net_total,
                'refund_amount' => (float)$r->refund_amount,
                'items_count' => $r->items->count(),
                'reason' => $r->reason,
                'user_name' => $r->user?->name,
                'store_name' => $r->store?->name,
                'items' => $r->items->map(fn($it) => [
                    'id' => $it->id,
                    'item_name' => $it->item?->name,
                    'quantity' => (float)$it->quantity,
                    'unit_price' => (float)$it->unit_price,
                    'subtotal' => (float)$it->subtotal,
                ]),
            ]),
            'metrics' => [
                'total_returns' => $totalReturns,
                'sales_returns_count' => $salesReturnsCount,
                'purchase_returns_count' => $purchaseReturnsCount,
            ],
            'filters' => [
                'search' => $search,
                'type' => $type,
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
        ]);
    }

    public function create(): Response
    {
        $customers = Customer::where('is_active', true)->select('id', 'name', 'phone')->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->select('id', 'name', 'company_name')->orderBy('name')->get();
        $items = Item::where('is_active', true)->select('id', 'name', 'code', 'unit', 'cost_price', 'selling_price', 'current_stock')->orderBy('name')->get();

        return Inertia::render('Returns/Create', [
            'customers' => $customers,
            'suppliers' => $suppliers->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->company_name ? "{$s->name} ({$s->company_name})" : $s->name,
            ]),
            'items' => $items,
        ]);
    }

    public function store(Request $request, ReturnService $returnService)
    {
        $validated = $request->validate([
            'return_type' => 'required|in:sales_return,purchase_return',
            'customer_id' => 'nullable|required_if:return_type,sales_return|exists:customers,id',
            'supplier_id' => 'nullable|required_if:return_type,purchase_return|exists:suppliers,id',
            'return_date' => 'required|date',
            'refund_amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $storeId = $request->session()->get('active_store_id') ?: Store::first()?->id;

        $returnDoc = $returnService->createReturn([
            'return_type' => $validated['return_type'],
            'customer_id' => $validated['customer_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'store_id' => $storeId,
            'return_date' => $validated['return_date'],
            'refund_amount' => $validated['refund_amount'] ?? '0.000',
            'reason' => $validated['reason'] ?? null,
            'items' => $validated['items'],
        ]);

        return redirect()->route('returns.index')->with('success', "تم تسجيل مستند المرتجع رقم {$returnDoc->return_number} وعكس حركة المخزون بنجاح");
    }

    public function destroy(int $id)
    {
        $returnDoc = ReturnDocument::findOrFail($id);
        $returnDoc->delete();

        return redirect()->back()->with('success', 'تم نقل مستند المرتجع إلى سلة المحذوفات بنجاح');
    }
}