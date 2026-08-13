<div class="max-w-5xl mx-auto space-y-8">
    <!-- Page Header -->
    <div class="bg-white dark:bg-slate-900/90 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-400 text-white flex items-center justify-center font-black text-xl sm:text-2xl shadow-lg shadow-amber-500/20 shrink-0">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-tajawal flex items-center gap-2 flex-wrap">
                    <span class="truncate">{{ $user->name }}</span>
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-sans">
                        حساب نشط
                    </span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                    البريد: <span class="text-slate-700 dark:text-slate-300 font-mono" dir="ltr">{{ $user->email }}</span> | انضم: {{ $user->created_at?->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-colors text-center">
                ← العودة للوحة التحكم
            </a>
        </div>
    </div>

    <!-- Settings Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        
        <!-- 1. General Printing & Store Branding Settings (System-Wide) -->
        <div class="lg:col-span-3 bg-white dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-7 shadow-sm">
            <div class="flex items-center gap-3.5 pb-4 border-b border-slate-200 dark:border-slate-800/80 mb-5">
                <div class="w-12 h-12 rounded-2xl bg-white dark:bg-slate-800 p-1 flex items-center justify-center shadow-md border border-slate-200 dark:border-slate-700 shrink-0">
                    <img src="{{ asset('logo.png') }}" alt="اللوجو" class="w-full h-full object-contain">
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white font-tajawal flex items-center gap-2">
                        <span>إعدادات ترويسة وهُوِيّة الطباعة (Store Branding & Logo)</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">عام لكافة المستخدمين</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">تخصيص اللوجو واسم المؤسسة والوصف الفرعي المطبوع على إيصالات وفواتير المبيعات (A4 & Thermal 80mm)</p>
                </div>
            </div>

            <form wire:submit.prevent="updateGeneralSettings" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Company Name -->
                    <div>
                        <label for="company_name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            اسم المحل / المؤسسة في الفاتورة <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="company_name"
                            type="text"
                            id="company_name"
                            required
                            placeholder="مثال: سرور كوفي"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('company_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Company Subtitle -->
                    <div>
                        <label for="company_subtitle" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            الوصف الفرعي (العنوان التوضيحي)
                        </label>
                        <input
                            wire:model.defer="company_subtitle"
                            type="text"
                            id="company_subtitle"
                            placeholder="مثال: لتوزيع خامات مطاحن البن"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('company_subtitle') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="space-y-3">
                    <!-- 1. Print Company Name Toggle Box -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="text-xl mt-0.5">🏢</span>
                            <div>
                                <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">إظهار اسم النشاط ({{ $company_name ?: 'سرور كوفي' }}) في الطباعة</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    عند التعطيل، سيتم إخفاء اسم النشاط من رأس فواتير A4 وإيصالات الكاشير.
                                </p>
                            </div>
                        </div>

                        <label class="relative inline-flex items-center cursor-pointer shrink-0 self-end sm:self-center">
                            <input type="checkbox" wire:model.live="show_print_company_name" class="sr-only peer">
                            <div class="w-12 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                            <span class="ms-3 text-xs font-bold text-slate-700 dark:text-slate-300 min-w-[75px]">
                                {{ $show_print_company_name ? 'مُفعّل (ظاهر)' : 'مُعطّل (مخفي)' }}
                            </span>
                        </label>
                    </div>

                    <!-- 2. Print Subtitle Toggle Box -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="text-xl mt-0.5">👁️</span>
                            <div>
                                <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">إظهار جملة الوصف الفرعي في الطباعة</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    عند التعطيل، سيتم إخفاء جملة "<span class="font-semibold text-slate-700 dark:text-slate-300">{{ $company_subtitle ?: 'لتوريدات خامات مطاحن البن' }}</span>" من فواتير A4 وإيصالات الكاشير.
                                </p>
                            </div>
                        </div>

                        <label class="relative inline-flex items-center cursor-pointer shrink-0 self-end sm:self-center">
                            <input type="checkbox" wire:model.live="show_print_subtitle" class="sr-only peer">
                            <div class="w-12 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                            <span class="ms-3 text-xs font-bold text-slate-700 dark:text-slate-300 min-w-[75px]">
                                {{ $show_print_subtitle ? 'مُفعّل (ظاهر)' : 'مُعطّل (مخفي)' }}
                            </span>
                        </label>
                    </div>

                    <!-- 3. Print Logo Toggle Box -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="text-xl mt-0.5">🖼️</span>
                            <div>
                                <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">إظهار اللوجو في الطباعة</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    عند التعطيل، سيتم إخفاء صورة اللوجو من رأس فواتير A4 وإيصالات الكاشير.
                                </p>
                            </div>
                        </div>

                        <label class="relative inline-flex items-center cursor-pointer shrink-0 self-end sm:self-center">
                            <input type="checkbox" wire:model.live="show_print_logo" class="sr-only peer">
                            <div class="w-12 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-amber-500"></div>
                            <span class="ms-3 text-xs font-bold text-slate-700 dark:text-slate-300 min-w-[75px]">
                                {{ $show_print_logo ? 'مُفعّل (ظاهر)' : 'مُعطّل (مخفي)' }}
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-600/30 transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="updateGeneralSettings">💾 حفظ إعدادات الطباعة العامة</span>
                        <span wire:loading wire:target="updateGeneralSettings">جاري الحفظ...</span>
                    </button>
                </div>
            </form>
        </div>

        @if(auth()->user()?->hasRole('admin'))
        <!-- 2. Telegram Bot Smart Notifications (Admin Only) -->
        <div class="lg:col-span-3 bg-white dark:bg-slate-900/80 backdrop-blur-md border border-sky-200 dark:border-sky-900/50 rounded-3xl p-5 sm:p-7 shadow-sm">
            <div class="flex items-center gap-3.5 pb-4 border-b border-slate-200 dark:border-slate-800/80 mb-5">
                <div class="w-12 h-12 rounded-2xl bg-sky-500/10 text-sky-500 flex items-center justify-center shadow-inner border border-sky-500/20 shrink-0 text-2xl">
                    ✈️
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white font-tajawal flex items-center gap-2">
                        <span>إشعارات وتقارير تيليجرام التلقائية (Telegram Bot Alerts)</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-md bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">خاص بالمدير</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">استقبال تقارير تقفيل اليومية (EOD) كل ليلة، إنذارات النواقص للمخازن، وتحذيرات الشفتات المفتوحة على تليجرامك الشخصي أو جروب الإدارة</p>
                </div>
            </div>

            <div class="space-y-5">
                <!-- Notifications Toggle -->
                <div class="p-4 rounded-2xl bg-sky-50/50 dark:bg-sky-950/20 border border-sky-100 dark:border-sky-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="text-xl mt-0.5">🔔</span>
                        <div>
                            <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">تفعيل خدمة إشعارات وتقارير تيليجرام</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                تشغيل الإرسال التلقائي للتقارير والإنذارات المجدولة في الخلفية.
                            </p>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer shrink-0 self-end sm:self-center">
                        <input type="checkbox" wire:model.live="telegram_notifications_enabled" class="sr-only peer">
                        <div class="w-12 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-sky-500"></div>
                        <span class="ms-3 text-xs font-bold text-slate-700 dark:text-slate-300 min-w-[75px]">
                            {{ $telegram_notifications_enabled ? 'مُفعّل' : 'مُعطّل' }}
                        </span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Bot Token -->
                    <div>
                        <label for="telegram_bot_token" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            رمز البوت (Bot Token) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="telegram_bot_token"
                            type="password"
                            id="telegram_bot_token"
                            dir="ltr"
                            placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-sky-500 focus:outline-none"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">يتم الحصول عليه مجاناً عبر التحدث مع <a href="https://t.me/BotFather" target="_blank" class="text-sky-500 font-bold underline">@BotFather</a></p>
                    </div>

                    <!-- Chat ID -->
                    <div>
                        <label for="telegram_chat_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                            معرف المحادثة أو الجروب (Chat ID / Group ID) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="telegram_chat_id"
                            type="text"
                            id="telegram_chat_id"
                            dir="ltr"
                            placeholder="مثال: 123456789 أو -100123456789 للجروبات"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-sky-500 focus:outline-none"
                        >
                        <p class="text-[11px] text-slate-400 mt-1">لمعرفة الـ ID الخاص بك راسل <a href="https://t.me/userinfobot" target="_blank" class="text-sky-500 font-bold underline">@userinfobot</a></p>
                    </div>
                </div>

                @if($telegramStatusMessage)
                <div class="p-3.5 rounded-xl text-xs font-bold {{ str_starts_with($telegramStatusMessage, '✅') ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' }}">
                    {{ $telegramStatusMessage }}
                </div>
                @endif

                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <button
                        wire:click="updateTelegramSettings"
                        type="button"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-6 py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-md transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="updateTelegramSettings">💾 حفظ الإعدادات</span>
                        <span wire:loading wire:target="updateTelegramSettings">جاري الحفظ...</span>
                    </button>

                    <button
                        wire:click="sendTestTelegramMessage"
                        type="button"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-sky-600 dark:text-sky-400 border border-sky-500/30 font-bold text-xs rounded-xl shadow-sm transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="sendTestTelegramMessage">📩 اختبار الاتصال</span>
                        <span wire:loading wire:target="sendTestTelegramMessage">جاري الإرسال...</span>
                    </button>

                    <button
                        wire:click="sendDailySummaryTest"
                        type="button"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-4 py-2.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 font-bold text-xs rounded-xl shadow-sm transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="sendDailySummaryTest">📊 تجربة تقرير اليومية (EOD)</span>
                        <span wire:loading wire:target="sendDailySummaryTest">جاري الإرسال...</span>
                    </button>

                    <button
                        wire:click="sendLowStockTest"
                        type="button"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-4 py-2.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30 font-bold text-xs rounded-xl shadow-sm transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="sendLowStockTest">⚠️ تجربة إنذار النواقص</span>
                        <span wire:loading wire:target="sendLowStockTest">جاري الإرسال...</span>
                    </button>

                    <button
                        wire:click="sendOverdueShiftTest"
                        type="button"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 font-bold text-xs rounded-xl shadow-sm transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="sendOverdueShiftTest">🚨 تجربة إنذار الشفتات</span>
                        <span wire:loading wire:target="sendOverdueShiftTest">جاري الإرسال...</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- 2. Personal Profile Info -->
        <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-7 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-slate-200 dark:border-slate-800/80 mb-5">
                    <span class="text-2xl">👤</span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white font-tajawal">البيانات الشخصية</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">تعديل اسم الحساب والمظهر</p>
                    </div>
                </div>

                <form wire:submit.prevent="updateProfile" class="space-y-4">
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            الاسم الكامل <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="name"
                            type="text"
                            id="name"
                            required
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            البريد الإلكتروني <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="email"
                            type="email"
                            id="email"
                            dir="ltr"
                            required
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                        @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            المظهر المفضل (Theme) <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2.5">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all {{ $theme_preference === 'dark' ? 'bg-slate-100 dark:bg-slate-950 border-amber-500 text-amber-600 dark:text-amber-300 shadow-md shadow-amber-500/10' : 'bg-slate-50 dark:bg-slate-950/40 border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700' }}">
                                <input type="radio" wire:model="theme_preference" value="dark" class="hidden">
                                <span class="text-base">🌙</span>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">ليلي (Dark)</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all {{ $theme_preference === 'light' ? 'bg-slate-100 dark:bg-slate-800 border-amber-500 text-amber-600 dark:text-amber-300 shadow-md shadow-amber-500/10' : 'bg-slate-50 dark:bg-slate-950/40 border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700' }}">
                                <input type="radio" wire:model="theme_preference" value="light" class="hidden">
                                <span class="text-base">☀️</span>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">نهاري (Light)</div>
                                </div>
                            </label>
                        </div>
                        @error('theme_preference') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full py-2.5 px-4 bg-slate-800 dark:bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow-md transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span wire:loading.remove wire:target="updateProfile">💾 حفظ البيانات الشخصية</span>
                            <span wire:loading wire:target="updateProfile">جاري الحفظ...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. Security & Password Change -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-7 shadow-sm flex flex-col justify-between" x-data="{ showCurr: false, showNew: false }">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-slate-200 dark:border-slate-800/80 mb-5">
                    <span class="text-2xl">🔐</span>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white font-tajawal">الأمان وكلمة المرور</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">تحديث كلمة المرور لحماية الحساب</p>
                    </div>
                </div>

                <form wire:submit.prevent="updatePassword" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Current Password -->
                        <div>
                            <label for="current_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                كلمة المرور الحالية <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    wire:model.defer="current_password"
                                    :type="showCurr ? 'text' : 'password'"
                                    id="current_password"
                                    required
                                    dir="ltr"
                                    placeholder="••••••••"
                                    class="w-full pr-4 pl-10 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                                >
                                <button
                                    type="button"
                                    @click="showCurr = !showCurr"
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 hover:text-amber-500 cursor-pointer"
                                >
                                    <span x-show="!showCurr" class="text-xs">👁️</span>
                                    <span x-show="showCurr" class="text-xs" style="display: none;">🙈</span>
                                </button>
                            </div>
                            @error('current_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="new_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                كلمة المرور الجديدة <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input
                                    wire:model.defer="new_password"
                                    :type="showNew ? 'text' : 'password'"
                                    id="new_password"
                                    required
                                    dir="ltr"
                                    placeholder="••••••••"
                                    class="w-full pr-4 pl-10 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                                >
                                <button
                                    type="button"
                                    @click="showNew = !showNew"
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 hover:text-amber-500 cursor-pointer"
                                >
                                    <span x-show="!showNew" class="text-xs">👁️</span>
                                    <span x-show="showNew" class="text-xs" style="display: none;">🙈</span>
                                </button>
                            </div>
                            @error('new_password') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            تأكيد كلمة المرور الجديدة <span class="text-rose-500">*</span>
                        </label>
                        <input
                            wire:model.defer="new_password_confirmation"
                            type="password"
                            id="new_password_confirmation"
                            required
                            dir="ltr"
                            placeholder="••••••••"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-6 py-2.5 bg-slate-800 dark:bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow-md transition-all font-tajawal flex items-center justify-center gap-2 cursor-pointer"
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
