<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import FeatureGate from '@/Components/FeatureGate.vue';
import { trans } from '@/helpers/trans';
import { useTheme } from '@/Composables/useTheme';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const tenant = computed(() => page.props.tenant);
const activeStore = computed(() => page.props.activeStore);
const stores = computed(() => page.props.stores || []);
const activeShift = computed(() => page.props.activeShift);
const isAdmin = computed(() => user.value.roles?.includes('admin'));

// Sidebar collapse & Mobile drawer state
const isSidebarOpen = ref(false);
const isSidebarCollapsed = ref(false);

// Header user menu, Notification dropdown & Store modal state
const showUserMenu = ref(false);
const showNotifications = ref(false);
const showStoreModal = ref(false);

const notifications = computed(() => page.props.system_notifications || []);

// Theme Composable
const { currentTheme, toggleTheme, initTheme } = useTheme(user.value.theme_preference || 'dark');

// Live Arabic Clock & Date
const currentTime = ref('');
const currentDate = ref('');
let timerInterval = null;

const updateClock = () => {
    const now = new Date();
    try {
        currentTime.value = now.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        currentDate.value = now.toLocaleDateString('ar-EG', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    } catch (e) {
        currentTime.value = now.toLocaleTimeString();
        currentDate.value = now.toLocaleDateString();
    }
};

// Global Hotkeys Listener (F2 for POS, Escape for modals)
const handleKeydown = (e) => {
    if (e.key === 'F2') {
        e.preventDefault();
        router.visit('/pos');
    }
    if (e.key === 'Escape') {
        showStoreModal.value = false;
        showUserMenu.value = false;
        showNotifications.value = false;
        isSidebarOpen.value = false;
    }
};

const layoutProps = defineProps({
    defaultCollapsed: { type: Boolean, default: false },
});

onMounted(() => {
    initTheme();
    const isPos = window.location.pathname.startsWith('/pos') || window.location.pathname.startsWith('/invoices/create') || layoutProps.defaultCollapsed;
    if (isPos) {
        isSidebarCollapsed.value = true;
    } else {
        try {
            const savedCollapsed = localStorage.getItem('sidebar_collapsed');
            if (savedCollapsed !== null) {
                isSidebarCollapsed.value = savedCollapsed === 'true';
            }
        } catch (e) {}
    }

    updateClock();
    timerInterval = setInterval(updateClock, 1000);
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
    window.removeEventListener('keydown', handleKeydown);
});

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    try {
        localStorage.setItem('sidebar_collapsed', isSidebarCollapsed.value ? 'true' : 'false');
    } catch (e) {}
};

const switchStore = (storeId) => {
    router.post('/store/switch', { store_id: storeId }, {
        preserveScroll: true,
        onSuccess: () => {
            showStoreModal.value = false;
        }
    });
};

const logout = () => {
    router.post('/logout');
};

