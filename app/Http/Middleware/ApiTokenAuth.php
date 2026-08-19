<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->header('X-API-TOKEN') ?: $request->query('api_token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح - رمز التحقق غير موجود (Missing API Token)',
            ], 401);
        }

        $user = User::where('api_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'جلسة غير صالحة أو تم تسجيل الخروج (Invalid or Expired Token)',
            ], 401);
        }

        // Set authenticated user for this request
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        // Set store context from header or user's active store
        $storeHeader = $request->header('X-Store-Id');
        if ($storeHeader && is_numeric($storeHeader)) {
            session(['current_store_id' => (int)$storeHeader]);
        }

        return $next($request);
    }
}
