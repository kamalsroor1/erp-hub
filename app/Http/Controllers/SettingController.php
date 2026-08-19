<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class SettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Index', [
            'settings' => [
                'company_name' => Setting::get('company_name', 'سرور كوفي'),
                'company_subtitle' => Setting::get('company_subtitle', 'لتوريدات خامات مطاحن البن'),
                'company_phone' => Setting::get('company_phone', '01012316954'),
                'company_address' => Setting::get('company_address', 'القاهرة - مصر'),
                'invoice_footer_note' => Setting::get('invoice_footer_note', 'شكراً لتعاملكم معنا - البضاعة المباعة ترد وتستبدل خلال 14 يوماً'),
                'show_print_company_name' => Setting::getBool('show_print_company_name', true),
                'show_print_subtitle' => Setting::getBool('show_print_subtitle', true),
                'show_print_logo' => Setting::getBool('show_print_logo', true),
                'thermal_show_customer_balance' => Setting::getBool('thermal_show_customer_balance', true),
                'print_show_qr' => Setting::getBool('print_show_qr', true),
                'telegram_bot_token' => Setting::get('telegram_bot_token', ''),
                'telegram_chat_id' => Setting::get('telegram_chat_id', ''),
                'telegram_notifications_enabled' => Setting::getBool('telegram_notifications_enabled', false),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_subtitle' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:255',
            'invoice_footer_note' => 'nullable|string|max:500',
            'show_print_company_name' => 'boolean',
            'show_print_subtitle' => 'boolean',
            'show_print_logo' => 'boolean',
            'thermal_show_customer_balance' => 'boolean',
            'print_show_qr' => 'boolean',
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_chat_id' => 'nullable|string|max:255',
            'telegram_notifications_enabled' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            if (is_bool($value)) {
                Setting::set($key, $value ? '1' : '0');
            } else {
                Setting::set($key, (string)($value ?? ''));
            }
        }

        Setting::clearCache();

        return redirect()->back()->with('success', 'تم حفظ وتحديث إعدادات النظام بنجاح');
    }
}