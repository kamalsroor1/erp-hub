<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    settings: { type: Object, required: true },
    active_tab: { type: String, default: 'branding' },
    system_info: { type: Object, default: () => ({}) },
});

const currentTab = ref(props.active_tab || 'branding');

const form = useForm({
    company_name: props.settings.company_name,
    company_subtitle: props.settings.company_subtitle,
    company_phone: props.settings.company_phone,
    company_address: props.settings.company_address,
    invoice_footer_note: props.settings.invoice_footer_note,
    show_print_company_name: props.settings.show_print_company_name,
    show_print_subtitle: props.settings.show_print_subtitle,
    show_print_logo: props.settings.show_print_logo,
    thermal_show_customer_balance: props.settings.thermal_show_customer_balance,
    print_show_qr: props.settings.print_show_qr,
    invoice_primary_color: props.settings.invoice_primary_color || 'amber',
    telegram_bot_token: props.settings.telegram_bot_token || '',
    telegram_chat_id: props.settings.telegram_chat_id || '',
    telegram_notifications_enabled: props.settings.telegram_notifications_enabled,
    logo_file: null,
});

const logoPreview = ref(null);

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo_file = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const saveSettings = () => {
    form.post('/settings', {
        preserveScroll: true,
        forceFormData: true,
    });
};

// Telegram Actions
const sendingTest = ref(false);
const sendTelegramTest = () => {
    sendingTest.value = true;
    router.post('/settings/telegram/test', {
        bot_token: form.telegram_bot_token,
        chat_id: form.telegram_chat_id,
    }, {
        preserveScroll: true,
        onFinish: () => { sendingTest.value = false; }
    });
};

const sendDailySummary = () => {
    router.post('/settings/telegram/daily-summary', {}, { preserveScroll: true });
};

const sendLowStock = () => {
    router.post('/settings/telegram/low-stock', {}, { preserveScroll: true });
};

const sendOverdueShift = () => {
    router.post('/settings/telegram/overdue-shifts', {}, { preserveScroll: true });
};

const sendBackupTelegram = () => {
    router.post('/settings/telegram/backup', {}, { preserveScroll: true });
};

