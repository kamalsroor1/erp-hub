<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useTheme, PRESET_PALETTES } from '@/Composables/useTheme';
import {
    Settings,
    Building2,
    Palette,
    Send,
    HardDrive,
    Cpu,
    Paintbrush,
    Eye,
    Save,
    Trash2,
    Check,
    CheckCircle2,
    RotateCcw,
    AlertTriangle,
    Sun,
    Moon,
    Sparkles,
    Zap,
    RefreshCw
} from 'lucide-vue-next';

const props = defineProps({
    settings: { type: Object, required: true },
    active_tab: { type: String, default: 'branding' },
    system_info: { type: Object, default: () => ({}) },
});

const currentTab = ref(props.active_tab || 'branding');

const { applyColorTheme } = useTheme();

const palettes = [
    { id: 'amber', name: 'الكهرمان / الذهبي الأصيل', sub: 'الهوية الرسمية الافتراضية لسرور كوفي', hex: '#f59e0b', ring: 'ring-amber-500', bg: 'bg-amber-500', icon: '🌟' },
    { id: 'emerald', name: 'الأخضر الزمردي الملكي', sub: 'طابع مالي فاخر ونقاء عالي', hex: '#10b981', ring: 'ring-emerald-500', bg: 'bg-emerald-500', icon: '🌿' },
    { id: 'blue', name: 'الأزرق الملكي (Sapphire)', sub: 'أناقة كلاسيكية احترافية للمؤسسات', hex: '#3b82f6', ring: 'ring-blue-500', bg: 'bg-blue-500', icon: '🔵' },
    { id: 'purple', name: 'البنفسجي الإمبراطوري', sub: 'فخامة وتميز إداري جذاب', hex: '#a855f7', ring: 'ring-purple-500', bg: 'bg-purple-500', icon: '🟣' },
    { id: 'rose', name: 'الياقوتي القرمزي (Ruby Rose)', sub: 'طاقة وحيوية واضحة للشاشات', hex: '#f43f5e', ring: 'ring-rose-500', bg: 'bg-rose-500', icon: '🌹' },
    { id: 'orange', name: 'البرتقالي الدافئ / بن محمص', sub: 'دفء الكافيهات وحبوب القهوة المحمصة', hex: '#f97316', ring: 'ring-orange-500', bg: 'bg-orange-500', icon: '☕' },
    { id: 'teal', name: 'السماوي التركوازي (Ocean Teal)', sub: 'طابع عصري متجدد ومريح للعين', hex: '#14b8a6', ring: 'ring-teal-500', bg: 'bg-teal-500', icon: '🌊' },
    { id: 'indigo', name: 'النيلي الداكن (Deep Indigo)', sub: 'هدوء تكنولوجي عصري حديث', hex: '#6366f1', ring: 'ring-indigo-500', bg: 'bg-indigo-500', icon: '🌌' },
];

const extendedSwatches = [
    { hex: '#06b6d4', name: 'Cyan' },
    { hex: '#84cc16', name: 'Lime' },
    { hex: '#ec4899', name: 'Pink' },
    { hex: '#e11d48', name: 'Crimson' },
    { hex: '#8b5cf6', name: 'Violet' },
    { hex: '#0ea5e9', name: 'Sky' },
    { hex: '#10b981', name: 'Emerald' },
    { hex: '#eab308', name: 'Yellow' },
    { hex: '#d97706', name: 'Bronze' },
    { hex: '#64748b', name: 'Slate' },
    { hex: '#14b8a6', name: 'Mint' },
    { hex: '#f97316', name: 'Coral' },
];

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

const isPreset = computed(() => {
    return palettes.some(p => p.id === form.system_theme_color);
});

const activeHexColor = computed(() => {
    const preset = palettes.find(p => p.id === form.system_theme_color);
    if (preset) return preset.hex;
    if (form.system_theme_color && form.system_theme_color.startsWith('#')) return form.system_theme_color;
    return '#f59e0b';
});

const customPickerColor = ref(activeHexColor.value);

const selectPalette = (paletteId) => {
    form.system_theme_color = paletteId;
    const preset = palettes.find(p => p.id === paletteId);
    if (preset) customPickerColor.value = preset.hex;
    applyColorTheme(paletteId);
};

