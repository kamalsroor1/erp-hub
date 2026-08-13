<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Livewire\Traits\RequiresAuth;
use App\Models\Setting;

#[Layout('components.layouts.app')]
#[Title('الملف الشخصي وإعدادات النظام والطباعة | سرور POS')]
class Profile extends Component
{
    use RequiresAuth, WithFileUploads;

    // Personal Info
    public string $name = '';
    public string $email = '';
    public string $theme_preference = 'dark';

    // General & Printing Settings (System-Wide - Admin Only)
    public string $company_name = 'سرور كوفي';
    public string $company_subtitle = 'لتوريدات خامات مطاحن البن';
    public bool $show_print_company_name = true;
    public bool $show_print_subtitle = true;
    public bool $show_print_logo = true;
    public $logo_file = null;

    // Telegram Bot Notifications
    public ?string $telegram_bot_token = '';
    public ?string $telegram_chat_id = '';
    public bool $telegram_notifications_enabled = true;
    public string $telegramStatusMessage = '';

    // Security
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->theme_preference = $user->theme_preference ?? 'dark';

        // Load General Settings
        $this->company_name = Setting::get('company_name', 'سرور كوفي');
        $this->company_subtitle = Setting::get('company_subtitle', 'لتوريدات خامات مطاحن البن');
        $this->show_print_company_name = Setting::getBool('show_print_company_name', true);
        $this->show_print_subtitle = Setting::getBool('show_print_subtitle', true);
        $this->show_print_logo = Setting::getBool('show_print_logo', true);

