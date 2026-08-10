<div class="max-w-4xl mx-auto space-y-8">
    <!-- Page Header -->
    <div class="bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-400 text-white flex items-center justify-center font-black text-2xl shadow-lg shadow-amber-500/20">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-2xl font-black text-white font-tajawal flex items-center gap-2">
                    <span>{{ $user->name }}</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-sans">
                        حساب نشط
                    </span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">
                    البريد الإلكتروني: <span class="text-slate-300 font-mono" dir="ltr">{{ $user->email }}</span> | انضم في: {{ $user->created_at?->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition-colors">
                ← العودة للوحة التحكم
            </a>
        </div>
    </div>

    <!-- Two-Column Settings Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- 1. Edit Personal Profile Info -->
        <div class="bg-slate-900/80 backdrop-blur-md border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-slate-800/80 mb-6">
                    <span class="text-2xl">👤</span>
                    <div>
                        <h2 class="text-lg font-bold text-white font-tajawal">البيانات الشخصية</h2>
                        <p class="text-xs text-slate-400">تعديل اسم الحساب والبريد الإلكتروني</p>
                    </div>
                </div>

                <form wire:submit.prevent="updateProfile" class="space-y-5">
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-300 mb-2">
                            الاسم الكامل <span class="text-rose-400">*</span>
                        </label>
                        <input
                            wire:model.defer="name"
                            type="text"
                            id="name"
                            required
                            class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('name') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-300 mb-2">
                            البريد الإلكتروني <span class="text-rose-400">*</span>
                        </label>
                        <input
                            wire:model.defer="email"
                            type="email"
                            id="email"
                            dir="ltr"
                            required
                            class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('email') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-2">
                            المظهر والسمة المفضلة (Theme) <span class="text-rose-400">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition-all {{ $theme_preference === 'dark' ? 'bg-slate-950 border-amber-500 text-amber-300 shadow-md shadow-amber-500/10' : 'bg-slate-950/40 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                                <input type="radio" wire:model="theme_preference" value="dark" class="hidden">
                                <span class="text-lg">🌙</span>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-white">الوضع الليلي (Dark)</div>
                                    <div class="text-[10px] text-slate-400">مريح للعين ومناسب للإضاءة الخافتة</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition-all {{ $theme_preference === 'light' ? 'bg-slate-800 border-amber-500 text-amber-300 shadow-md shadow-amber-500/10' : 'bg-slate-950/40 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                                <input type="radio" wire:model="theme_preference" value="light" class="hidden">
                                <span class="text-lg">☀️</span>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-white">الوضع النهاري (Light)</div>
                                    <div class="text-[10px] text-slate-400">إضاءة عالية وتباين ساطع للشاشات</div>
                                </div>
                            </label>
                        </div>
                        @error('theme_preference') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full py-3 px-6 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold rounded-xl shadow-lg shadow-amber-600/30 transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="updateProfile">💾 حفظ البيانات الشخصية</span>
                            <span wire:loading wire:target="updateProfile">جاري الحفظ...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Security & Password Change -->
        <div class="bg-slate-900/80 backdrop-blur-md border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col justify-between" x-data="{ showCurr: false, showNew: false }">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-slate-800/80 mb-6">
                    <span class="text-2xl">🔐</span>
                    <div>
                        <h2 class="text-lg font-bold text-white font-tajawal">الأمان وكلمة المرور</h2>
                        <p class="text-xs text-slate-400">تحديث كلمة المرور لحماية الحساب</p>
                    </div>
                </div>

                <form wire:submit.prevent="updatePassword" class="space-y-5">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-xs font-bold text-slate-300 mb-2">
                            كلمة المرور الحالية <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input
                                wire:model.defer="current_password"
                                :type="showCurr ? 'text' : 'password'"
                                id="current_password"
                                required
                                dir="ltr"
                                placeholder="••••••••"
                                class="w-full pr-4 pl-11 py-3 bg-slate-950 border border-slate-700 rounded-xl text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                            >
                            <button
                                type="button"
                                @click="showCurr = !showCurr"
                                class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 hover:text-amber-400"
                            >
                                <span x-show="!showCurr" class="text-sm">👁️</span>
                                <span x-show="showCurr" class="text-sm" style="display: none;">🙈</span>
                            </button>
                        </div>
                        @error('current_password') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="new_password" class="block text-xs font-bold text-slate-300 mb-2">
                            كلمة المرور الجديدة <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <input
                                wire:model.defer="new_password"
                                :type="showNew ? 'text' : 'password'"
                                id="new_password"
                                required
                                dir="ltr"
                                placeholder="••••••••"
                                class="w-full pr-4 pl-11 py-3 bg-slate-950 border border-slate-700 rounded-xl text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                            >
                            <button
                                type="button"
                                @click="showNew = !showNew"
                                class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 hover:text-amber-400"
                            >
                                <span x-show="!showNew" class="text-sm">👁️</span>
                                <span x-show="showNew" class="text-sm" style="display: none;">🙈</span>
                            </button>
                        </div>
                        @error('new_password') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-bold text-slate-300 mb-2">
                            تأكيد كلمة المرور الجديدة <span class="text-rose-400">*</span>
                        </label>
                        <input
                            wire:model.defer="new_password_confirmation"
                            type="password"
                            id="new_password_confirmation"
                            required
                            dir="ltr"
                            placeholder="••••••••"
                            class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="pt-4">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full py-3 px-6 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold rounded-xl shadow-lg transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
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
