<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\User;

class StoreController extends Controller
{
    /**
     * Get list of stores/branches accessible by the authenticated user
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // Check if user is Super Admin or has global store access
        $isGlobalAdmin = $user->id === 1 
            || $user->hasRole('super-admin') 
            || $user->hasRole('admin') 
            || (method_exists($user, 'can') && $user->can('view all stores'));

        if ($isGlobalAdmin) {
            $stores = Store::where('is_active', true)
                ->orderByDesc('is_main')
                ->orderBy('name')
                ->get();
        } else {
            $stores = $user->stores()
                ->where('is_active', true)
                ->orderByDesc('is_main')
                ->orderBy('name')
                ->get();

            // Fallback if no specific store assigned
            if ($stores->isEmpty()) {
                if ($user->default_store_id) {
                    $stores = Store::where('id', $user->default_store_id)->where('is_active', true)->get();
                }
                if ($stores->isEmpty()) {
                    $stores = collect([Store::getMainStore()]);
                }
            }
        }

        // Active Store
        $activeStoreId = $request->header('X-Store-Id') ?: session('current_store_id') ?: $user->default_store_id ?: ($stores->first()?->id);
        $activeStore = $stores->firstWhere('id', (int)$activeStoreId) ?: $stores->first();

        return response()->json([
            'success'      => true,
            'active_store' => $activeStore,
            'stores'       => $stores->values(),
        ]);
    }

    /**
     * Switch Active Store/Branch
     */
    public function switchStore(Request $request)
    {
        $validated = $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
        ]);

        /** @var User $user */
        $user = $request->user();
        $targetStoreId = (int)$validated['store_id'];

        $isGlobalAdmin = $user->id === 1 
            || $user->hasRole('super-admin') 
            || $user->hasRole('admin') 
            || (method_exists($user, 'can') && $user->can('view all stores'));

        $isAssigned = $user->stores()->where('stores.id', $targetStoreId)->exists() 
            || $user->default_store_id === $targetStoreId;

        if (!$isGlobalAdmin && !$isAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'عفواً، ليس لديك صلاحية للوصول إلى هذا الفرع.',
            ], 403);
        }

        $store = Store::findOrFail($targetStoreId);

        session(['current_store_id' => $store->id]);
        $user->update(['default_store_id' => $store->id]);

        return response()->json([
            'success'      => true,
            'message'      => 'تم التبديل إلى فرع: ' . $store->name,
            'active_store' => $store,
        ]);
    }
}
