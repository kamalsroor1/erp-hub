<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

#[Layout('components.layouts.app')]
#[Title('تسجيل الدخول | سرور POS')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = true;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'يرجى إدخال البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
        ];
    }

    public function login()
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "تم تجاوز عدد المحاولات المسموح بها. يرجى المحاولة بعد {$seconds} ثانية.");
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'محاولات دخول كثيرة',
                'text' => "يرجى الانتظار {$seconds} ثانية قبل إعادة المحاولة."
            ]);
            return;
        }

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password, 'is_active' => true], $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            $this->addError('email', 'بيانات الدخول غير صحيحة أو الحساب غير مفعل.');
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'title' => 'فشل تسجيل الدخول',
                'text' => 'يرجى التحقق من صحة البريد الإلكتروني وكلمة المرور.'
            ]);
            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        session()->flash('swal:toast', [
            'type' => 'success',
            'title' => 'مرحباً بك!',
            'text' => 'تم تسجيل الدخول بنجاح إلى منظومة سرور.'
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
