<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (ApiService::isAuthenticated()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login', [
            'defaultApiUrl' => ApiService::getBaseUrl(),
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
            'apiUrl'   => 'nullable|string',
        ]);

        if (!empty($validated['apiUrl'])) {
            ApiService::setBaseUrl($validated['apiUrl']);
        }

        $result = ApiService::login($validated['login'], $validated['password']);

        if ($result['success'] ?? false) {
            return redirect()->route('dashboard')->with('success', 'تم تسجيل الدخول بنجاح');
        }

        return back()->with([
            'error' => $result['message'] ?? 'بيانات الدخول غير صحيحة',
        ]);
    }

    public function logout()
    {
        ApiService::logout();
        return redirect()->route('login')->with('success', 'تم تسجيل الخروج بنجاح');
    }
}
