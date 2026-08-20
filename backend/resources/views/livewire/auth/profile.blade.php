<div class="space-y-6">

    <!-- Top Banner for Admin: Quick Link to Advanced System Settings -->
    @if(auth()->user()?->hasRole('admin'))
    <div class="p-5 bg-gradient-to-r from-amber-500/15 via-sky-500/10 to-indigo-500/15 dark:from-amber-950/40 dark:via-sky-950/30 dark:to-indigo-950/40 border border-amber-500/30 rounded-3xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-4 text-center sm:text-right">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                ⚙️
            </div>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white font-tajawal">
                    لوحة إعدادات النظام المتقدمة والطباعة
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    تخصيص اللوجو والهوية، إشعارات وبوت التيليجرام، والنسخ الاحتياطي السحابي اليومي
                </p>
            </div>
        </div>

        <a
            href="{{ route('settings.index') }}"
            class="px-6 py-2.5 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs rounded-xl shadow-md shadow-amber-600/20 transition-all font-tajawal flex items-center gap-2 shrink-0 cursor-pointer"
        >
            <span>فتح إعدادات النظام</span>
            <span>←</span>
        </a>
    </div>
    @endif

    <!-- Profile Header Card -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 to-slate-800 dark:from-slate-950 dark:to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-slate-800">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-300 p-0.5 shadow-lg shadow-amber-500/30 flex items-center justify-center">
                    <div class="w-full h-full bg-slate-900 rounded-2xl flex items-center justify-center text-2xl sm:text-3xl font-black text-amber-400 font-tajawal">
                        {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                    </div>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black font-tajawal flex items-center gap-2">
                        <span>{{ $user->name }}</span>
                        @if($user->hasRole('admin'))
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">مدير عام</span>
                        @else
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30">{{ $user->roles->first()?->name ?? 'مستخدم' }}</span>
                        @endif
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1 font-mono">{{ $user->email }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 self-end sm:self-center">
                <div class="text-right">
                    <p class="text-[11px] text-slate-400">الفرع / المخزن الافتراضي</p>
                    <p class="text-xs font-bold text-amber-400">{{ $user->defaultStore?->name ?? 'المركز الرئيسي' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Info & Security Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- 1. Personal Information & Theme -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3.5 pb-4 border-b border-slate-200 dark:border-slate-800 mb-6">
                    <span class="text-2xl">👤</span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white font-tajawal">البيانات الشخصية والمظهر</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">تحديث اسم الحساب، البريد الإلكتروني، وتفضيل الوضع الليلي أو النهاري</p>
                    </div>
                </div>

                <form wire:submit.prevent="updateProfile" class="space-y-5">
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            الاسم بالكامل <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="name"
                            type="text"
                            id="name"
                            required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            البريد الإلكتروني <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="email"
                            type="email"
                            id="email"
                            required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none font-mono"
                        >
                        @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            تفضيل المظهر (Theme)
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex items-center justify-between p-3.5 rounded-2xl border cursor-pointer transition-all {{ $theme_preference === 'dark' ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                                <div class="flex items-center gap-2.5">
                                    <span>🌙</span>
                                    <span class="text-xs">الوضع الليلي (داكن)</span>
                                </div>
                                <input type="radio" wire:model.live="theme_preference" value="dark" class="text-amber-500 focus:ring-amber-500">
                            </label>

                            <label class="relative flex items-center justify-between p-3.5 rounded-2xl border cursor-pointer transition-all {{ $theme_preference === 'light' ? 'border-amber-500 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                                <div class="flex items-center gap-2.5">
                                    <span>☀️</span>
                                    <span class="text-xs">الوضع النهاري (فاتح)</span>
                                </div>
                                <input type="radio" wire:model.live="theme_preference" value="light" class="text-amber-500 focus:ring-amber-500">
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:hover:bg-white text-white dark:text-slate-900 font-bold text-xs rounded-xl shadow-md transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="updateProfile">💾 حفظ البيانات والمظهر</span>
                            <span wire:loading wire:target="updateProfile">جاري الحفظ...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Security & Password Change -->
        <div class="bg-white dark:bg-slate-900/90 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3.5 pb-4 border-b border-slate-200 dark:border-slate-800 mb-6">
                    <span class="text-2xl">🔒</span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white font-tajawal">أمان الحساب وكلمة المرور</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">تغيير كلمة المرور الخاصة بتسجيل الدخول للحساب</p>
                    </div>
                </div>

                <form wire:submit.prevent="updatePassword" class="space-y-5">
                    <div>
                        <label for="current_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            كلمة المرور الحالية <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="current_password"
                            type="password"
                            id="current_password"
                            required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('current_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="new_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            كلمة المرور الجديدة <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="new_password"
                            type="password"
                            id="new_password"
                            required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('new_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            تأكيد كلمة المرور الجديدة <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="new_password_confirmation"
                            type="password"
                            id="new_password_confirmation"
                            required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-md transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="updatePassword">🔒 تحديث كلمة المرور</span>
                            <span wire:loading wire:target="updatePassword">جاري التحديث...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>
