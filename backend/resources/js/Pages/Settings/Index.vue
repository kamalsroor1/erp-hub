<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    Settings,
    Building2,
    Palette,
    Send,
    HardDrive,
    Cpu,
    RefreshCw
} from 'lucide-vue-next';

// Atomic Settings Tab Components (SOLID SRP)
import BrandingTab from '@/Components/Settings/BrandingTab.vue';
import ThemeTab from '@/Components/Settings/ThemeTab.vue';
import TelegramTab from '@/Components/Settings/TelegramTab.vue';
import BackupTab from '@/Components/Settings/BackupTab.vue';
import SystemTab from '@/Components/Settings/SystemTab.vue';

const props = defineProps({
    settings: { type: Object, required: true },
    active_tab: { type: String, default: 'branding' },
    system_info: { type: Object, default: () => ({}) },
});

const currentTab = ref(props.active_tab || 'branding');

const form = useForm({
    company_name: props.settings.company_name || 'سرور كوفي',
    company_subtitle: props.settings.company_subtitle || 'إدارة المحامص والمخازن',
    company_phone: props.settings.company_phone || '',
    company_address: props.settings.company_address || '',
    invoice_footer_note: props.settings.invoice_footer_note || '',
    show_print_company_name: props.settings.show_print_company_name,
    show_print_subtitle: props.settings.show_print_subtitle,
    show_print_logo: props.settings.show_print_logo,
    thermal_show_customer_balance: props.settings.thermal_show_customer_balance,
    print_show_qr: props.settings.print_show_qr,
    invoice_primary_color: props.settings.invoice_primary_color || 'amber',
    system_theme_color: props.settings.system_theme_color || 'amber',
    telegram_bot_token: props.settings.telegram_bot_token || '',
    telegram_chat_id: props.settings.telegram_chat_id || '',
    telegram_notifications_enabled: props.settings.telegram_notifications_enabled,
    logo_file: null,
    logo_light_file: null,
    logo_dark_file: null,
});

const saveSettings = () => {
    form.post('/settings', {
        preserveScroll: true,
        forceFormData: true,
    });
};

// Telegram Action Handlers
const sendTelegramTest = () => {
    router.post('/settings/telegram/test', {
        bot_token: form.telegram_bot_token,
        chat_id: form.telegram_chat_id,
    }, {
        preserveScroll: true,
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
    <Head :title="$t('settings.title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header Banner -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-theme-light border border-theme-light text-theme-primary flex items-center justify-center text-2xl font-bold shadow-xs shrink-0">
                        <Settings class="w-6 h-6" />
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            {{ $t('settings.title') }}
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-bold mt-0.5">
                            {{ $t('settings.subtitle') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button
                        @click="clearCache"
                        type="button"
                        class="w-full sm:w-auto h-11 px-5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 text-xs font-bold transition active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 shadow-xs"
                    >
                        <RefreshCw class="w-3.5 h-3.5" />
                        <span>{{ $t('settings.clear_cache_btn') }}</span>
                    </button>
                </div>
            </div>

            <!-- Navigation Tabs (5 Tabs - Segmented Horizontal Pill Bar on Mobile) -->
            <div class="flex sm:grid sm:grid-cols-5 gap-1.5 sm:gap-2 bg-slate-100 dark:bg-slate-950 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 font-tajawal overflow-x-auto no-scrollbar">
                <button
                    @click="currentTab = 'branding'"
                    type="button"
                    class="min-h-[44px] py-2.5 px-3.5 sm:px-3 rounded-xl font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center shrink-0 sm:shrink"
                    :class="currentTab === 'branding' ? 'tab-theme-active' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                    <Building2 class="w-4 h-4" />
                    <span>{{ $t('settings.tab_branding') }}</span>
                </button>

                <button
                    @click="currentTab = 'theme'"
                    type="button"
                    class="min-h-[44px] py-2.5 px-3.5 sm:px-3 rounded-xl font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center shrink-0 sm:shrink"
                    :class="currentTab === 'theme' ? 'tab-theme-active' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                    <Palette class="w-4 h-4" />
                    <span>{{ $t('settings.tab_theme') }}</span>
                </button>

                <button
                    @click="currentTab = 'telegram'"
                    type="button"
                    class="min-h-[44px] py-2.5 px-3.5 sm:px-3 rounded-xl font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center shrink-0 sm:shrink"
                    :class="currentTab === 'telegram' ? 'tab-theme-active' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                    <Send class="w-4 h-4" />
                    <span>{{ $t('settings.tab_telegram') }}</span>
                </button>

                <button
                    @click="currentTab = 'backup'"
                    type="button"
                    class="min-h-[44px] py-2.5 px-3.5 sm:px-3 rounded-xl font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center shrink-0 sm:shrink"
                    :class="currentTab === 'backup' ? 'tab-theme-active' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                    <HardDrive class="w-4 h-4" />
                    <span>{{ $t('settings.tab_backup') }}</span>
                </button>

                <button
                    @click="currentTab = 'system'"
                    type="button"
                    class="min-h-[44px] py-2.5 px-3.5 sm:px-3 rounded-xl font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center shrink-0 sm:shrink"
                    :class="currentTab === 'system' ? 'tab-theme-active' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                    <Cpu class="w-4 h-4" />
                    <span>{{ $t('settings.tab_system') }}</span>
                </button>
            </div>

            <!-- Tab Components (SOLID Sub-Components) -->
            <BrandingTab
                v-if="currentTab === 'branding'"
                :form="form"
                @save="saveSettings"
            />

            <ThemeTab
                v-if="currentTab === 'theme'"
                :form="form"
                @save="saveSettings"
            />

            <TelegramTab
                v-if="currentTab === 'telegram'"
                :form="form"
                @save="saveSettings"
                @send-test="sendTelegramTest"
                @send-daily-summary="sendDailySummary"
                @send-low-stock="sendLowStock"
                @send-overdue-shifts="sendOverdueShift"
                @send-backup-telegram="sendBackupTelegram"
            />

            <BackupTab
                v-if="currentTab === 'backup'"
                @send-backup-telegram="sendBackupTelegram"
            />

            <SystemTab
                v-if="currentTab === 'system'"
                :system-info="system_info"
                @clear-cache="clearCache"
            />
        </div>
    </AppLayout>
</template>