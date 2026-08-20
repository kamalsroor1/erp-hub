<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class StoreController extends Controller
{
    /**
     * Switch Active Store
     */
    public function switchStore(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|integer',
        ]);

        $res = ApiService::switchStore((int)$validated['store_id']);

        if ($res['success'] ?? false) {
            return back()->with('success', $res['message'] ?? 'تم تغيير الفرع بنجاح');
        }

        return back()->with('error', $res['message'] ?? 'فشل التبديل إلى هذا الفرع');
    }
}