// Navigation Groups & Items
const navigationGroups = computed(() => [
    {
        title: '',
        items: [
            { name: trans('nav.dashboard'), href: '/', icon: '📊', active: page.url === '/' || page.url === '', feature: null },
        ]
    },
    {
        title: trans('nav.group_sales'),
        items: [
            { name: trans('nav.invoices_log'), href: '/invoices', icon: '🧾', active: page.url === '/invoices' || page.url.startsWith('/invoices/'), feature: 'invoices.create' },
            { name: trans('nav.daily_journal'), href: '/daily-journal', icon: '💰', active: page.url.startsWith('/daily-journal') || page.url.startsWith('/shifts'), feature: 'shifts.manage' },
            { name: trans('nav.customers'), href: '/customers', icon: '👥', active: page.url.startsWith('/customers'), feature: null },
        ]
    },
    {
        title: trans('nav.group_inventory'),
        items: [
            { name: trans('nav.items_catalog'), href: '/items', icon: '📦', active: page.url.startsWith('/items'), feature: 'items.manage' },
            { name: trans('nav.store_stocks'), href: '/store-stocks', icon: '📋', active: page.url.startsWith('/store-stocks'), feature: 'items.view' },
            { name: trans('nav.stock_transfers'), href: '/stock-transfers', icon: '🚚', active: page.url.startsWith('/stock-transfers'), feature: 'transfers.manage' },
            { name: trans('nav.stores'), href: '/stores', icon: '🏬', active: page.url.startsWith('/stores'), feature: null },
            { name: trans('nav.purchases'), href: '/purchases', icon: '🚛', active: page.url.startsWith('/purchases'), feature: 'purchases.manage' },
            { name: trans('nav.smart_reorder'), href: '/purchases/smart-reorder', icon: '🤖', active: page.url.startsWith('/purchases/smart-reorder'), feature: 'purchases.reorder' },
            { name: trans('nav.suppliers'), href: '/suppliers', icon: '🏭', active: page.url.startsWith('/suppliers'), feature: null },
        ]
    },
    {
        title: trans('nav.group_financials'),
        items: [
            { name: trans('nav.expenses'), href: '/expenses', icon: '💸', active: page.url.startsWith('/expenses'), feature: 'expenses.manage' },
            { name: trans('nav.returns_adjustments'), href: '/returns', icon: '↩️', active: page.url.startsWith('/returns'), feature: 'returns.manage' },
            { name: trans('nav.reports'), href: '/reports', icon: '📈', active: page.url.startsWith('/reports'), feature: 'reports.advanced' },
            { name: trans('nav.coffee_blender'), href: '/coffee-blender', icon: '☕', active: page.url.startsWith('/coffee-blender'), feature: 'blender.access' },
        ]
    },
    {
        title: trans('nav.group_management'),
        items: [
            { name: trans('nav.users'), href: '/users', icon: '👤', active: page.url.startsWith('/users'), feature: 'roles.manage' },
            { name: trans('nav.roles'), href: '/roles', icon: '🛡️', active: page.url.startsWith('/roles'), feature: 'roles.manage' },
            { name: trans('nav.audit_logs'), href: '/activity-logs', icon: '📋', active: page.url.startsWith('/activity-logs'), feature: 'audit.logs' },
            { name: trans('nav.trash'), href: '/trash', icon: '🗑️', active: page.url.startsWith('/trash'), feature: 'trash.access' },
            { name: trans('nav.settings'), href: '/settings', icon: '⚙️', active: page.url.startsWith('/settings'), feature: null },
        ]
    }
]);

