<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

final class SuperAdminAuthController extends Controller
{
    /**
     * Show the Super Admin dedicated login page
     */
    public function showLogin(): Response
    {
        return Inertia::render('SuperAdmin/Auth/Login', [
            'platform_name' => config('app.name', 'مخزني ERP'),
            'platform_version' => 'v2.5 Enterprise Hub',
        ]);
    }

    /**
     * Authenticate Super Admin into Central Platform
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where(function ($q) use ($credentials) {
            $q->where('phone', $credentials['phone'])
              ->orWhere('email', $credentials['phone']);
        })->where('is_active', true)->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => __('auth.failed'),
            ]);
        }

        // Strict Check: User MUST have admin role or be authorized for Super Admin
        if (!$user->hasRole('admin') && !$user->can('super_admin.access')) {
            throw ValidationException::withMessages([
                'phone' => 'هذا الحساب غير مصرح له بالدخول إلى لوحة التحكم المركزية للسوبر أدمن.',
            ]);
        }

        Auth::login($user, (bool)$request->input('remember', true));
        $request->session()->regenerate();

        return redirect()->intended(route('super.dashboard'));
    }

    /**
     * Super Admin Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super.login')->with('success', 'تم تسجيل الخروج من لوحة السوبر أدمن بنجاح');
    }
}
