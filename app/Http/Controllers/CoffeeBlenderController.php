<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CoffeeBlenderController extends Controller
{
    public function index(): Response
    {
        $items = Item::where('is_active', true)
            ->whereNull('deleted_at')
            ->select('id', 'name', 'code', 'category', 'unit', 'cost_price', 'selling_price', 'current_stock')
            ->orderBy('name')
            ->get();

        $customers = Customer::where('is_active', true)
            ->whereNull('deleted_at')
            ->select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();

        return Inertia::render('CoffeeBlender/Index', [
            'items' => $items,
            'customers' => $customers,
        ]);
    }

    public function createInvoice(Request $request, InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'blend_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'components' => 'required|array|min:1',
            'components.*.item_id' => 'required|exists:items,id',
            'components.*.grams' => 'required|numeric|min:1',
            'components.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $itemsForInvoice = [];

        foreach ($validated['components'] as $comp) {
            $kg = bcdiv((string)$comp['grams'], '1000', 4);
            if (bccomp($kg, '0.000', 4) > 0) {
                $itemsForInvoice[] = [
                    'item_id' => (int)$comp['item_id'],
                    'quantity' => $kg,
                    'unit_price' => (string)$comp['unit_price'],
                    'discount_amount' => '0.000',
                ];
            }
        }

        $invoice = $invoiceService->createInvoice([
            'customer_id' => (int)$validated['customer_id'],
            'invoice_date' => now()->toDateString(),
            'items' => $itemsForInvoice,
            'payment_method' => 'cash',
            'notes' => "خلطة وتوليفة مخصوصة: {$validated['blend_name']}" . ($validated['notes'] ? " - {$validated['notes']}" : ''),
        ]);

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'تم إنشاء وتأكيد فاتورة التوليفة بنجاح');
    }
}