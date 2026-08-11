<?php

namespace App\Livewire\Auth;

use Livewire\Component;
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
    use RequiresAuth;

    // Personal Info
    public string $name = '';
    public string $email = '';
    public string $theme_preference = 'dark';

    // General & Printing Settings (System-Wide)
    public string $company_name = 'سرور كوفي';
    public string $company_subtitle = 'لتوريدات خامات مطاحن البن';
    public bool $show_print_company_name = true;
    public bool $show_print_subtitle = true;
    public bool $show_print_logo = true;

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
        $this->validate([
            'company_name'            => ['required', 'string', 'max:255'],
            'company_subtitle'        => ['nullable', 'string', 'max:255'],
            'show_print_company_name' => ['boolean'],
            'show_print_subtitle'     => ['boolean'],
            'show_print_logo'         => ['boolean'],
        ], [
            'company_name.required' => 'يرجى إدخال اسم المؤسسة أو النشاط.',
        ]);

        Setting::set('company_name', $this->company_name);
        Setting::set('company_subtitle', $this->company_subtitle ?? '');
        Setting::set('show_print_company_name', $this->show_print_company_name ? '1' : '0');
        Setting::set('show_print_subtitle', $this->show_print_subtitle ? '1' : '0');
        Setting::set('show_print_logo', $this->show_print_logo ? '1' : '0');

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'تم حفظ إعدادات الطباعة العامة!',
            'text' => 'تم تطبيق إعدادات الطباعة على كافة فواتير النظام بنجاح.'
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

    public function render()
    {
        return view('livewire.auth.profile', [
            'user' => Auth::user(),
        ]);
    }
}
