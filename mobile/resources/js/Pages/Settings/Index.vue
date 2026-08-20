<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    stores: { type: Array, default: () => [] },
    users_count: { type: Number, default: 0 },
});

const activeTab = ref('branding'); // 'branding' | 'printing' | 'telegram'

const form = useForm({
    company_name: props.settings.company_name || 'سرور كوفي',
    company_subtitle: props.settings.company_subtitle || 'لتوريدات خامات مطاحن البن',
    company_phone: props.settings.company_phone || '01000000000',
    company_address: props.settings.company_address || 'المخزن الرئيسي',
    commercial_register: props.settings.commercial_register || '',
    show_print_company_name: Boolean(props.settings.show_print_company_name),
    show_print_subtitle: Boolean(props.settings.show_print_subtitle),
    show_print_logo: Boolean(props.settings.show_print_logo),
    receipt_footer_message: props.settings.receipt_footer_message || 'شكراً لتعاملكم معنا ونتمنى لكم يوماً سعيداً ☕',
    telegram_notifications_enabled: Boolean(props.settings.telegram_notifications_enabled),
    telegram_bot_token: props.settings.telegram_bot_token || '',
    telegram_chat_id: props.settings.telegram_chat_id || '',
});

const saveSettings = () => {
    haptic.success();
    form.post('/settings');
};
</script>

<template>
    <MobileLayout>
        <div class="space-y-4 pb-24 select-none">
            <!-- Header Banner -->
            <div class="bg-gradient-to-l from-slate-800 via-slate-900 to-slate-950 rounded-3xl p-4 text-white shadow-xl shadow-slate-900/30 flex items-center justify-between border border-slate-700/50">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">⚙️</span>
                        <h2 class="text-base font-black">إعدادات النظام والطباعة</h2>
                    </div>
                    <p class="text-[11px] text-slate-400 font-bold mt-0.5">
                        تخصيص هوية المحل، إيصالات الطباعة الحرارية، وإشعارات البوت
                    </p>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center text-xl shrink-0">
                    ☕
                </div>
            </div>

            <!-- Settings Tabs -->
            <div class="grid grid-cols-3 gap-1.5 bg-slate-200/70 dark:bg-slate-900 p-1 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs">
                <button
                    @click="activeTab = 'branding'"
                    type="button"
                    class="py-2 rounded-xl font-black transition touch-active text-center"
                    :class="activeTab === 'branding' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                >
                    🏢 هوية المحل
                </button>
                <button
                    @click="activeTab = 'printing'"
                    type="button"
                    class="py-2 rounded-xl font-black transition touch-active text-center"
                    :class="activeTab === 'printing' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                >
                    🖨️ الفواتير
                </button>
                <button
                    @click="activeTab = 'telegram'"
                    type="button"
                    class="py-2 rounded-xl font-black transition touch-active text-center"
                    :class="activeTab === 'telegram' ? 'bg-white dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400'"
                >
                    📱 التنبيهات
                </button>
            </div>

            <!-- Settings Form -->
            <form @submit.prevent="saveSettings" class="space-y-4">
                <!-- TAB 1: BRANDING -->
                <div v-if="activeTab === 'branding'" class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3.5 animate-in fade-in">
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 mb-1">اسم المؤسسة / المحل:</label>
                        <input v-model="form.company_name" type="text" required class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 mb-1">الوصف والنشاط التجاري (السطر الثاني):</label>
                        <input v-model="form.company_subtitle" type="text" class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white" />
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">هاتف المحل:</label>
                            <input v-model="form.company_phone" type="text" class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 mb-1">رقم السجل التجاري / الضريبي:</label>
                            <input v-model="form.commercial_register" type="text" placeholder="اختياري..." class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 mb-1">عنوان الفرع الرئيسي:</label>
                        <input v-model="form.company_address" type="text" class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-bold text-slate-900 dark:text-white" />
                    </div>
                </div>

                <!-- TAB 2: PRINTING & RECEIPTS -->
                <div v-if="activeTab === 'printing'" class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4 animate-in fade-in">
                    <div class="space-y-3">
                        <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 cursor-pointer">
                            <span class="text-xs font-bold text-slate-900 dark:text-white">إظهار اسم المحل في ترويسة الفاتورة</span>
                            <input v-model="form.show_print_company_name" type="checkbox" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500" />
                        </label>

                        <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 cursor-pointer">
                            <span class="text-xs font-bold text-slate-900 dark:text-white">إظهار الوصف والنشاط في الفاتورة</span>
                            <input v-model="form.show_print_subtitle" type="checkbox" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500" />
                        </label>

                        <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 cursor-pointer">
                            <span class="text-xs font-bold text-slate-900 dark:text-white">إظهار الشعار في الإيصال الحراري</span>
                            <input v-model="form.show_print_logo" type="checkbox" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500" />
                        </label>
                    </div>

                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 mb-1">رسالة الشكر أسفل الإيصال الحراري:</label>
                        <textarea v-model="form.receipt_footer_message" rows="2" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl p-3 text-xs font-bold text-slate-900 dark:text-white"></textarea>
                    </div>
                </div>

                <!-- TAB 3: TELEGRAM BOT -->
                <div v-if="activeTab === 'telegram'" class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3.5 animate-in fade-in">
                    <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 cursor-pointer">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">تفعيل إشعارات تيليجرام الفورية للإدارة</span>
                        <input v-model="form.telegram_notifications_enabled" type="checkbox" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500" />
                    </label>

                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 mb-1">Telegram Bot Token:</label>
                        <input v-model="form.telegram_bot_token" type="text" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 mb-1">Telegram Chat ID:</label>
                        <input v-model="form.telegram_chat_id" type="text" placeholder="-100123456789" class="w-full h-10 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl px-3 text-xs font-mono font-bold text-slate-900 dark:text-white" />
                    </div>
                </div>

                <!-- Save Button -->
                <button
                    :disabled="form.processing"
                    type="submit"
                    class="w-full h-13 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-emerald-600/30 flex items-center justify-center gap-2 transition touch-active"
                >
                    <span>💾</span>
                    <span>{{ form.processing ? 'جاري الحفظ...' : 'حفظ وتحديث إعدادات النظام' }}</span>
                </button>
            </form>
        </div>
    </MobileLayout>
</template>