const clearCache = () => {
    router.post('/settings/clear-cache', {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="إعدادات النظام والتحكم الشامل" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header Banner -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-2xl font-bold">
                        ⚙️
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            إعدادات النظام والتحكم الشامل
                        </h1>
                        <p class="text-xs text-slate-400 font-bold mt-0.5">
                            تخصيص الهوية واللوجو، إشعارات تيليجرام التلقائية، النسخ الاحتياطي، وصيانة السيرفر
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="clearCache"
                        type="button"
                        class="h-10 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 text-xs font-bold transition cursor-pointer flex items-center gap-1.5"
                    >
                        <span>⚡</span>
                        <span>تنظيف وتسريع الكاش</span>
                    </button>
                </div>
            </div>

            <!-- Navigation Tabs (4 Tabs) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 bg-slate-950 p-1.5 rounded-2xl border border-slate-800">
                <button
                    @click="currentTab = 'branding'"
                    type="button"
                    class="py-3 px-4 rounded-xl font-bold text-xs transition cursor-pointer flex items-center justify-center gap-2"
                    :class="currentTab === 'branding' ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-slate-400 hover:text-white'"
                >
                    <span>🏢</span>
                    <span>الهوية والطباعة</span>
                </button>

                <button
                    @click="currentTab = 'telegram'"
                    type="button"
                    class="py-3 px-4 rounded-xl font-bold text-xs transition cursor-pointer flex items-center justify-center gap-2"
                    :class="currentTab === 'telegram' ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-slate-400 hover:text-white'"
                >
                    <span>✈️</span>
                    <span>إشعارات تيليجرام</span>
                </button>

                <button
                    @click="currentTab = 'backup'"
                    type="button"
                    class="py-3 px-4 rounded-xl font-bold text-xs transition cursor-pointer flex items-center justify-center gap-2"
                    :class="currentTab === 'backup' ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-slate-400 hover:text-white'"
                >
                    <span>💾</span>
                    <span>النسخ الاحتياطي</span>
                </button>

                <button
                    @click="currentTab = 'system'"
                    type="button"
                    class="py-3 px-4 rounded-xl font-bold text-xs transition cursor-pointer flex items-center justify-center gap-2"
                    :class="currentTab === 'system' ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-slate-400 hover:text-white'"
                >
                    <span>⚡</span>
                    <span>الأداء والصيانة</span>
                </button>
            </div>

            <!-- Tab 1: Branding & Printing -->
            <div v-if="currentTab === 'branding'" class="space-y-6">
                <form @submit.prevent="saveSettings" class="space-y-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
                        <!-- Logo Upload Section -->
                        <div class="flex flex-col sm:flex-row items-center gap-6 pb-6 border-b border-slate-800">
                            <div class="w-24 h-24 rounded-3xl bg-slate-950 border border-slate-800 p-2 flex items-center justify-center overflow-hidden shadow-inner shrink-0">
                                <img
                                    :src="logoPreview || '/logo.png'"
                                    alt="شعار المؤسسة"
                                    class="w-full h-full object-contain"
                                >
                            </div>

                            <div class="space-y-2 text-center sm:text-right">
                                <h3 class="text-sm font-black text-white">شعار المؤسسة المطبوع على الفواتير (Logo)</h3>
                                <p class="text-xs text-slate-400">يُفضل رفع صورة بخلفية شفافة PNG بحجم لا يتجاوز 3 ميجابايت</p>
                                <div>
                                    <label class="inline-block px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs font-bold border border-slate-700 cursor-pointer transition">
                                        <span>📁 اختيار شعار جديد...</span>
                                        <input type="file" accept="image/*" @change="handleLogoChange" class="hidden">
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">اسم النشاط أو المنشأة *</label>
                                <input
                                    v-model="form.company_name"
                                    type="text"
                                    required
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">الوصف الفرعي (تحت الاسم)</label>
                                <input
                                    v-model="form.company_subtitle"
                                    type="text"
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">رقم الهاتف الرسمي</label>
                                <input
                                    v-model="form.company_phone"
                                    type="text"
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">العنوان الرسمي</label>
                                <input
                                    v-model="form.company_address"
                                    type="text"
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                                >
                            </div>

                            <div class="sm:col-span-2 space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">عبارة تذييل الفاتورة (Footer Note)</label>
                                <textarea
                                    v-model="form.invoice_footer_note"
                                    rows="2"
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Printing Toggles Matrix -->
                        <div class="space-y-3 pt-4 border-t border-slate-800">
                            <h3 class="text-xs font-black text-amber-400">خيارات تخصيص الإيصالات والطباعة:</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 cursor-pointer">
                                    <input type="checkbox" v-model="form.show_print_logo" class="rounded text-amber-500 focus:ring-0">
                                    <span class="text-xs font-bold text-slate-300">إظهار الشعار (Logo) على الفواتير والإيصالات</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 cursor-pointer">
                                    <input type="checkbox" v-model="form.show_print_company_name" class="rounded text-amber-500 focus:ring-0">
                                    <span class="text-xs font-bold text-slate-300">إظهار اسم المنشأة أعلى الفاتورة</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 cursor-pointer">
                                    <input type="checkbox" v-model="form.thermal_show_customer_balance" class="rounded text-amber-500 focus:ring-0">
                                    <span class="text-xs font-bold text-slate-300">إظهار رصيد العميل المتبقي على الإيصال الحراري</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 cursor-pointer">
                                    <input type="checkbox" v-model="form.print_show_qr" class="rounded text-amber-500 focus:ring-0">
                                    <span class="text-xs font-bold text-slate-300">تضمين رمز الاستجابة السريع (QR Code) الذكي</span>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4 border-t border-slate-800">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="h-12 px-8 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                            >
                                {{ form.processing ? 'جاري الحفظ...' : 'حفظ إعدادات الهوية والطباعة 💾' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab 2: Telegram Notifications -->
            <div v-if="currentTab === 'telegram'" class="space-y-6">
                <form @submit.prevent="saveSettings" class="space-y-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div>
                                <h2 class="text-sm font-black text-white flex items-center gap-2">
                                    <span>✈️</span>
                                    <span>إعدادات وتنبيهات بوت تيليجرام</span>
                                </h2>
                                <p class="text-xs text-slate-400 mt-0.5">إرسال تقارير المبيعات، إنذار النواقص، وإغلاق الورديات فورياً لهاتفك</p>
                            </div>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="form.telegram_notifications_enabled" class="rounded text-amber-500 focus:ring-0">
                                <span class="text-xs font-bold text-white">تفعيل البوت</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">رمز توكن البوت (Bot Token) *</label>
                                <input
                                    v-model="form.telegram_bot_token"
                                    type="text"
                                    placeholder="123456789:ABCdef..."
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">معرف المحادثة أو الجروب (Chat ID) *</label>
                                <input
                                    v-model="form.telegram_chat_id"
                                    type="text"
                                    placeholder="-100xxxxxxxxx أو 12345678"
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                                >
                            </div>
                        </div>

                        <!-- Live Action Testing Triggers -->
                        <div class="space-y-3 pt-4 border-t border-slate-800">
                            <h3 class="text-xs font-black text-amber-400">إجراءات واختبارات الإرسال الفوري لتيليجرام:</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <button
                                    @click="sendTelegramTest"
                                    type="button"
                                    class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-amber-500/50 text-slate-200 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <span>✉️</span>
                                    <span>إرسال رسالة تجريبية</span>
                                </button>

                                <button
                                    @click="sendDailySummary"
                                    type="button"
                                    class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-amber-500/50 text-slate-200 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <span>📊</span>
                                    <span>إرسال تقرير اليومية الشامل</span>
                                </button>

                                <button
                                    @click="sendLowStock"
                                    type="button"
                                    class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-amber-500/50 text-slate-200 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <span>🚨</span>
                                    <span>إرسال إنذار النواقص</span>
                                </button>

                                <button
                                    @click="sendOverdueShift"
                                    type="button"
                                    class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-amber-500/50 text-slate-200 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <span>⏰</span>
                                    <span>إنذار الشفتات المفتوحة</span>
                                </button>

                                <button
                                    @click="sendBackupTelegram"
                                    type="button"
                                    class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-amber-500/50 text-slate-200 text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <span>💾</span>
                                    <span>إرسال نسخة احتياطية لتيليجرام</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-800">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="h-12 px-8 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                            >
                                حفظ إعدادات تيليجرام 💾
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab 3: Backups -->
            <div v-if="currentTab === 'backup'" class="space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
                    <div class="border-b border-slate-800 pb-4">
                        <h2 class="text-sm font-black text-white flex items-center gap-2">
                            <span>💾</span>
                            <span>النسخ الاحتياطي السحابي وقاعدة البيانات</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">توليد وتنزيل ملفات النسخ الاحتياطي المضغوطة SQL.gz لحفظ بياناتك بأمان تام</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 space-y-3 flex flex-col justify-between">
                            <div class="space-y-2">
                                <h3 class="text-xs font-black text-white flex items-center gap-2">
                                    <span>💻</span>
                                    <span>تنزيل نسخة احتياطية مباشرة لجهازك</span>
                                </h3>
                                <p class="text-xs text-slate-400 leading-relaxed">
                                    يتم توليد ملف SQL مضغوط GZIP يحتوي على كامل بيانات الفواتير، الأصناف، العملاء، والمخزون.
                                </p>
                            </div>

                            <a
                                href="/settings/backup/download"
                                class="h-11 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 transition cursor-pointer"
                            >
                                <span>📥</span>
                                <span>تحميل ملف SQL.gz الآن</span>
                            </a>
                        </div>

                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-5 space-y-3 flex flex-col justify-between">
                            <div class="space-y-2">
                                <h3 class="text-xs font-black text-white flex items-center gap-2">
                                    <span>☁️</span>
                                    <span>إرسال النسخة الاحتياطية لتيليجرام</span>
                                </h3>
                                <p class="text-xs text-slate-400 leading-relaxed">
                                    يتم ضغط قاعدة البيانات وإرسالها فورياً لجروب أو محادثة تيليجرام الخاصة بالإدارة.
                                </p>
                            </div>

                            <button
                                @click="sendBackupTelegram"
                                type="button"
                                class="h-11 px-6 rounded-2xl bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer"
                            >
                                <span>✈️</span>
                                <span>إرسال لقناة تيليجرام</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: System Performance & Maintenance -->
            <div v-if="currentTab === 'system'" class="space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
                    <div class="border-b border-slate-800 pb-4">
                        <h2 class="text-sm font-black text-white flex items-center gap-2">
                            <span>⚡</span>
                            <span>أداء النظام، الكاش، وبيئة التشغيل</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">صيانة سريعة وتسريع الأداء وإعادة تهيئة مسارات التوجيه والتخزين المؤقت</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                            <span class="text-[10px] text-slate-400 font-tajawal">إصدار PHP:</span>
                            <div class="text-sm font-black text-amber-400">{{ system_info.php_version }}</div>
                        </div>

                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                            <span class="text-[10px] text-slate-400 font-tajawal">إصدار Laravel:</span>
                            <div class="text-sm font-black text-white">{{ system_info.laravel_version }}</div>
                        </div>

                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                            <span class="text-[10px] text-slate-400 font-tajawal">محرك قاعدة البيانات:</span>
                            <div class="text-sm font-black text-emerald-400">{{ system_info.db_driver }}</div>
                        </div>

                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                            <span class="text-[10px] text-slate-400 font-tajawal">بيئة التشغيل:</span>
                            <div class="text-sm font-black text-indigo-400">{{ system_info.environment }}</div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <a
                                href="/pulse"
                                target="_blank"
                                class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-purple-400 border border-slate-700 text-xs font-bold flex items-center gap-1.5 transition"
                            >
                                <span>📊</span>
                                <span>مراقبة Pulse</span>
                            </a>

                            <a
                                href="/telescope"
                                target="_blank"
                                class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-teal-400 border border-slate-700 text-xs font-bold flex items-center gap-1.5 transition"
                            >
                                <span>🔭</span>
                                <span>تليسكوب Telescope</span>
                            </a>
                        </div>

                        <button
                            @click="clearCache"
                            type="button"
                            class="h-11 px-6 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 transition transform active:scale-95 cursor-pointer"
                        >
                            ⚡ تنظيف وتسريع الكاش الآن
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>