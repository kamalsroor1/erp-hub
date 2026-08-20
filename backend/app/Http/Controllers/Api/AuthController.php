<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Store;
use App\Models\Setting;

class AuthController extends Controller
{
    /**
     * Mobile User Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($request->input('login'));
        $password = $request->input('password');

        // Find user by phone or email
        $user = User::where(function ($query) use ($login) {
            $query->where('phone', $login)
                  ->orWhere('email', $login);
        })->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة (Invalid credentials)',
            ], 422);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الحساب معطل، يرجى مراجعة الإدارة',
            ], 403);
        }

        // Generate persistent/secure API token
        $token = Str::random(60) . '.' . time();
        $user->update([
            'api_token'     => $token,
            'last_login_at' => now(),
        ]);

        // Get user permissions & roles
        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        // User store context
        $currentStore = $user->getCurrentStore();
        $userStores = $user->hasRole('admin') 
            ? Store::where('is_active', true)->get(['id', 'name', 'code', 'type', 'is_main'])
            : $user->stores()->where('is_active', true)->get(['stores.id', 'name', 'code', 'type', 'is_main']);

        $companyName = Setting::get('company_name', 'سرور كوفي');
        $companySubtitle = Setting::get('company_subtitle', 'لتوريدات خامات مطاحن البن');

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token'   => $token,
            'user'    => [
                'id'                  => $user->id,
                'name'                => $user->name,
                'phone'               => $user->phone,
                'email'               => $user->email,
                'theme_preference'    => $user->theme_preference ?? 'dark',
                'show_print_subtitle' => (bool)$user->show_print_subtitle,
                'roles'               => $roles,
                'permissions'         => $permissions,
            ],
            'store'   => $currentStore ? [
                'id'      => $currentStore->id,
                'name'    => $currentStore->name,
                'code'    => $currentStore->code,
                'is_main' => (bool)$currentStore->is_main,
            ] : null,
            'stores'  => $userStores,
            'system'  => [
                'company_name'     => $companyName,
                'company_subtitle' => $companySubtitle,
                'server_time'      => now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Get Current Authenticated User Info
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $currentStore = $user->getCurrentStore();

        return response()->json([
            'success' => true,
            'user'    => [
                'id'                  => $user->id,
                'name'                => $user->name,
                'phone'               => $user->phone,
                'email'               => $user->email,
                'theme_preference'    => $user->theme_preference ?? 'dark',
                'show_print_subtitle' => (bool)$user->show_print_subtitle,
                'roles'               => $user->getRoleNames(),
                'permissions'         => $user->getAllPermissions()->pluck('name'),
            ],
            'store'   => $currentStore ? [
                'id'      => $currentStore->id,
                'name'    => $currentStore->name,
                'code'    => $currentStore->code,
                'is_main' => (bool)$currentStore->is_main,
            ] : null,
        ]);
    }

    /**
     * Logout & Revoke API Token
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->update(['api_token' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
}
