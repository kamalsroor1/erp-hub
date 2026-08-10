<div class="min-h-[85vh] flex items-center justify-center p-4 sm:p-6" x-data="{ showPass: false }">
    <div class="w-full max-w-md bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 relative overflow-hidden">
        <!-- Ambient Top Glow -->
        <div class="absolute -top-16 -right-16 w-36 h-36 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-36 h-36 bg-amber-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header / Logo -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white dark:bg-slate-800/80 p-2 shadow-lg shadow-amber-500/20 border border-slate-200 dark:border-slate-700">
                <img src="{{ asset('logo.png') }}" alt="سرور POS" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white font-tajawal tracking-tight">
                سرور لإدارة الفواتير والمخزون
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-sans">
                سجل الدخول برقم الهاتف وكلمة المرور للمتابعة
            </p>
        </div>

        <!-- Login Form -->
        <form wire:submit.prevent="login" class="space-y-4 font-sans">
            <!-- Phone Number Input -->
            <div>
                <label for="phone" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 font-tajawal">
                    رقم الهاتف / الموبايل <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input
                        wire:model.defer="phone"
                        type="text"
                        id="phone"
                        required
                        autofocus
                        dir="ltr"
                        placeholder="01012316954"
                        class="w-full pr-10 pl-4 py-3 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-2xl text-slate-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all placeholder:text-slate-400"
                    >
                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-sm pointer-events-none">
                        📱
                    </span>
                </div>
                @error('phone')
                    <span class="text-xs text-rose-500 mt-1.5 block font-bold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password Input with Alpine Toggle -->
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 font-tajawal">
                    كلمة المرور <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input
                        wire:model.defer="password"
                        :type="showPass ? 'text' : 'password'"
                        id="password"
                        required
                        dir="ltr"
                        placeholder="••••••••"
                        class="w-full pr-10 pl-11 py-3 bg-slate-50 dark:bg-slate-950/80 border border-slate-300 dark:border-slate-700/80 rounded-2xl text-slate-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition-all placeholder:text-slate-400"
                    >
                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-sm pointer-events-none">
                        🔒
                    </span>
                    <button
                        type="button"
                        @click="showPass = !showPass"
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 hover:text-amber-500 transition-colors cursor-pointer"
                        title="إظهار / إخفاء كلمة المرور"
                    >
                        <span x-show="!showPass" class="text-sm">👁️</span>
                        <span x-show="showPass" class="text-sm" style="display: none;">🙈</span>
                    </button>
                </div>
                @error('password')
                    <span class="text-xs text-rose-500 mt-1.5 block font-bold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input
                        wire:model.defer="remember"
                        type="checkbox"
                        class="w-4 h-4 rounded bg-slate-50 dark:bg-slate-950 border-slate-300 dark:border-slate-700 text-amber-500 focus:ring-amber-500 cursor-pointer"
                    >
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold font-tajawal">تذكرني على هذا الجهاز</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full py-3.5 px-6 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-black rounded-2xl shadow-lg shadow-amber-600/30 hover:shadow-amber-500/40 transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer text-sm"
                >
                    <span wire:loading.remove wire:target="login">🚀 دخول إلى لوحة التحكم</span>
                    <span wire:loading wire:target="login" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>جاري التحقق والدخول...</span>
                    </span>
                </button>
            </div>
        </form>

        <!-- Quick Credentials Hint Box -->
        <div class="p-4 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 rounded-2xl space-y-2 text-xs">
            <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 font-bold font-tajawal">
                <span>🔑 الحسابات المصرح لها (سوبر أدمن):</span>
                <span class="text-[10px] text-amber-600 dark:text-amber-400">انقر للتعبئة السريعة</span>
            </div>
            <div class="grid grid-cols-1 gap-2 pt-1">
                <button
                    type="button"
                    wire:click="$set('phone', '01012316954'); $set('password', 'password');"
                    class="w-full flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 text-right transition-colors cursor-pointer"
                >
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">👑 كمال سرور (سوبر أدمن 1)</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono" dir="ltr">01012316954</p>
                    </div>
                    <span class="text-[10px] px-2 py-1 bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30 rounded-lg">
                        password
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="$set('phone', '01558088841'); $set('password', '123456789');"
                    class="w-full flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700/60 text-right transition-colors cursor-pointer"
                >
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">👑 المدير العام 2 (سوبر أدمن 2)</p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono" dir="ltr">01558088841</p>
                    </div>
                    <span class="text-[10px] px-2 py-1 bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30 rounded-lg">
                        123456789
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
