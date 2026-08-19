<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use Exception;

class SettingController extends Controller
{
    /**
     * Get system settings
     */
    public function index()
    {
        $settings = [
            'company_name'                  => Setting::get('company_name', 'سرور كوفي'),
            'company_subtitle'              => Setting::get('company_subtitle', 'لتوريدات خامات مطاحن البن'),
            'company_phone'                 => Setting::get('company_phone', '01000000000'),
            'company_address'               => Setting::get('company_address', 'المخزن الرئيسي'),
            'commercial_register'           => Setting::get('commercial_register', ''),
            'show_print_company_name'       => Setting::getBool('show_print_company_name', true),
            'show_print_subtitle'           => Setting::getBool('show_print_subtitle', true),
            'show_print_logo'               => Setting::getBool('show_print_logo', true),
            'receipt_footer_message'        => Setting::get('receipt_footer_message', 'شكراً لتعاملكم معنا ونتمنى لكم يوماً سعيداً ☕'),
            'telegram_notifications_enabled'=> Setting::getBool('telegram_notifications_enabled', true),
            'telegram_bot_token'            => Setting::get('telegram_bot_token', ''),
            'telegram_chat_id'              => Setting::get('telegram_chat_id', ''),
        ];

        $stores = Store::all();
        $usersCount = User::count();

        return response()->json([
            'success'     => true,
            'settings'    => $settings,
            'stores'      => $stores,
            'users_count' => $usersCount,
        ]);
    }

    /**
     * Update system settings
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

        try {
            foreach ($validated as $key => $val) {
                if (is_bool($val)) {
                    Setting::set($key, $val ? '1' : '0');
                } else {
                    Setting::set($key, (string)$val);
                }
            }

            Setting::clearCache();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ وتحديث إعدادات النظام بنجاح ✓',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل حفظ الإعدادات: ' . $e->getMessage(),
            ], 422);
        }
    }
}