const onCustomColorInput = (e) => {
    const newHex = e.target.value;
    customPickerColor.value = newHex;
    form.system_theme_color = newHex;
    applyColorTheme(newHex);
};

const onHexTextInput = (val) => {
    if (!val) return;
    let hex = val.trim();
    if (!hex.startsWith('#')) hex = `#${hex}`;
    customPickerColor.value = hex;
    form.system_theme_color = hex;
    applyColorTheme(hex);
};

const logoPreview = ref(null);
const logoLightPreview = ref(null);
const logoDarkPreview = ref(null);

const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo_file = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handleLogoLightChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo_light_file = file;
        logoLightPreview.value = URL.createObjectURL(file);
    }
};

const handleLogoDarkChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo_dark_file = file;
        logoDarkPreview.value = URL.createObjectURL(file);
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

            <!-- Navigation Tabs (5 Tabs) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-1.5 sm:gap-2 bg-slate-100 dark:bg-slate-950 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 font-tajawal">
                <button
                    @click="currentTab = 'branding'"
                    type="button"
                    class="min-h-[44px] py-2.5 px-3 rounded-xl font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center"
                    :class="currentTab === 'branding' ? 'tab-theme-active' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                    <Building2 class="w-4 h-4" />
                    <span>{{ $t('settings.tab_branding') }}</span>
                </button>

                <button
                    @click="currentTab = 'theme'"
                    type="button"
                    class="min-h-[44px] py-2.5 px-3 rounded-xl font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center"
                    :class="currentTab === 'theme' ? 'tab-theme-active' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                    <Palette class="w-4 h-4" />
                    <span>{{ $t('settings.tab_theme') }}</span>
                </button>

                <button
                    @click="currentTab = 'telegram'"
                    type="button"
                    class="min-h-[44px] py-2.5 px-3 rounded-xl font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center"
                    :class="currentTab === 'telegram' ? 'tab-theme-active' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                    <Send class="w-4 h-4" />
                    <span>{{ $t('settings.tab_telegram') }}</span>
                </button>

                <button
                    @click="currentTab = 'backup'"
                    type="button"
                    class="min-h-[44px] py-2.5 px-3 rounded-xl font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center"
                    :class="currentTab === 'backup' ? 'tab-theme-active' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                    <HardDrive class="w-4 h-4" />
                    <span>{{ $t('settings.tab_backup') }}</span>
                </button>

                <button
                    @click="currentTab = 'system'"
                    type="button"
                    class="min-h-[44px] py-2.5 px-3 rounded-xl font-bold text-xs transition active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center col-span-2 sm:col-span-1"
                    :class="currentTab === 'system' ? 'tab-theme-active' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                >
                    <Cpu class="w-4 h-4" />
                    <span>{{ $t('settings.tab_system') }}</span>
                </button>
            </div>

            <!-- Tab: Theme & Colors Customization -->
            <div v-if="currentTab === 'theme'" class="space-y-6">
                <form @submit.prevent="saveSettings" class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs space-y-6">
                        <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                            <h2 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>🎨</span>
                                <span>{{ $t('settings.theme_title') }}</span>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $t('settings.theme_sub') }}</p>
                        </div>

                        <!-- Palettes Grid -->
                        <div class="space-y-3">
                            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.palette_select_title') }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                <div
                                    v-for="p in palettes"
                                    :key="p.id"
                                    @click="selectPalette(p.id)"
                                    class="p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 relative group flex flex-col justify-between gap-3 shadow-xs"
                                    :style="form.system_theme_color === p.id ? { borderColor: p.hex, backgroundColor: `${p.hex}15`, boxShadow: `0 0 0 2px ${p.hex}30` } : {}"
                                    :class="form.system_theme_color === p.id
                                        ? ''
                                        : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 hover:border-slate-300 dark:hover:border-slate-700'"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-xl">{{ p.icon }}</span>
                                            <div class="w-6 h-6 rounded-full shadow-sm border border-white/20 shrink-0" :style="{ backgroundColor: p.hex }"></div>
                                        </div>
                                        <span v-if="form.system_theme_color === p.id" class="w-5 h-5 rounded-full text-white font-black text-xs flex items-center justify-center shadow-xs" :style="{ backgroundColor: p.hex }">
                                            ✓
                                        </span>
                                    </div>

                                    <div>
                                        <h4 class="font-black text-xs text-slate-900 dark:text-white">{{ p.name }}</h4>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-tight">{{ p.sub }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 🎨 Custom Color Picker & Color Calendar Section -->
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                                <div>
                                    <h3 class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-2">
                                        <Paintbrush class="w-4 h-4 text-theme-primary" />
                                        <span>{{ $t('settings.custom_color_title') }}</span>
                                    </h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $t('settings.custom_color_sub') }}</p>
                                </div>
                                <span v-if="!isPreset" class="px-2.5 py-1 rounded-xl text-[11px] font-black bg-theme-light text-theme-primary border border-theme-light self-start sm:self-auto flex items-center gap-1">
                                    <Check class="w-3.5 h-3.5" />
                                    <span>{{ $t('settings.custom_color_badge') }}</span>
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                <!-- Interactive Color Wheel / Native Input -->
                                <div class="flex items-center gap-3 bg-white dark:bg-slate-900 p-2.5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                                    <div class="relative w-10 h-10 rounded-xl overflow-hidden shadow-xs cursor-pointer border border-slate-300 dark:border-slate-700 flex items-center justify-center">
                                        <input
                                            type="color"
                                            :value="activeHexColor"
                                            @input="onCustomColorInput"
                                            class="absolute -inset-4 w-20 h-20 opacity-0 cursor-pointer"
                                            :title="$t('settings.custom_color_label')"
                                        >
                                        <div class="w-full h-full rounded-xl transition-transform hover:scale-110" :style="{ backgroundColor: activeHexColor }"></div>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold block">{{ $t('settings.custom_color_label') }}</span>
                                        <span class="text-xs font-mono font-black text-slate-900 dark:text-white">{{ activeHexColor.toUpperCase() }}</span>
                                    </div>
                                </div>

                                <!-- Direct HEX Input Field -->
                                <div class="flex items-center gap-2 bg-white dark:bg-slate-900 px-3 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
                                    <span class="text-xs font-bold text-slate-400">#</span>
                                    <input
                                        type="text"
                                        :value="activeHexColor.replace('#', '')"
                                        @input="onHexTextInput($event.target.value)"
                                        maxlength="7"
                                        placeholder="3B82F6"
                                        class="w-24 bg-transparent text-xs font-mono font-black text-slate-900 dark:text-white uppercase focus:outline-none"
                                    >
                                </div>
                            </div>

                            <!-- Extended Quick Swatches Calendar -->
                            <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-800/80">
                                <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 block">{{ $t('settings.quick_swatches') }}</span>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button
                                        v-for="swatch in extendedSwatches"
                                        :key="swatch.hex"
                                        type="button"
                                        @click="onHexTextInput(swatch.hex)"
                                        class="w-7 h-7 rounded-xl transition transform hover:scale-125 cursor-pointer shadow-xs border border-white/20 relative flex items-center justify-center"
                                        :style="{ backgroundColor: swatch.hex }"
                                        :title="swatch.name + ' (' + swatch.hex + ')'"
                                    >
                                        <span v-if="activeHexColor.toLowerCase() === swatch.hex.toLowerCase()" class="text-white text-[10px] font-black">✓</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Live Real-Time Preview Card -->
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-4">
                            <h3 class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <Eye class="w-4 h-4 text-theme-primary" />
                                <span>{{ $t('settings.live_preview_title') }}</span>
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                                <!-- Sample KPI Card -->
                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2 shadow-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $t('settings.preview_kpi_sales') }}</span>
                                        <Zap class="w-4 h-4 text-theme-primary" />
                                    </div>
                                    <div class="text-2xl font-black font-mono text-theme-primary">
                                        24,850.00 <span class="text-xs font-bold text-slate-500">ج.م</span>
                                    </div>
                                </div>

                                <!-- Sample Action Button -->
                                <button
                                    type="button"
                                    class="h-12 px-5 rounded-2xl btn-primary-theme font-black text-xs flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer"
                                >
                                    <Zap class="w-4 h-4 fill-current" />
                                    <span>{{ $t('settings.preview_button_active') }} (F2)</span>
                                </button>

                                <!-- Sample Badge & Store Chip -->
                                <div class="flex flex-col items-center sm:items-start gap-2">
                                    <span class="px-3 py-1.5 rounded-xl text-xs font-black badge-theme flex items-center gap-1.5 shadow-xs">
                                        <CheckCircle2 class="w-3.5 h-3.5" />
                                        <span>الفرع النشط: محمص سرور الرئيسي</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="h-12 px-8 rounded-2xl btn-primary-theme font-black text-xs transition transform active:scale-95 cursor-pointer disabled:opacity-50 flex items-center gap-2"
                            >
                                <Palette class="w-4 h-4" />
                                <span>{{ form.processing ? $t('common.save') + '...' : $t('settings.save_theme_btn') }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab 1: Branding & Printing -->
            <div v-if="currentTab === 'branding'" class="space-y-6">
                <form @submit.prevent="saveSettings" class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs space-y-6">
                        <!-- Dual Logo Upload Section (Light Mode & Dark Mode) -->
                        <div class="pb-6 border-b border-slate-200 dark:border-slate-800 space-y-4">
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                                    <Building2 class="w-4 h-4 text-theme-primary" />
                                    <span>{{ $t('settings.company_logo') }}</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $t('settings.logo_hint') }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- ☀️ Light Mode Logo Card -->
                                <div class="p-4 rounded-3xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex items-center gap-4 shadow-xs">
                                    <div class="w-20 h-20 rounded-2xl bg-white border border-slate-200 p-2 flex items-center justify-center overflow-hidden shadow-xs shrink-0">
                                        <img
                                            :src="logoLightPreview || '/logo-light.png'"
                                            alt="شعار الوضع الفاتح"
                                            class="w-full h-full object-contain"
                                        >
                                    </div>

                                    <div class="space-y-1.5 flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <Sun class="w-4 h-4 text-amber-500" />
                                            <h4 class="text-xs font-black text-slate-900 dark:text-white truncate">{{ $t('settings.company_logo_light') }}</h4>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">{{ $t('settings.logo_light_hint') }}</p>
                                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 cursor-pointer transition shadow-xs">
                                            <span>{{ $t('settings.choose_logo_light') }}</span>
                                            <input type="file" accept="image/*" @change="handleLogoLightChange" class="hidden">
                                        </label>
                                    </div>
                                </div>

                                <!-- 🌙 Dark Mode Logo Card -->
                                <div class="p-4 rounded-3xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 flex items-center gap-4 shadow-xs">
                                    <div class="w-20 h-20 rounded-2xl bg-slate-900 border border-slate-800 p-2 flex items-center justify-center overflow-hidden shadow-xs shrink-0">
                                        <img
                                            :src="logoDarkPreview || '/logo-dark.png'"
                                            alt="شعار الوضع الداكن"
                                            class="w-full h-full object-contain"
                                        >
                                    </div>

                                    <div class="space-y-1.5 flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <Moon class="w-4 h-4 text-indigo-400" />
                                            <h4 class="text-xs font-black text-slate-900 dark:text-white truncate">{{ $t('settings.company_logo_dark') }}</h4>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">{{ $t('settings.logo_dark_hint') }}</p>
                                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 cursor-pointer transition shadow-xs">
                                            <span>{{ $t('settings.choose_logo_dark') }}</span>
                                            <input type="file" accept="image/*" @change="handleLogoDarkChange" class="hidden">
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.company_name') }}</label>
                                <input
                                    v-model="form.company_name"
                                    type="text"
                                    required
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.company_subtitle') }}</label>
                                <input
                                    v-model="form.company_subtitle"
                                    type="text"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.company_phone') }}</label>
                                <input
                                    v-model="form.company_phone"
                                    type="text"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white font-mono placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.company_address') }}</label>
                                <input
                                    v-model="form.company_address"
                                    type="text"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="sm:col-span-2 space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.invoice_footer') }}</label>
                                <textarea
                                    v-model="form.invoice_footer_note"
                                    rows="2"
                                    class="w-full p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none shadow-inner"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Printing Toggles Matrix -->
                        <div class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                            <h3 class="text-xs font-black text-theme-primary">{{ $t('settings.print_options_title') }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-theme-primary cursor-pointer transition active:scale-98 min-h-[48px]">
                                    <input type="checkbox" v-model="form.show_print_logo" class="w-4 h-4 rounded accent-theme-primary focus:ring-0">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.show_print_logo') }}</span>
                                </label>

                                <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-theme-primary cursor-pointer transition active:scale-98 min-h-[48px]">
                                    <input type="checkbox" v-model="form.show_print_company_name" class="w-4 h-4 rounded accent-theme-primary focus:ring-0">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.show_print_name') }}</span>
                                </label>

                                <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-theme-primary cursor-pointer transition active:scale-98 min-h-[48px]">
                                    <input type="checkbox" v-model="form.thermal_show_customer_balance" class="w-4 h-4 rounded accent-theme-primary focus:ring-0">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.show_thermal_balance') }}</span>
                                </label>

                                <label class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-theme-primary cursor-pointer transition active:scale-98 min-h-[48px]">
                                    <input type="checkbox" v-model="form.print_show_qr" class="w-4 h-4 rounded accent-theme-primary focus:ring-0">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.show_qr') }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto h-12 px-8 rounded-2xl btn-primary-theme font-black text-xs sm:text-sm transition transform active:scale-95 cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2 shadow-theme-primary"
                            >
                                <Save class="w-4 h-4" />
                                <span>{{ form.processing ? $t('common.save') : $t('settings.save_branding_btn') }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab 2: Telegram Notifications -->
            <div v-if="currentTab === 'telegram'" class="space-y-6">
                <form @submit.prevent="saveSettings" class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-6 shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4 gap-3">
                            <div>
                                <h2 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                                    <Send class="w-4 h-4 text-theme-primary" />
                                    <span>{{ $t('settings.telegram_title') }}</span>
                                </h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $t('settings.telegram_sub') }}</p>
                            </div>

                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" v-model="form.telegram_notifications_enabled" class="w-4 h-4 rounded accent-theme-primary focus:ring-0">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $t('settings.enable_bot') }}</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.bot_token') }}</label>
                                <input
                                    v-model="form.telegram_bot_token"
                                    type="text"
                                    placeholder="123456789:ABCdef..."
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white font-mono placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('settings.chat_id') }}</label>
                                <input
                                    v-model="form.telegram_chat_id"
                                    type="text"
                                    placeholder="-100xxxxxxxxx أو 12345678"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white font-mono placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none shadow-inner"
                                >
                            </div>
                        </div>

                        <!-- Live Action Testing Triggers -->
                        <div class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                            <h3 class="text-xs font-black text-theme-primary">{{ $t('settings.telegram_actions_title') }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
                                <button
                                    @click="sendTelegramTest"
                                    type="button"
                                    class="min-h-[48px] p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-theme-primary text-slate-700 dark:text-slate-200 text-xs font-bold transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                                >
                                    <Send class="w-4 h-4 text-theme-primary" />
                                    <span>{{ $t('settings.send_test_msg') }}</span>
                                </button>

                                <button
                                    @click="sendDailySummary"
                                    type="button"
                                    class="min-h-[48px] p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-theme-primary text-slate-700 dark:text-slate-200 text-xs font-bold transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                                >
                                    <BarChart3 class="w-4 h-4 text-emerald-500" />
                                    <span>{{ $t('settings.send_daily_summary') }}</span>
                                </button>

                                <button
                                    @click="sendLowStock"
                                    type="button"
                                    class="min-h-[48px] p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-theme-primary text-slate-700 dark:text-slate-200 text-xs font-bold transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                                >
                                    <AlertTriangle class="w-4 h-4 text-rose-500" />
                                    <span>{{ $t('settings.send_low_stock') }}</span>
                                </button>

                                <button
                                    @click="sendOverdueShift"
                                    type="button"
                                    class="min-h-[48px] p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-theme-primary text-slate-700 dark:text-slate-200 text-xs font-bold transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                                >
                                    <RotateCcw class="w-4 h-4 text-amber-500" />
                                    <span>{{ $t('settings.send_overdue_shifts') }}</span>
                                </button>

                                <button
                                    @click="sendBackupTelegram"
                                    type="button"
                                    class="min-h-[48px] p-3.5 rounded-2xl bg-slate-50 hover:bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-theme-primary text-slate-700 dark:text-slate-200 text-xs font-bold transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer shadow-xs"
                                >
                                    <HardDrive class="w-4 h-4 text-indigo-500" />
                                    <span>{{ $t('settings.send_backup_telegram') }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto h-12 px-8 rounded-2xl btn-primary-theme font-black text-xs sm:text-sm transition transform active:scale-95 cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2 shadow-theme-primary"
                            >
                                <Save class="w-4 h-4" />
                                <span>{{ $t('settings.save_telegram_btn') }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab 3: Backups -->
            <div v-if="currentTab === 'backup'" class="space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <HardDrive class="w-4 h-4 text-theme-primary" />
                            <span>{{ $t('settings.backup_title') }}</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $t('settings.backup_sub') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 space-y-3 flex flex-col justify-between shadow-xs">
                            <div class="space-y-2">
                                <h3 class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-2">
                                    <HardDrive class="w-4 h-4 text-indigo-500" />
                                    <span>{{ $t('settings.direct_download_title') }}</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                    {{ $t('settings.direct_download_desc') }}
                                </p>
                            </div>

                            <a
                                href="/settings/backup/download"
                                class="w-full h-11 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md shadow-indigo-600/30 transition active:scale-95 cursor-pointer mt-2"
                            >
                                <HardDrive class="w-4 h-4" />
                                <span>{{ $t('settings.download_sql_btn') }}</span>
                            </a>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 space-y-3 flex flex-col justify-between shadow-xs">
                            <div class="space-y-2">
                                <h3 class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-2">
                                    <Send class="w-4 h-4 text-sky-500" />
                                    <span>{{ $t('settings.backup_to_telegram_title') }}</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                    {{ $t('settings.backup_to_telegram_desc') }}
                                </p>
                            </div>

                            <button
                                @click="sendBackupTelegram"
                                type="button"
                                class="w-full h-11 px-6 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-bold text-xs flex items-center justify-center gap-2 transition active:scale-95 cursor-pointer shadow-xs mt-2"
                            >
                                <Send class="w-4 h-4 text-sky-500" />
                                <span>{{ $t('settings.send_to_telegram_btn') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: System Performance & Maintenance -->
            <div v-if="currentTab === 'system'" class="space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-6 shadow-xs space-y-6">
                    <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h2 class="text-sm sm:text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <Cpu class="w-4 h-4 text-theme-primary" />
                            <span>{{ $t('settings.system_perf_title') }}</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $t('settings.system_perf_sub') }}</p>
                    </div>

                    <!-- 2x2 Bento Matrix for System Specs on Mobile -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 font-mono">
                        <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 sm:p-4 space-y-1 shadow-xs">
                            <span class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-tajawal">{{ $t('settings.php_version') }}</span>
                            <div class="text-xs sm:text-sm font-black text-theme-primary truncate">{{ system_info.php_version }}</div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 sm:p-4 space-y-1 shadow-xs">
                            <span class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-tajawal">{{ $t('settings.laravel_version') }}</span>
                            <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate">{{ system_info.laravel_version }}</div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 sm:p-4 space-y-1 shadow-xs">
                            <span class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-tajawal">{{ $t('settings.db_engine') }}</span>
                            <div class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 truncate">{{ system_info.db_driver }}</div>
                        </div>

                        <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 sm:p-4 space-y-1 shadow-xs">
                            <span class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-tajawal">{{ $t('settings.environment') }}</span>
                            <div class="text-xs sm:text-sm font-black text-indigo-600 dark:text-indigo-400 truncate">{{ system_info.environment }}</div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 w-full sm:w-auto">
                            <a
                                href="/pulse"
                                target="_blank"
                                class="flex-1 sm:flex-none h-11 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-purple-600 dark:text-purple-400 border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-95 shadow-xs"
                            >
                                <BarChart3 class="w-4 h-4" />
                                <span>{{ $t('settings.pulse_monitoring') }}</span>
                            </a>

                            <a
                                href="/telescope"
                                target="_blank"
                                class="flex-1 sm:flex-none h-11 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-teal-600 dark:text-teal-400 border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-95 shadow-xs"
                            >
                                <Cpu class="w-4 h-4" />
                                <span>{{ $t('settings.telescope') }}</span>
                            </a>
                        </div>

                        <button
                            @click="clearCache"
                            type="button"
                            class="w-full sm:w-auto h-11 px-6 rounded-2xl btn-primary-theme font-black text-xs transition transform active:scale-95 cursor-pointer flex items-center justify-center gap-2 shadow-theme-primary"
                        >
                            <RefreshCw class="w-4 h-4" />
                            <span>{{ $t('settings.clear_cache_now') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>