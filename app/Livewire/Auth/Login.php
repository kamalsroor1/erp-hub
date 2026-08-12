<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;

#[Layout('components.layouts.app')]
#[Title('تسجيل الدخول برقم الهاتف | سرور POS')]
class Login extends Component
{
    public string $phone = '';
    public string $password = '';
    public bool $remember = true;

    protected function rules(): array
    {
        return [
            'phone'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'phone.required'    => 'يرجى إدخال رقم الهاتف أو اسم المستخدم.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
        ];
    }

    public function login()
    {
        $this->validate();

        $cleanPhone = trim($this->phone);
        $throttleKey = Str::transliterate(Str::lower($cleanPhone).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('phone', "تم تجاوز عدد المحاولات المسموح بها. يرجى المحاولة بعد {$seconds} ثانية.");
            $this->dispatch('swal:toast', [
                'type'  => 'error',
                'title' => 'محاولات دخول كثيرة',
                'text'  => "يرجى الانتظار {$seconds} ثانية قبل إعادة المحاولة."
            ]);
            return;
        }

        // Try login by phone first, then by email fallback
        $attemptPhone = Auth::attempt(['phone' => $cleanPhone, 'password' => $this->password, 'is_active' => true], $this->remember);
        $attemptEmail = false;

        if (!$attemptPhone) {
            $attemptEmail = Auth::attempt(['email' => $cleanPhone, 'password' => $this->password, 'is_active' => true], $this->remember);
        }

        if (!$attemptPhone && !$attemptEmail) {
            RateLimiter::hit($throttleKey, 60);

            $this->addError('phone', 'رقم الهاتف أو كلمة المرور غير صحيحة أو الحساب معطل.');
            $this->dispatch('swal:toast', [
                'type'  => 'error',
                'title' => 'فشل تسجيل الدخول',
                'text'  => 'يرجى التأكد من كتابة رقم الهاتف وكلمة المرور بشكل سليم.'
            ]);
            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        session()->flash('swal:toast', [
            'type'  => 'success',
            'title' => 'مرحباً بك!',
            'text'  => 'تم تسجيل الدخول بنجاح إلى منظومة سرور POS.'
        ]);

        return $this->redirectIntended(default: route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