const getUserRoleLabel = computed(() => {
    if (user.value.roles?.includes('admin')) return trans('nav.admin_role');
    if (user.value.roles?.includes('cashier')) return trans('nav.cashier_role');
    if (user.value.roles?.includes('accountant')) return trans('nav.accountant_role');
    if (user.value.roles?.includes('storekeeper')) return trans('nav.storekeeper_role');
    return trans('nav.user_role');
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col font-sans selection:bg-amber-500 selection:text-white transition-colors duration-200" dir="rtl">
        <!-- Top App Bar -->
        <header class="h-16 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-40 shrink-0 shadow-xs">
            <!-- Right Section (in RTL: Start) -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Mobile Hamburger -->
                <button
                    @click="isSidebarOpen = true"
                    type="button"
                    class="lg:hidden p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                >
                    <span class="text-xl">☰</span>
                </button>

                <!-- Desktop Collapse Button -->
                <button
                    @click="toggleSidebar()"
                    type="button"
                    class="hidden lg:flex p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:text-amber-500 dark:hover:text-amber-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                    :title="$t('nav.toggle_sidebar')"
                >
                    <svg class="w-5 h-5 transition-transform duration-300" :class="isSidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>

                <!-- Live Arabic Date & Clock (Clean compact chip) -->
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-xs font-bold font-tajawal text-slate-700 dark:text-slate-300">
                    <span class="text-slate-500 dark:text-slate-400 hidden xl:inline">{{ currentDate }}</span>
                    <span class="text-slate-300 dark:text-slate-600 hidden xl:inline">|</span>
                    <span class="text-amber-600 dark:text-amber-400 font-mono font-bold">{{ currentTime }}</span>
                </div>
            </div>

            <!-- Left Section (in RTL: End Actions) -->
            <div class="flex items-center gap-2 sm:gap-2.5">
                <!-- Single Interactive Store Switcher Button -->
                <button
                    @click="showStoreModal = true"
                    type="button"
                    class="h-9 px-3 rounded-xl bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800/90 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2 transition cursor-pointer font-tajawal shadow-xs"
                    :title="$t('nav.switch_store') || 'تبديل الفرع'"
                >
                    <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse shrink-0"></span>
                    <span class="text-sm">🏬</span>
                    <span class="max-w-[130px] sm:max-w-[170px] truncate text-start">{{ activeStore?.name || $t('common.main_store_default') }}</span>
                    <span class="text-[9px] text-slate-400 shrink-0">▼</span>
                </button>

                <!-- Shift Status Indicator -->
                <Link
                    href="/daily-journal"
                    class="h-9 px-2.5 sm:px-3 rounded-xl border text-xs font-bold flex items-center gap-1.5 transition font-tajawal shadow-xs shrink-0"
                    :class="activeShift ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20' : 'bg-rose-500/10 border-rose-500/30 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20'"
                    :title="activeShift ? 'الوردية مفتوحة ونشطة' : 'لا توجد وردية مفتوحة حالياً'"
                >
                    <span class="w-2 h-2 rounded-full shrink-0" :class="activeShift ? 'bg-emerald-500 dark:bg-emerald-400 animate-pulse' : 'bg-rose-500 dark:bg-rose-400'"></span>
                    <span class="hidden md:inline">
                        {{ activeShift ? $t('nav.active_shift') : $t('nav.closed_shift') }}
                    </span>
                </Link>

                <!-- Quick POS Fast Action Button -->
                <FeatureGate feature="pos.access">
                    <Link
                        href="/pos"
                        class="h-9 px-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-md shadow-emerald-500/20 transition transform active:scale-95 cursor-pointer font-tajawal shrink-0"
                    >
                        <span>⚡</span>
                        <span class="hidden sm:inline">{{ $t('nav.pos_fast') }}</span>
                        <span class="px-1.5 py-0.5 rounded bg-slate-950/25 text-white text-[10px] font-mono">F2</span>
                    </Link>
                </FeatureGate>

                <!-- Notification Center Dropdown -->
                <div class="relative" @click.stop>
                    <button
                        @click="showNotifications = !showNotifications; showUserMenu = false"
                        type="button"
                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-transparent flex items-center justify-center transition relative cursor-pointer"
                        :title="$t('nav.notifications_title')"
                    >
                        <span class="text-sm">🔔</span>
                        <span
                            v-if="notifications.length > 0"
                            class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-500 text-white font-black text-[9px] flex items-center justify-center animate-pulse"
                        >
                            {{ notifications.length }}
                        </span>
                    </button>

                    <!-- Notifications Dropdown Panel -->
                    <div
                        v-if="showNotifications"
                        class="absolute left-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl p-3 z-50 space-y-2 font-tajawal text-slate-900 dark:text-slate-100"
                    >
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800 px-1">
                            <span class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>🔔</span>
                                <span>{{ $t('nav.live_notifications_center') }}</span>
                            </span>
                            <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold">
                                {{ notifications.length }} {{ $t('nav.notifications_count') }}
                            </span>
                        </div>

                        <div class="space-y-2 max-h-72 overflow-y-auto">
                            <div
                                v-for="(n, nIdx) in notifications"
                                :key="nIdx"
                                class="p-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800/80 space-y-1 hover:border-slate-300 dark:hover:border-slate-700 transition"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">{{ n.icon }}</span>
                                    <span class="text-xs font-black text-slate-900 dark:text-white">{{ n.title }}</span>
                                </div>
                                <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-snug">{{ n.description }}</p>
                                <div class="pt-1 flex justify-end">
                                    <Link
                                        :href="n.link"
                                        @click="showNotifications = false"
                                        class="text-[10px] font-bold text-amber-600 dark:text-amber-400 hover:underline transition"
                                    >
                                        {{ n.link_label }} ←
                                    </Link>
                                </div>
                            </div>

                            <div v-if="notifications.length === 0" class="py-6 text-center text-xs text-slate-400 font-bold">
                                {{ $t('nav.no_urgent_notifications') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Theme Toggle Button -->
                <button
                    @click="toggleTheme"
                    type="button"
                    class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-transparent flex items-center justify-center transition cursor-pointer"
                    :title="currentTheme === 'dark' ? $t('nav.switch_to_light') : $t('nav.switch_to_dark')"
                >
                    <span>{{ currentTheme === 'dark' ? '☀️' : '🌙' }}</span>
                </button>

                <!-- User Profile & Dropdown -->
                <div class="relative" @click.stop>
                    <button
                        @click="showUserMenu = !showUserMenu"
                        type="button"
                        class="flex items-center gap-2 p-1.5 pr-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition cursor-pointer text-slate-800 dark:text-slate-200"
                    >
                        <div class="w-7 h-7 rounded-lg bg-amber-500/20 border border-amber-500/40 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center">
                            {{ user.name ? user.name.charAt(0) : 'U' }}
                        </div>
                        <span class="text-xs font-bold hidden lg:inline max-w-[120px] truncate">
                            {{ user.name }}
                        </span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                    </button>

                    <!-- User Dropdown Menu -->
                    <div
                        v-if="showUserMenu"
                        class="absolute left-0 mt-2 w-56 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl p-2 z-50 space-y-1 font-tajawal text-slate-800 dark:text-slate-200"
                    >
                        <div class="p-2 border-b border-slate-200 dark:border-slate-800">
                            <p class="text-xs font-black text-slate-900 dark:text-white truncate">{{ user.name }}</p>
                            <p class="text-[11px] text-amber-600 dark:text-amber-400 font-mono mt-0.5">{{ user.phone }}</p>
                            <span class="mt-1.5 inline-block px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                {{ getUserRoleLabel }}
                            </span>
                        </div>

                        <Link
                            href="/profile"
                            @click="showUserMenu = false"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition"
                        >
                            <span>👤</span>
                            <span>{{ $t('nav.profile') }}</span>
                        </Link>

                        <a
                            v-if="isAdmin"
                            href="/admin/super"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-950/40 border border-purple-200 dark:border-purple-800/40 transition font-bold"
                        >
                            <span>👑</span>
                            <span>{{ $t('nav.super_admin') }}</span>
                        </a>

                        <button
                            @click="logout"
                            type="button"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition font-bold cursor-pointer"
                        >
                            <span>🚪</span>
                            <span>{{ $t('nav.logout') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar Navigation -->
            <aside
                id="main-sidebar"
                class="fixed inset-y-0 right-0 z-50 bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 flex flex-col shadow-xl lg:shadow-none select-none shrink-0 transition-all duration-300 lg:static"
                :class="[
                    isSidebarOpen ? 'translate-x-0 w-72' : 'translate-x-full lg:translate-x-0',
                    isSidebarCollapsed ? 'lg:w-20' : 'lg:w-72'
                ]"
            >
                <!-- Brand Header -->
                <div class="h-16 px-3.5 flex items-center justify-between border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/60 overflow-hidden">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <Link href="/" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 p-1 flex items-center justify-center shadow-xs border border-slate-200 dark:border-slate-700 shrink-0">
                            <img src="/logo.png" alt="Logo" class="w-full h-full object-contain">
                        </Link>
                        <div v-if="!isSidebarCollapsed" class="truncate min-w-0">
                            <h1 class="font-black text-sm tracking-tight text-slate-900 dark:text-white font-tajawal truncate">
                                {{ tenant?.name || 'سرور كوفي' }}
                            </h1>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-bold truncate">{{ $t('nav.cloud_erp_subtitle') }}</p>
                        </div>
                    </div>

                    <button @click="isSidebarOpen = false" class="lg:hidden p-2 text-slate-400 hover:text-slate-700 dark:hover:text-white">✕</button>
                </div>

                <!-- Primary New Sale Action Button (Amber Gradient) -->
                <FeatureGate feature="pos.access">
                    <div class="p-2.5 border-b border-slate-200 dark:border-slate-800/80">
                        <Link
                            href="/pos"
                            class="w-full flex items-center justify-center gap-2 py-3 px-3.5 bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-400 text-white font-bold rounded-2xl shadow-lg shadow-amber-600/30 transition-all duration-200 active:scale-95 font-tajawal cursor-pointer group"
                            :title="$t('nav.new_sale_invoice_btn')"
                        >
                            <span class="text-lg font-black shrink-0 transition-transform group-hover:rotate-90 duration-300">+</span>
                            <span v-if="!isSidebarCollapsed" class="truncate text-xs font-bold">{{ $t('nav.new_sale_invoice_btn') }}</span>
                        </Link>
                    </div>
                </FeatureGate>

                <!-- Nav Links (Scrollable) -->
                <nav class="flex-1 px-2.5 py-3 space-y-4 overflow-y-auto font-tajawal">
                    <div v-for="(group, gIdx) in navigationGroups" :key="gIdx" class="space-y-1">
                        <!-- Group Header -->
                        <div
                            v-if="group.title && !isSidebarCollapsed"
                            class="pt-2 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 truncate"
                        >
                            {{ group.title }}
                        </div>
                        <div v-if="group.title && isSidebarCollapsed" class="my-1.5 border-t border-slate-200 dark:border-slate-800 mx-1"></div>

                        <!-- Group Items -->
                        <div class="space-y-1">
                            <template v-for="item in group.items" :key="item.href">
                                <FeatureGate :feature="item.feature">
                                    <Link
                                        :href="item.href"
                                        @click="isSidebarOpen = false"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 group"
                                        :class="[
                                            item.active
                                                ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 shadow-xs font-black'
                                                : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white',
                                            isSidebarCollapsed ? 'justify-center px-2' : ''
                                        ]"
                                        :title="item.name"
                                    >
                                        <span class="text-base shrink-0 group-hover:scale-110 transition-transform">{{ item.icon }}</span>
                                        <span v-if="!isSidebarCollapsed" class="truncate flex-1">{{ item.name }}</span>
                                    </Link>
                                </FeatureGate>
                            </template>
                        </div>
                    </div>
                </nav>

                <!-- Sidebar Footer (Super Admin Platform Hub Button) -->
                <div v-if="isAdmin" class="p-2.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/70">
                    <a
                        href="/admin/super"
                        class="w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-purple-100 hover:bg-purple-200 dark:bg-purple-950/60 dark:hover:bg-purple-900 border border-purple-200 dark:border-purple-800/80 text-purple-700 dark:text-purple-300 text-xs font-bold transition font-tajawal shadow-xs"
                        :class="isSidebarCollapsed ? 'justify-center px-2' : ''"
                        :title="$t('nav.super_admin')"
                    >
                        <span>👑</span>
                        <span v-if="!isSidebarCollapsed" class="truncate">{{ $t('nav.super_admin') }}</span>
                    </a>
                </div>
            </aside>

            <!-- Backdrop overlay for mobile drawer -->
            <div
                v-if="isSidebarOpen"
                @click="isSidebarOpen = false"
                class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden"
            ></div>

            <!-- Main Page Content Area -->
            <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-950 flex flex-col p-4 sm:p-6 lg:p-8 space-y-6">
                <!-- Flash Alert Banners -->
                <div v-if="$page.props.flash?.success" class="p-3.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-bold flex items-center justify-between font-tajawal shadow-sm">
                    <span>✓ {{ $page.props.flash.success }}</span>
                </div>
                <div v-if="$page.props.flash?.error" class="p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-600 dark:text-rose-400 text-xs font-bold flex items-center justify-between font-tajawal shadow-sm">
                    <span>⚠️ {{ $page.props.flash.error }}</span>
                </div>

                <slot />
            </main>
        </div>

        <!-- Store / Van Switcher Modal -->
        <div
            v-if="showStoreModal"
            @click="showStoreModal = false"
            class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4"
        >
            <div @click.stop class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-2xl space-y-3 font-tajawal text-slate-900 dark:text-white">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                    <h3 class="font-black text-sm">{{ $t('nav.select_store_modal_title') }}</h3>
                    <button @click="showStoreModal = false" class="w-7 h-7 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs dark:hover:text-white transition">✕</button>
                </div>

                <div class="space-y-2 max-h-64 overflow-y-auto pt-1">
                    <div
                        v-for="store in stores"
                        :key="store.id"
                        @click="switchStore(store.id)"
                        class="p-3 rounded-2xl border flex items-center justify-between cursor-pointer transition"
                        :class="activeStore?.id === store.id ? 'bg-amber-500/15 border-amber-500/40 text-amber-600 dark:text-amber-400 font-black' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">{{ store.type === 'van' ? '🚐' : '🏬' }}</span>
                            <div>
                                <p class="text-xs font-bold">{{ store.name }}</p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-sans">{{ store.type === 'van' ? $t('nav.van_store') : $t('nav.branch_store') }}</p>
                            </div>
                        </div>
                        <span v-if="activeStore?.id === store.id" class="text-sm font-bold text-amber-600 dark:text-amber-400">✓</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
