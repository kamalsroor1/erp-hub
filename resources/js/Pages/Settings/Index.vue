<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({
    company_name: props.settings.company_name,
    company_subtitle: props.settings.company_subtitle,
    company_phone: props.settings.company_phone,
    company_address: props.settings.company_address,
    invoice_footer_note: props.settings.invoice_footer_note,
    show_print_company_name: Boolean(props.settings.show_print_company_name),
    show_print_subtitle: Boolean(props.settings.show_print_subtitle),
    show_print_logo: Boolean(props.settings.show_print_logo),
    thermal_show_customer_balance: Boolean(props.settings.thermal_show_customer_balance),
    print_show_qr: Boolean(props.settings.print_show_qr),
    telegram_bot_token: props.settings.telegram_bot_token || '',
    telegram_chat_id: props.settings.telegram_chat_id || '',
    telegram_notifications_enabled: Boolean(props.settings.telegram_notifications_enabled),
});

const submitSettings = () => {
    form.post('/settings', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="إعدادات النظام والطباعة" />

    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">⚙️</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            إعدادات النظام والهوية والطباعة الحرارية
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        تخصيص ترويسة الفواتير، بيانات الشركة، الإشعارات، وتفضيلات النظام
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitSettings" class="space-y-6">
                <!-- Company & Branding Details -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span>🏢</span>
                        <span>بيانات الشركة وهوية الفواتير</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">اسم المنشأة / الشركة *</label>
                            <input
                                v-model="form.company_name"
                                type="text"
                                required
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">العنوان الفرعي / النشاط</label>
                            <input
                                v-model="form.company_subtitle"
                                type="text"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">رقم الهاتف / الدعم</label>
                            <input
                                v-model="form.company_phone"
                                type="text"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">العنوان الرئيسي</label>
                            <input
                                v-model="form.company_address"
                                type="text"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-300">ملاحظة أسفل الفاتورة (Footer Note)</label>
                            <input
                                v-model="form.invoice_footer_note"
                                type="text"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                        </div>
                    </div>
                </div>

                <!-- Print Options -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span>🖨️</span>
                        <span>خيارات الطباعة الحرارية (Thermal 80mm) و A4</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-950/60 border border-slate-800 cursor-pointer">
                            <input type="checkbox" v-model="form.show_print_logo" class="rounded text-amber-500 focus:ring-0">
                            <span class="text-xs font-bold text-white">إظهار اللوجو الذهبي في الفاتورة</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-950/60 border border-slate-800 cursor-pointer">
                            <input type="checkbox" v-model="form.show_print_company_name" class="rounded text-amber-500 focus:ring-0">
                            <span class="text-xs font-bold text-white">طباعة اسم المنشأة في الترويسة</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-950/60 border border-slate-800 cursor-pointer">
                            <input type="checkbox" v-model="form.thermal_show_customer_balance" class="rounded text-amber-500 focus:ring-0">
                            <span class="text-xs font-bold text-white">طباعة رصيد ومديونية العميل أسفل الإيصال</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-950/60 border border-slate-800 cursor-pointer">
                            <input type="checkbox" v-model="form.print_show_qr" class="rounded text-amber-500 focus:ring-0">
                            <span class="text-xs font-bold text-white">طباعة باركود الاستجابة السريعة (QR Code)</span>
                        </label>
                    </div>
                </div>

                <!-- Telegram Notifications -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-white border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span>✈️</span>
                        <span>إشعارات بوت تيليجرام للأدمن (Z-Report & المبيعات اليومية)</span>
                    </h2>

                    <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-950/60 border border-slate-800 cursor-pointer mb-3">
                        <input type="checkbox" v-model="form.telegram_notifications_enabled" class="rounded text-amber-500 focus:ring-0">
                        <span class="text-xs font-bold text-white">تفعيل إرسال إشعارات الإغلاق اليومي للتيليجرام</span>
                    </label>

                    <div v-if="form.telegram_notifications_enabled" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">Telegram Bot Token</label>
                            <input
                                v-model="form.telegram_bot_token"
                                type="text"
                                placeholder="123456789:ABCdefGhI..."
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">Telegram Chat ID</label>
                            <input
                                v-model="form.telegram_chat_id"
                                type="text"
                                placeholder="-100xxxxxxxxxx"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="h-12 px-8 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                    >
                        <span>💾</span>
                        <span>{{ form.processing ? 'جاري الحفظ...' : 'حفظ الإعدادات والتفضيلات' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>