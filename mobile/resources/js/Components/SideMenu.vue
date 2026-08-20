<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { can, hasRole } from '@/Utils/permissions';

const props = defineProps({
    isOpen: Boolean,
    isDark: Boolean,
});

const emit = defineEmits(['close', 'toggleTheme', 'openBranchSwitcher']);

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const store = computed(() => page.props.auth?.store || {});

const userRoles = computed(() => {
    const r = user.value.roles;
    if (Array.isArray(r) && r.length > 0) {
        return r.map(role => {
            if (role === 'admin') return 'مسؤول النظام (Admin)';
            if (role === 'cashier') return 'كاشير (Cashier)';
            if (role === 'storekeeper') return 'أمين مخزن (Storekeeper)';
            if (role === 'accountant') return 'محاسب مالي (Accountant)';
            return role;
        }).join(' • ');
    }
    return 'مستخدم النظام';
});

const userRoleBadge = computed(() => {
    const r = user.value.roles;
    return (Array.isArray(r) && r.length > 0) ? r[0] : 'user';
});

const closeMenu = () => {
    emit('close');
};

const logout = () => {
    closeMenu();
    router.post('/logout');
};

const openBranchModal = () => {
    closeMenu();
    emit('openBranchSwitcher');
};

const triggerUpdateModal = () => {
    closeMenu();
    if (typeof haptic !== 'undefined') haptic.medium();
    if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('open-app-update-modal'));
    }
};
</script>

<template>
    <div>
        <!-- Backdrop Overlay -->
        <div
            v-if="isOpen"
            @click="closeMenu"
            class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs transition-opacity duration-300 select-none animate-in fade-in"
        ></div>

        <!-- Offcanvas Drawer Menu -->
        <aside
            class="fixed top-0 bottom-0 start-0 z-50 w-72 max-w-[80vw] bg-white dark:bg-slate-900 shadow-2xl transition-transform duration-300 ease-in-out select-none flex flex-col justify-between border-e border-slate-200 dark:border-slate-800"
            :class="isOpen ? 'translate-x-0' : '-translate-x-full rtl:translate-x-full'"
        >
            <!-- Drawer Header (User & Active Branch Profile) -->
            <div class="p-4 bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-900 text-white space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl border border-white/20">
                            🏢
                        </div>
                        <div>
                            <h3 class="font-black text-sm leading-tight">{{ user?.name || 'مستخدم النظام' }}</h3>
                            <div class="flex items-center gap-1 mt-0.5">
                                <span class="px-1.5 py-0.2 rounded-md bg-amber-400 text-slate-950 text-[9px] font-black uppercase">
                                    {{ userRoleBadge }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <button
                        @click="closeMenu"
                        type="button"
                        class="w-7 h-7 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-xs font-bold transition touch-active"
                    >
                        ✕
                    </button>
                </div>

                <!-- Active Branch Card (Clickable to switch) -->
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
                    <div class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 px-2 tracking-wider">
                        الكاشير والمبيعات
                    </div>

                    <Link
                        v-if="can('pos.access')"
                        href="/pos"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/pos') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">⚡</span>
                            <span>كاشير ونقاط البيع (POS)</span>
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
                            <span class="text-base">🔐</span>
                            <span>ورديات الكاشير ودرج النقدية</span>
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
                            <span>فواتير المبيعات ومشاركة واتساب</span>
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
                            <span>دليل العملاء وكشوف الحساب</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>
                </div>

                <!-- Section 2: Inventory & Items -->
                <div v-if="can('items.view')" class="space-y-1">
                    <div class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 px-2 tracking-wider">
                        المخزون والأصناف
                    </div>

                    <Link
                        v-if="can('items.view')"
                        href="/items"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/items') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">📦</span>
                            <span>دليل الأصناف والمخزون</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>

                    <Link
                        v-if="can('items.view') || hasRole('admin') || hasRole('storekeeper')"
                        href="/transfers"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/transfers') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">🚚</span>
                            <span>التحويل المخزني بين الفروع</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>
                </div>

                <!-- Section 3: Suppliers & Purchases & Returns -->
                <div v-if="can('purchases.view') || can('suppliers.manage') || can('returns.manage')" class="space-y-1">
                    <div class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 px-2 tracking-wider">
                        المشتريات والموردين والمرتجعات
                    </div>

                    <Link
                        v-if="can('purchases.view')"
                        href="/purchases"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/purchases') ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">📦</span>
                            <span>فواتير المشتريات والتوريد</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>

                    <Link
                        v-if="can('returns.manage') || hasRole('admin') || hasRole('cashier')"
                        href="/returns"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/returns') ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">🔄</span>
                            <span>مرتجعات المبيعات والمشتريات</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>

                    <Link
                        v-if="can('suppliers.manage')"
                        href="/suppliers"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/suppliers') ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">🏭</span>
                            <span>دليل الموردين وكشوف الحساب</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>
                </div>

                <!-- Section 4: Treasury, Vouchers & Reports -->
                <div v-if="can('daily_journal.view') || can('expenses.manage') || can('reports.view')" class="space-y-1">
                    <div class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 px-2 tracking-wider">
                        المالية والخزينة
                    </div>

                    <Link
                        v-if="can('daily_journal.view')"
                        href="/payments"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/payments') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">💰</span>
                            <span>سندات القبض والصرف</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>

                    <Link
                        v-if="can('expenses.manage')"
                        href="/expenses"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/expenses') ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">💸</span>
                            <span>المصروفات وتكلفة التشغيل</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>

                    <Link
                        v-if="can('daily_journal.view')"
                        href="/treasury"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/treasury') ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">🏦</span>
                            <span>حركة الخزينة والصندوق</span>
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
                            <span class="text-base">📈</span>
                            <span>تقارير الأرباح وتحليلات المبيعات</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>
                </div>

                <!-- Section 5: Admin & System Settings -->
                <div v-if="hasRole('admin') || can('roles.manage')" class="space-y-1">
                    <div class="text-[10px] font-extrabold uppercase text-slate-400 dark:text-slate-500 px-2 tracking-wider">
                        الإدارة والتحكم
                    </div>

                    <Link
                        href="/settings"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/settings') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">⚙️</span>
                            <span>إعدادات النظام والطباعة</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>

                    <Link
                        href="/audit-logs"
                        @click="closeMenu"
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition"
                        :class="page.url.startsWith('/audit-logs') ? 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-base">🕵️‍♂️</span>
                            <span>سجل الرقابة وتدقيق العمليات</span>
                        </div>
                        <span class="text-slate-400 text-xs">‹</span>
                    </Link>
                </div>

            </div>

            <!-- Drawer Footer (Controls & Logout) -->
            <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border-t border-slate-200 dark:border-slate-800 space-y-2">
                <button
                    @click="emit('toggleTheme')"
                    type="button"
                    class="w-full h-10 px-3 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs font-bold transition"
                >
                    <span class="flex items-center gap-2">
                        <span v-if="isDark">🌙</span>
                        <span v-else>☀️</span>
                        <span>{{ isDark ? 'المظهر الليلي (Dark Slate)' : 'المظهر الفاتح (Light Shell)' }}</span>
                    </span>
                    <span class="text-[10px] text-slate-400">تغيير</span>
                </button>

                <button
                    @click="logout"
                    type="button"
                    class="w-full h-10 px-3 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center gap-2 text-xs font-black transition"
                >
                    <span>🚪</span>
                    <span>تسجيل الخروج من الحساب</span>
                </button>
            </div>
        </aside>
    </div>
</template>
