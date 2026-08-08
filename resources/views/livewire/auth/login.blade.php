<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-950 via-slate-900 to-amber-950/40 relative overflow-hidden" x-data="{ showPassword: false }">
    <!-- Ambient Background Blobs -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-amber-600/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-emerald-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full space-y-8 relative z-10">
        <!-- Brand Header -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-400 text-white shadow-xl shadow-amber-500/20 mb-4 ring-8 ring-amber-500/10 transform hover:scale-105 transition-transform duration-300">
                <span class="text-4xl">☕</span>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-white font-tajawal">
                سرور <span class="text-amber-400 font-extrabold">POS</span>
            </h1>
            <p class="mt-2 text-sm text-slate-400">
                نظام إدارة الفواتير والمخزون ومطحنة البن والشاي
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 rounded-3xl shadow-2xl p-8 sm:p-10 relative">
            <div class="mb-6 border-b border-slate-700/50 pb-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <span>🔐</span>
                    <span>تسجيل الدخول للنظام</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">أدخل بيانات الحساب المصرح له للمتابعة</p>
            </div>

            <!-- Error Banner -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-start gap-3">
                    <span class="text-lg">⚠️</span>
                    <div class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="login" class="space-y-6">
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        البريد الإلكتروني / اسم المستخدم <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                            </svg>
                        </div>
                        <input
                            wire:model.defer="email"
                            type="email"
                            id="email"
                            required
                            autofocus
                            dir="ltr"
                            placeholder="admin@sroor.com"
                            class="block w-full pr-11 pl-4 py-3.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm transition-all duration-200"
                        >
                    </div>
                </div>

                <!-- Password Input with Toggle -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            كلمة المرور <span class="text-rose-400">*</span>
                        </label>
                    </div>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            wire:model.defer="password"
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            required
                            dir="ltr"
                            placeholder="••••••••"
                            class="block w-full pr-11 pl-12 py-3.5 bg-slate-900/90 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm transition-all duration-200"
                        >
                        <!-- Show/Hide Button -->
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 hover:text-amber-400 transition-colors focus:outline-none"
                            title="إظهار / إخفاء كلمة المرور"
                        >
                            <span x-show="!showPassword" class="text-sm">👁️</span>
                            <span x-show="showPassword" class="text-sm" style="display: none;">🙈</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input
                            wire:model="remember"
                            type="checkbox"
                            class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-900"
                        >
                        <span class="text-xs text-slate-300 font-medium">تذكر بياناتي على هذا الجهاز</span>
                    </label>
                </div>

                <!-- Submit Button with Spinner -->
                <div>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full relative flex items-center justify-center gap-2 py-4 px-6 rounded-xl text-white font-bold bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 shadow-lg shadow-amber-600/30 transform active:scale-[0.99] transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed text-base font-tajawal cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="login" class="flex items-center gap-2">
                            <span>🚀</span>
                            <span>دخول إلى لوحة التحكم</span>
                        </span>
                        <span wire:loading wire:target="login" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>جاري التحقق والدخول...</span>
                        </span>
                    </button>
                </div>
            </form>

            <!-- Quick Demo Credentials Hint -->
            <div class="mt-8 pt-6 border-t border-slate-700/50 text-center">
                <p class="text-xs text-slate-400 mb-2 font-medium">بيانات الدخول الافتراضية للمدير:</p>
                <div class="inline-flex items-center gap-3 px-3 py-1.5 rounded-lg bg-slate-900/60 border border-slate-700/60 text-xs font-mono text-amber-300" dir="ltr">
                    <span>admin@sroor.com</span>
                    <span class="text-slate-600">|</span>
                    <span>password</span>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <p class="text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} سرور POS — جميع الحقوق محفوظة
        </p>
    </div>
</div>