        // Load Telegram Settings
        $this->telegram_bot_token = (string)(Setting::get('telegram_bot_token') ?? config('services.telegram.bot_token') ?? '');
        $this->telegram_chat_id = (string)(Setting::get('telegram_chat_id') ?? config('services.telegram.chat_id') ?? '');
        $this->telegram_notifications_enabled = Setting::getBool('telegram_notifications_enabled', true);
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $this->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'theme_preference' => ['required', 'string', 'in:dark,light'],
        ], [
            'name.required'  => 'يرجى إدخال الاسم بالكامل.',
            'email.required' => 'يرجى إدخال البريد الإلكتروني.',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'   => 'هذا البريد الإلكتروني مسجل بالفعل لمستخدم آخر.',
        ]);

        $user->update([
            'name'             => $this->name,
            'email'            => $this->email,
            'theme_preference' => $this->theme_preference,
        ]);

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'تم حفظ البيانات!',
            'text' => 'تم تحديث البيانات الشخصية والمظهر بنجاح.'
        ]);
        $this->dispatch('theme-changed', $this->theme_preference);
    }

    public function updateGeneralSettings()
    {
        abort_if(!auth()->user()?->hasRole('admin'), 403, 'غير مصرح لك بتعديل إعدادات الهوية والطباعة');

        $this->validate([
            'company_name'            => ['required', 'string', 'max:255'],
            'company_subtitle'        => ['nullable', 'string', 'max:255'],
            'show_print_company_name' => ['boolean'],
            'show_print_subtitle'     => ['boolean'],
            'show_print_logo'         => ['boolean'],
            'logo_file'               => ['nullable', 'image', 'max:3072'], // 3MB max
        ], [
            'company_name.required' => 'يرجى إدخال اسم المؤسسة أو النشاط.',
            'logo_file.image'       => 'الملف المرفوع يجب أن يكون صورة صالحة (PNG/JPG/WEBP).',
            'logo_file.max'         => 'حجم اللوجو يجب ألا يتجاوز 3 ميجابايت.',
        ]);

        if ($this->logo_file) {
            @copy($this->logo_file->getRealPath(), public_path('logo.png'));
            $this->reset('logo_file');
        }

        Setting::set('company_name', $this->company_name);
        Setting::set('company_subtitle', $this->company_subtitle ?? '');
        Setting::set('show_print_company_name', $this->show_print_company_name ? '1' : '0');
        Setting::set('show_print_subtitle', $this->show_print_subtitle ? '1' : '0');
        Setting::set('show_print_logo', $this->show_print_logo ? '1' : '0');

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'تم حفظ إعدادات الهوية والطباعة!',
            'text' => 'تم تحديث الشعار والبيانات العامة بنجاح.'
        ]);
    }

    public function updatePassword()
    {
        $user = Auth::user();

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password'     => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required'         => 'يرجى إدخال كلمة المرور الحالية.',
            'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة.',
            'new_password.required'             => 'يرجى إدخال كلمة المرور الجديدة.',
            'new_password.min'                  => 'كلمة المرور الجديدة يجب ألا تقل عن 6 أحرف.',
            'new_password.confirmed'            => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'تم تغيير كلمة المرور!',
            'text' => 'تم تحديث كلمة المرور الخاصة بحسابك بنجاح.'
        ]);
    }

    public function updateTelegramSettings()
    {
        abort_if(!auth()->user()?->hasRole('admin'), 403, 'غير مصرح لك بتعديل إعدادات الإشعارات');

        Setting::set('telegram_bot_token', trim($this->telegram_bot_token));
        Setting::set('telegram_chat_id', trim($this->telegram_chat_id));
        Setting::set('telegram_notifications_enabled', $this->telegram_notifications_enabled ? '1' : '0');

        $this->dispatch('swal:toast', [
            'type'  => 'success',
            'title' => 'تم حفظ إعدادات تيليجرام!',
            'text'  => 'تم تحديث بيانات البوت ومعرف المحادثة بنجاح.'
        ]);
    }

    public function sendTestTelegramMessage(\App\Services\TelegramService $telegramService)
    {
        abort_if(!auth()->user()?->hasRole('admin'), 403, 'غير مصرح');

        Setting::set('telegram_bot_token', trim($this->telegram_bot_token));
        Setting::set('telegram_chat_id', trim($this->telegram_chat_id));

        $res = $telegramService->sendTestNotification(trim($this->telegram_chat_id));

        if ($res['success']) {
            $this->telegramStatusMessage = '✅ ' . $res['message'];
            $this->dispatch('swal:toast', [
                'type'  => 'success',
                'title' => 'تم إرسال الرسالة بنجاح!',
                'text'  => 'وصلت الرسالة التجريبية إلى حسابك في تيليجرام بنجاح.'
            ]);
        } else {
            $this->telegramStatusMessage = '❌ ' . $res['message'];
            $this->dispatch('swal:toast', [
                'type'  => 'error',
                'title' => 'فشل الإرسال!',
                'text'  => $res['message']
            ]);
        }
    }

    public function sendDailySummaryTest(\App\Services\TelegramService $telegramService)
    {
        abort_if(!auth()->user()?->hasRole('admin'), 403, 'غير مصرح');

        $res = $telegramService->sendDailySummaryNotification();
        $this->telegramStatusMessage = ($res['success'] ? '✅ ' : '❌ ') . $res['message'];

        $this->dispatch('swal:toast', [
            'type'  => $res['success'] ? 'success' : 'error',
            'title' => $res['success'] ? 'تم إرسال تقرير اليومية!' : 'فشل الإرسال',
            'text'  => $res['message']
        ]);
    }

    public function sendLowStockTest(\App\Services\TelegramService $telegramService)
    {
        abort_if(!auth()->user()?->hasRole('admin'), 403, 'غير مصرح');

        $res = $telegramService->sendLowStockNotification(previewSample: true);
        $this->telegramStatusMessage = ($res['success'] ? '✅ ' : '❌ ') . $res['message'];

        $this->dispatch('swal:toast', [
            'type'  => $res['success'] ? 'success' : 'error',
            'title' => $res['success'] ? 'تم إرسال إنذار النواقص!' : 'فشل الإرسال',
            'text'  => $res['message']
        ]);
    }

    public function sendOverdueShiftTest(\App\Services\TelegramService $telegramService)
    {
        abort_if(!auth()->user()?->hasRole('admin'), 403, 'غير مصرح');

        $res = $telegramService->sendOverdueShiftNotification(previewSample: true);
        $this->telegramStatusMessage = ($res['success'] ? '✅ ' : '❌ ') . $res['message'];

        $this->dispatch('swal:toast', [
            'type'  => $res['success'] ? 'success' : 'error',
            'title' => $res['success'] ? 'تم إرسال إنذار الشفتات!' : 'فشل الإرسال',
            'text'  => $res['message']
        ]);
    }

    public function render()
    {
        return view('livewire.auth.profile', [
            'user' => Auth::user(),
        ])->layout('components.layouts.app', ['title' => 'الملف الشخصي وإعدادات النظام']);
    }
}
