<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class BlenderController extends Controller
{
    /**
     * Display the Coffee Blender Studio
     */
    public function index(Request $request)
    {
        $options = ApiService::getBlenderOptions();

        return Inertia::render('Blender/Index', [
            'coffees'      => $options['coffees'] ?? [],
            'spices'       => $options['spices'] ?? ['cardamom' => null, 'mastic' => null],
            'customers'    => $options['customers'] ?? [],
            'roast_types'  => $options['roast_types'] ?? ['فاتح', 'وسط', 'غامق', 'محروق'],
            'grind_levels' => $options['grind_levels'] ?? ['تركي ناعم (كنكة)', 'إسبريسو ناعم', 'فلتر و V60 وسط', 'فرينش بريس خشن', 'حبوب بدون طحن'],
            'presets'      => $options['presets'] ?? [],
        ]);
    }

    /**
     * Process direct checkout for Custom Blend
     */
    public function checkout(Request $request)
    {
        $result = ApiService::checkoutBlend($request->all());

        if (!empty($result['success'])) {
            $invoice = $result['data'] ?? [];
            $invoiceId = $invoice['id'] ?? null;

            if ($request->header('X-Inertia')) {
                if ($invoiceId) {
                    return redirect('/invoices/' . $invoiceId . '/print-thermal')
                        ->with('success', $result['message'] ?? 'تم إنشاء فاتورة التوليفة بنجاح ✓');
                }
                return redirect('/invoices')
                    ->with('success', $result['message'] ?? 'تم إنشاء فاتورة التوليفة بنجاح ✓');
            }

            return response()->json($result);
        }

        if ($request->header('X-Inertia')) {
            return back()->with('error', $result['message'] ?? 'فشل إنشاء فاتورة التوليفة');
        }

        return response()->json($result, 422);
    }
}
