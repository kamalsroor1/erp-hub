<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Customer;
use App\Services\InvoiceService;
use Exception;

class BlenderController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Get Coffee Blender Options & Raw Beans
     */
    public function options()
    {
        $availableCoffees = Item::active()
            ->where(function ($q) {
                $q->where('category', 'like', '%بن%')
                  ->orWhere('category', 'like', '%توليف%')
                  ->orWhere('category', 'like', '%خامات%');
            })
            ->get();

        // If none found with category filter, fetch all active items
        if ($availableCoffees->isEmpty()) {
            $availableCoffees = Item::active()->get();
        }

        $cardamom = Item::where('code', 'SPICE-001')->orWhere('name', 'like', '%حبهان%')->first();
        $mastic = Item::where('code', 'SPICE-002')->orWhere('name', 'like', '%مستكة%')->first();
        $customers = Customer::active()->orderBy('name')->get();

        return response()->json([
            'success'   => true,
            'coffees'   => $availableCoffees,
            'spices'    => [
                'cardamom' => $cardamom,
                'mastic'   => $mastic,
            ],
            'customers' => $customers,
            'roast_types' => ['فاتح', 'وسط', 'غامق', 'محروق'],
            'grind_levels' => [
                'تركي ناعم (كنكة)',
                'إسبريسو ناعم',
                'فلتر و V60 وسط',
                'فرينش بريس خشن',
                'حبوب بدون طحن (حصى)'
            ],
            'presets' => [
                ['label' => 'ثمن كيلو (125 جم)', 'grams' => 125],
                ['label' => 'ربع كيلو (250 جم)', 'grams' => 250],
                ['label' => 'نصف كيلو (500 جم)', 'grams' => 500],
                ['label' => 'كيلو كامل (1000 جم)', 'grams' => 1000],
            ]
        ]);
    }

    /**
     * Checkout Blend directly into a Sales Invoice
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_id'         => 'required|integer',
            'blend_name'          => 'required|string|max:255',
            'target_weight_grams' => 'required|numeric|min:1',
            'roast_type'          => 'nullable|string',
            'grind_level'         => 'nullable|string',
            'components'          => 'required|array|min:1',
            'components.*.item_id'=> 'required|integer',
            'components.*.grams'  => 'required|numeric|min:0.001',
            'cardamom_grams'      => 'nullable|numeric|min:0',
            'mastic_grams'        => 'nullable|numeric|min:0',
            'payment_type'        => 'nullable|in:cash,credit,bank_transfer',
            'paid_amount'         => 'nullable|numeric|min:0',
        ]);

        try {
            $itemsForInvoice = [];

            // Add coffee components converted to Kg
            foreach ($validated['components'] as $c) {
                $item = Item::findOrFail($c['item_id']);
                $kg = bcdiv((string)$c['grams'], '1000', 4);
                if (bccomp($kg, '0.000', 4) > 0) {
                    $itemsForInvoice[] = [
                        'item_id'         => $item->id,
                        'quantity'        => $kg,
                        'unit_price'      => $item->selling_price,
                        'discount_amount' => '0.000',
                    ];
                }
            }

            // Add Cardamom if any
            if (!empty($validated['cardamom_grams']) && bccomp((string)$validated['cardamom_grams'], '0.000', 3) > 0) {
                $cardItem = Item::where('code', 'SPICE-001')->orWhere('name', 'like', '%حبهان%')->first();
                if ($cardItem) {
                    $itemsForInvoice[] = [
                        'item_id'         => $cardItem->id,
                        'quantity'        => bcdiv((string)$validated['cardamom_grams'], '1000', 4),
                        'unit_price'      => $cardItem->selling_price,
                        'discount_amount' => '0.000',
                    ];
                }
            }

            // Add Mastic if any
            if (!empty($validated['mastic_grams']) && bccomp((string)$validated['mastic_grams'], '0.000', 3) > 0) {
                $masItem = Item::where('code', 'SPICE-002')->orWhere('name', 'like', '%مستكة%')->first();
                if ($masItem) {
                    $itemsForInvoice[] = [
                        'item_id'         => $masItem->id,
                        'quantity'        => bcdiv((string)$validated['mastic_grams'], '1000', 4),
                        'unit_price'      => $masItem->selling_price,
                        'discount_amount' => '0.000',
                    ];
                }
            }

            $storeId = $request->input('store_id') 
                ?? auth()->user()?->getCurrentStore()?->id 
                ?? \App\Models\Store::getMainStore()?->id;

            $roast = $validated['roast_type'] ?? 'وسط';
            $grind = $validated['grind_level'] ?? 'تركي ناعم';
            $weightG = $validated['target_weight_grams'];

            $invoice = $this->invoiceService->confirmInvoice([
                'customer_id'    => $validated['customer_id'],
                'store_id'       => $storeId,
                'invoice_date'   => now()->toDateString(),
                'payment_type'   => $validated['payment_type'] ?? 'cash',
                'discount_type'  => 'fixed',
                'discount_value' => '0.000',
                'paid_amount'    => $validated['paid_amount'] ?? '0.000',
                'notes'          => "☕ توليفة بن مخصوصة: {$validated['blend_name']} (وزن: {$weightG} جم | تحميص: {$roast} | طحن: {$grind})",
                'items'          => $itemsForInvoice,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء فاتورة التوليفة المخصوصة وخصم مكونات البن من المخزن بنجاح ✓',
                'data'    => $invoice,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إنشاء فاتورة التوليفة: ' . $e->getMessage(),
            ], 422);
        }
    }
}
