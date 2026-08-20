<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ApiService;

class SettingController extends Controller
{
    /**
     * Display System Settings Page
     */
    public function index()
    {
        $data = ApiService::getSettings();

        return Inertia::render('Settings/Index', [
            'settings'    => $data['settings'] ?? [],
            'stores'      => $data['stores'] ?? [],
            'users_count' => $data['users_count'] ?? 0,
        ]);
    }

    /**
     * Save System Settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name'                  => 'required|string|max:255',
            'company_subtitle'              => 'nullable|string|max:255',
            'company_phone'                 => 'nullable|string|max:50',
            'company_address'               => 'nullable|string|max:255',
            'commercial_register'           => 'nullable|string|max:100',
            'show_print_company_name'       => 'nullable|boolean',
            'show_print_subtitle'           => 'nullable|boolean',
            'show_print_logo'               => 'nullable|boolean',
            'receipt_footer_message'        => 'nullable|string|max:500',
            'telegram_notifications_enabled'=> 'nullable|boolean',
            'telegram_bot_token'            => 'nullable|string|max:255',
            'telegram_chat_id'              => 'nullable|string|max:255',
        ]);

        $res = ApiService::updateSettings($validated);

        if (!empty($res['success'])) {
            return back()->with('success', $res['message'] ?? 'تم حفظ الإعدادات بنجاح ✓');
        }

        return back()->with('error', $res['message'] ?? 'فشل حفظ الإعدادات');
    }
}
