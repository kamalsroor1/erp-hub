<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    metrics: { type: Object, default: () => ({}) },
    recent_invoices: { type: Array, default: () => [] },
    low_stock_items: { type: Array, default: () => [] },
    top_selling_items: { type: Array, default: () => [] },
    active_shift: { type: Object, default: null },
    active_store: { type: Object, default: null },
});

const page = usePage();
const tenant = computed(() => page.props.tenant);
const activeStore = computed(() => props.active_store || page.props.activeStore);
const activeShift = computed(() => props.active_shift || page.props.activeShift);
const summary = computed(() => props.metrics || {});

const { formatMoney } = useMoney();

const getPaymentTypeBadge = (type) => {
    switch (type) {
        case 'cash': return { label: 'نقدي', class: 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' };
        case 'credit': return { label: 'آجل', class: 'bg-rose-500/15 text-rose-400 border border-rose-500/30' };
        case 'partial': return { label: 'جزئي', class: 'bg-amber-500/15 text-amber-400 border border-amber-500/30' };
        default: return { label: type, class: 'bg-slate-800 text-slate-400' };
    }
};
</script>

<template>
    <Head :title="$t('dashboard.welcome', { app: tenant?.name || 'مخزني ERP' })" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Welcome Header Banner -->
            <div class="bg-gradient-to-l from-emerald-900 via-slate-900 to-slate-900 rounded-3xl p-6 border border-emerald-500/20 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">☕</span>
                        <h1 class="text-xl md:text-2xl font-black text-white">
                            {{ $t('dashboard.welcome', { app: tenant?.name || 'مخزني ERP' }) }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        {{ $t('common.store') }}: <span class="text-emerald-400 font-black">{{ activeStore?.name || $t('common.main_store_default') }}</span> • {{ $t('dashboard.subtitle') }}
                    </p>
                </div>

                <div class="flex items-center gap-2.5 w-full md:w-auto">
                    <Link
                        href="/invoices/create"
                        class="flex-1 md:flex-none h-11 px-5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/25 transition transform active:scale-95"
                    >
                        <span>⚡</span>
                        <span>{{ $t('dashboard.pos_button') }} (F2)</span>
                    </Link>

                    <Link
                        href="/invoices"
                        class="h-11 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs flex items-center justify-center gap-1.5 transition"
                    >
                        <span>🧾</span>
                        <span class="hidden sm:inline">{{ $t('nav.invoices_log') }}</span>
                    </Link>
                </div>
            </div>

            <!-- KPI Summary Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Sales Today -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-emerald-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">{{ $t('dashboard.sales_today') }}</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-black">
                            💵
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black font-mono text-white">
                            {{ formatMoney(summary?.total_sales) }} <span class="text-xs font-bold text-emerald-400">{{ $t('common.currency') }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1 flex items-center gap-2">
                            <span>{{ $t('dashboard.cash_sales') }}: {{ formatMoney(summary?.cash_sales) }}</span>
                            <span>•</span>
                            <span>{{ $t('dashboard.credit_sales') }}: {{ formatMoney(summary?.credit_sales) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Invoices Count -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-amber-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">{{ $t('dashboard.invoices_count') }}</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-black">
                            🧾
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black font-mono text-white">
                            {{ summary?.invoices_count || 0 }} <span class="text-xs font-bold text-slate-400">{{ $t('pos.invoice_number') }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">
                            {{ formatMoney(summary?.invoices_count ? (summary.total_sales / summary.invoices_count) : 0) }} {{ $t('common.currency') }}
                        </div>
                    </div>
                </div>

                <!-- Card 3: Cash In Drawer / Shift -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-teal-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">{{ $t('dashboard.cash_collected') }}</span>
                        <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center font-black">
                            💰
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black font-mono text-emerald-400">
                            {{ formatMoney(summary?.total_cash_collected) }} <span class="text-xs font-bold text-white">{{ $t('common.currency') }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">
                            {{ activeShift ? $t('dashboard.shift_number', { number: activeShift.shift_number }) : $t('dashboard.closed_shift') }}
                        </div>
                    </div>
                </div>

                <!-- Card 4: Operating Expenses & Net -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-indigo-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">{{ $t('dashboard.net_cash') }}</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-black">
                            📈
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black font-mono" :class="Number(summary?.net_cash_today) >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                            {{ formatMoney(summary?.net_cash_today) }} <span class="text-xs font-bold text-white">{{ $t('common.currency') }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">
                            {{ $t('dashboard.expenses_today') }}: {{ formatMoney(summary?.total_expenses) }} {{ $t('common.currency') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Section: Recent Invoices & Low Stock Radar -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Recent Sales Invoices (2 Columns) -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🧾</span>
                            <h2 class="text-sm font-black text-white">{{ $t('dashboard.recent_invoices') }}</h2>
                        </div>
                        <Link href="/invoices" class="text-xs font-bold text-emerald-400 hover:underline">
                            {{ $t('dashboard.view_all_invoices') }}
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                    <th class="pb-3">{{ $t('pos.invoice_number') }}</th>
                                    <th class="pb-3">{{ $t('pos.customer') }}</th>
                                    <th class="pb-3">{{ $t('pos.payment_type') }}</th>
                                    <th class="pb-3">{{ $t('common.net') }}</th>
                                    <th class="pb-3">{{ $t('common.paid') }}</th>
                                    <th class="pb-3 text-left">{{ $t('common.time') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                <tr v-for="inv in recent_invoices" :key="inv.id" class="hover:bg-slate-800/40 transition">
                                    <td class="py-3 font-mono font-black text-white">
                                        <Link :href="`/invoices/${inv.id}`" class="hover:text-emerald-400">
                                            #{{ inv.invoice_number }}
                                        </Link>
                                    </td>
                                    <td class="py-3 font-bold text-slate-300">{{ inv.customer_name }}</td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="getPaymentTypeBadge(inv.payment_type).class">
                                            {{ getPaymentTypeBadge(inv.payment_type).label }}
                                        </span>
                                    </td>
                                    <td class="py-3 font-mono font-bold text-emerald-400">{{ formatMoney(inv.net_total) }}</td>
                                    <td class="py-3 font-mono font-bold text-slate-300">{{ formatMoney(inv.paid_amount) }}</td>
                                    <td class="py-3 font-mono text-slate-400 text-left">{{ inv.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="recent_invoices.length === 0" class="py-12 text-center text-slate-500 text-xs font-bold">
                            {{ $t('dashboard.no_sales_today') }}
                        </div>
                    </div>
                </div>

                <!-- Right: Low Stock Radar -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🚨</span>
                            <h2 class="text-sm font-black text-white">{{ $t('dashboard.low_stock_radar') }}</h2>
                        </div>
                        <Link href="/items" class="text-xs font-bold text-emerald-400 hover:underline">
                            {{ $t('common.all') }} 👈
                        </Link>
                    </div>

                    <div class="space-y-2.5">
                        <div
                            v-for="item in low_stock_items"
                            :key="item.id"
                            class="p-3 rounded-2xl bg-slate-800/50 border border-slate-800/80 flex items-center justify-between gap-3 hover:border-amber-500/30 transition"
                        >
                            <div class="flex-1 truncate">
                                <div class="font-bold text-xs text-white truncate">{{ item.name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    {{ $t('common.quantity') }}: {{ Number(item.min_stock_level).toFixed(1) }} {{ item.unit }}
                                </div>
                            </div>
                            <div class="text-left font-mono shrink-0">
                                <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                    {{ Number(item.current_stock).toFixed(1) }} {{ item.unit }}
                                </span>
                            </div>
                        </div>

                        <div v-if="low_stock_items.length === 0" class="py-12 text-center text-slate-500 text-xs font-bold">
                            {{ $t('dashboard.no_low_stock') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
