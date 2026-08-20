<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { can, hasRole } from '@/Utils/permissions';

const props = defineProps({
    isOpen: Boolean,
    isDark: Boolean,
});

const emit = defineEmits(['close', 'toggleTheme', 'openBranchSwitcher', 'openUpdateModal']);

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const store = computed(() => page.props.current_store || {});

const closeMenu = () => emit('close');
const toggleTheme = () => emit('toggleTheme');
const openBranchModal = () => {
    emit('close');
    emit('openBranchSwitcher');
};
const triggerUpdateModal = () => {
    emit('close');
    emit('openUpdateModal');
};

const handleLogout = () => {
    emit('close');
    router.post('/logout');
};
</script>

<template>
    <!-- Off-Canvas Drawer Backdrop -->
    <div
        v-if="isOpen"
        @click="closeMenu"
        class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs transition-opacity duration-300 animate-fade-in"
    ></div>

    <!-- Drawer Content -->
    <aside
        class="fixed top-0 bottom-0 start-0 z-50 w-72 max-w-[85vw] bg-white dark:bg-slate-900 shadow-2xl flex flex-col transition-transform duration-300 ease-out border-e border-slate-200 dark:border-slate-800"
        :class="isOpen ? 'translate-x-0' : '-translate-x-full rtl:translate-x-full'"
    >
        <!-- Header: User Profile & Current Branch -->
        <div class="p-4 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-white flex flex-col gap-3 relative overflow-hidden">
            <!-- Close Button -->
            <button
                @click="closeMenu"
                type="button"
                class="absolute top-3 end-3 p-1.5 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white transition"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- User Info Avatar -->
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-lg font-black shadow-lg shadow-emerald-600/30 ring-2 ring-white/20">
                    {{ user.name ? user.name.charAt(0) : 'U' }}
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="font-black text-sm text-white truncate font-tajawal">{{ user.name || 'مستخدم النظام' }}</h3>
                    <p class="text-xs text-emerald-400 font-mono font-bold truncate">{{ user.phone || user.email || '01000000000' }}</p>
                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-md bg-white/10 text-[10px] font-bold text-slate-300">
                        {{ user.role_name || (hasRole('admin') ? 'مدير النظام' : 'كاشير') }}
                    </span>
                </div>
            </div>

            <!-- Branch Switcher Trigger Button -->
            <button
                @click="openBranchModal"
                type="button"
                class="w-full p-2 rounded-xl bg-white/10 hover:bg-white/20 transition flex items-center justify-between text-[11px] font-bold text-start"
            >
                <div class="flex items-center gap-2 truncate">
                    <span class="text-sm">🏬</span>
                    <span class="truncate">{{ store?.name || 'الفرع الرئيسي' }}</span>
                </div>
                <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/80 font-bold shrink-0">تبديل</span>
            </button>

            <!-- Update Available Trigger Card (if update exists) -->
            <button
                v-if="page.props.appUpdate?.has_update"
                @click="triggerUpdateModal"
                type="button"
                class="w-full p-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 transition flex items-center justify-between text-[11px] font-black shadow-md touch-active"
            >
                <div class="flex items-center gap-1.5 truncate">
                    <span>🚀</span>
                    <span>تحديث متوفر (v{{ page.props.appUpdate?.latest_version }})</span>
                </div>
                <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-950 text-white font-bold shrink-0">تثبيت ‹</span>
            </button>
        </div>

        <!-- Navigation Links List (Filtered by Roles & Permissions) -->
        <div class="flex-1 overflow-y-auto p-3 space-y-4 text-xs font-bold text-slate-700 dark:text-slate-200">
            
            <!-- Section 1: Cashier & Sales -->
            <div class="space-y-1">
                <div class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 px-3 pb-1">المبيعات ونقاط البيع</div>
                <Link
                    v-if="can('pos.access')"
                    href="/pos"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/pos') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">⚡</span>
                        <span>الكاشير السريع (POS)</span>
                    </div>
                    <span class="text-xs px-1.5 py-0.5 rounded-md bg-emerald-500 text-white font-bold">سريع</span>
                </Link>

                <Link
                    v-if="can('daily_journal.view')"
                    href="/shifts"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/shifts') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">🔒</span>
                        <span>الورديات والخزينة</span>
                    </div>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold font-mono">Z-Report</span>
                </Link>

                <Link
                    v-if="can('invoices.view')"
                    href="/invoices"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/invoices') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">🧾</span>
                        <span>فواتير المبيعات</span>
                    </div>
                    <span class="text-slate-400 text-xs">‹</span>
                </Link>

                <Link
                    v-if="can('customers.manage')"
                    href="/customers"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/customers') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">👥</span>
                        <span>دليل العملاء والتحصيل</span>
                    </div>
                    <span class="text-slate-400 text-xs">‹</span>
                </Link>

                <Link
                    v-if="can('returns.manage')"
                    href="/returns"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/returns') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">↩️</span>
                        <span>المرتجعات والتسويات</span>
                    </div>
                    <span class="text-slate-400 text-xs">‹</span>
                </Link>
            </div>

            <!-- Section 2: Inventory & Coffee Production -->
            <div class="space-y-1">
                <div class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 px-3 pb-1">المخزون والإنتاج</div>
                <Link
                    v-if="can('items.manage')"
                    href="/items"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/items') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">📦</span>
                        <span>أصناف ومخزون البن</span>
                    </div>
                    <span class="text-slate-400 text-xs">‹</span>
                </Link>

                <Link
                    v-if="can('blender.access')"
                    href="/blender"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/blender') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">☕</span>
                        <span>استوديو توليف البن</span>
                    </div>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-amber-500/20 text-amber-600 dark:text-amber-400 font-bold">توليف</span>
                </Link>

                <Link
                    v-if="can('transfers.manage')"
                    href="/transfers"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/transfers') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">🚚</span>
                        <span>التحويلات بين الفروع</span>
                    </div>
                    <span class="text-slate-400 text-xs">‹</span>
                </Link>
            </div>

            <!-- Section 3: Purchases & Suppliers -->
            <div class="space-y-1">
                <div class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 px-3 pb-1">المشتريات والموردين</div>
                <Link
                    v-if="can('purchases.manage')"
                    href="/purchases"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/purchases') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">🚛</span>
                        <span>فواتير المشتريات</span>
                    </div>
                    <span class="text-slate-400 text-xs">‹</span>
                </Link>

                <Link
                    v-if="can('suppliers.manage')"
                    href="/suppliers"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/suppliers') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">🤝</span>
                        <span>دليل الموردين</span>
                    </div>
                    <span class="text-slate-400 text-xs">‹</span>
                </Link>
            </div>

            <!-- Section 4: Finance & Analytics -->
            <div class="space-y-1">
                <div class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 px-3 pb-1">المالية والتقارير</div>
                <Link
                    v-if="can('expenses.manage')"
                    href="/expenses"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/expenses') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">💸</span>
                        <span>المصروفات والنثريات</span>
                    </div>
                    <span class="text-slate-400 text-xs">‹</span>
                </Link>

                <Link
                    v-if="can('reports.view')"
                    href="/reports"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/reports') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">📊</span>
                        <span>التقارير والأرباح</span>
                    </div>
                    <span class="text-slate-400 text-xs">‹</span>
                </Link>

                <Link
                    v-if="hasRole('admin')"
                    href="/audit-logs"
                    @click="closeMenu"
                    class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                    :class="page.url.startsWith('/audit-logs') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">🛡️</span>
                        <span>سجل الرقابة والعمليات</span>
                    </div>
                    <span class="text-slate-400 text-xs">‹</span>
                </Link>
            </div>
        </div>

        <!-- Footer: Theme Toggle & Logout -->
        <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2">
            <!-- Theme Toggle -->
            <button
                @click="toggleTheme"
                type="button"
                class="flex-1 py-2 px-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60 transition"
            >
                <span>{{ isDark ? '☀️' : '🌙' }}</span>
                <span>{{ isDark ? 'نهاري' : 'ليلي' }}</span>
            </button>

            <!-- Logout -->
            <button
                @click="handleLogout"
                type="button"
                class="py-2 px-3 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center gap-1.5 text-xs font-black transition"
            >
                <span>🚪</span>
                <span>خروج</span>
            </button>
        </div>
    </aside>
</template>
