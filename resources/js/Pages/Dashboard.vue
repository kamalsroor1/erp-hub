<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    metrics: { type: Object, default: () => ({}) },
    analytics: { type: Object, default: () => ({}) },
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
const analytics = computed(() => props.analytics || {});

const { formatMoney } = useMoney();

const maxDailySales = computed(() => {
    if (!props.analytics?.daily_trend?.length) return 1;
    return Math.max(...props.analytics.daily_trend.map(d => d.sales), 1);
});

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
    <Head :title="`${$t('dashboard.welcome_banner_title')} | ${tenant?.name || 'سرور كوفي ERP'}`" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Welcome Header Banner -->
            <div class="bg-gradient-to-l from-amber-600/30 via-slate-900 to-slate-900 rounded-3xl p-6 lg:p-8 border border-amber-500/20 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">☕</span>
                        <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight">
                            {{ $t('dashboard.welcome_banner_title') }}
                        </h1>
                    </div>
                    <p class="text-sm lg:text-base text-slate-300 font-bold flex flex-wrap items-center gap-2">
                        <span>{{ $t('dashboard.current_branch_label') }}</span>
                        <span class="px-2.5 py-0.5 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 font-black">
                            {{ activeStore?.name || $t('common.main_store_default') }}
                        </span>
                        <span class="text-slate-500 hidden sm:inline">•</span>
                        <span class="text-slate-400">{{ $t('dashboard.overview_subtitle') }}</span>
                    </p>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <Link
                        href="/pos"
                        class="flex-1 md:flex-none h-12 px-6 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-sm flex items-center justify-center gap-2.5 shadow-xl shadow-amber-500/25 transition transform active:scale-95 cursor-pointer"
                    >
                        <span class="text-base">⚡</span>
                        <span>{{ $t('dashboard.pos_fast_btn') }}</span>
                        <span class="px-2 py-0.5 rounded-lg bg-slate-950/20 text-xs font-mono font-black">F2</span>
                    </Link>

                    <Link
                        href="/purchases/create"
                        class="h-12 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-black text-sm flex items-center justify-center gap-2 border border-slate-700 transition cursor-pointer"
                    >
                        <span>🚛</span>
                        <span class="hidden sm:inline">{{ $t('dashboard.supply_invoice_btn') }}</span>
                    </Link>
                </div>
            </div>

            <!-- 4 Key Metrics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Today Sales -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-emerald-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-300">{{ $t('dashboard.today_sales_card') }}</span>
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg font-black">
                            💵
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl lg:text-4xl font-black font-mono text-white tracking-tight">
                            {{ formatMoney(summary?.total_sales) }} <span class="text-sm font-bold text-emerald-400">{{ $t('common.currency') }}</span>
                        </div>
                        <div class="text-xs text-slate-400 font-bold mt-1.5 flex items-center gap-1">
                            <span>🧾</span>
                            <span>{{ summary?.invoices_count || 0 }} {{ $t('dashboard.today_invoices_count', { count: '' }).replace(':count', '') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Monthly Profit Margin -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-amber-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-300">{{ $t('dashboard.monthly_gross_profit_card') }}</span>
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg font-black">
                            📈
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl lg:text-4xl font-black font-mono text-amber-400 tracking-tight">
                            {{ formatMoney(summary?.monthly_gross_profit) }} <span class="text-sm font-bold text-white">{{ $t('common.currency') }}</span>
                        </div>
                        <div class="text-xs text-slate-400 font-bold mt-1.5 flex items-center gap-1">
                            <span>{{ $t('dashboard.profit_margin_label') }}</span>
                            <span class="text-emerald-400 font-mono font-black text-sm">{{ summary?.monthly_margin || '0.00' }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Customers Debt -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-rose-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-300">{{ $t('dashboard.customers_debt_card') }}</span>
                        <div class="w-10 h-10 rounded-2xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-lg font-black">
                            👥
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl lg:text-4xl font-black font-mono text-rose-400 tracking-tight">
                            {{ formatMoney(summary?.total_customers_debt) }} <span class="text-sm font-bold text-white">{{ $t('common.currency') }}</span>
                        </div>
                        <div class="text-xs text-slate-400 font-bold mt-1.5 flex items-center gap-1">
                            <span>⏳</span>
                            <span>{{ $t('dashboard.due_collections_label') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Monthly Sales -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-indigo-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-300">{{ $t('dashboard.monthly_sales_card') }}</span>
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-lg font-black">
                            📊
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl lg:text-4xl font-black font-mono text-indigo-300 tracking-tight">
                            {{ formatMoney(summary?.monthly_sales) }} <span class="text-sm font-bold text-white">{{ $t('common.currency') }}</span>
                        </div>
                        <div class="text-xs text-slate-400 font-bold mt-1.5 flex items-center gap-1">
                            <span>📅</span>
                            <span>{{ $t('dashboard.monthly_net_operations') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Analytics: 7-Day Trend & Peak Hours -->
            <div v-if="analytics?.daily_trend" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- 7-Day Trend (2 Cols) -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base lg:text-lg font-black text-white flex items-center gap-2">
                                <span>📊</span>
                                <span>{{ $t('dashboard.seven_days_trend_title') }}</span>
                            </h3>
                            <p class="text-xs lg:text-sm text-slate-400 font-bold mt-1">{{ $t('dashboard.seven_days_trend_desc') }}</p>
                        </div>
                        <div class="text-start sm:text-left bg-slate-950/80 px-3.5 py-2 rounded-2xl border border-slate-800">
                            <span class="text-xs text-slate-400 font-bold block">{{ $t('dashboard.avg_invoice_val') }}</span>
                            <span class="text-base font-black font-mono text-emerald-400">
                                {{ formatMoney(analytics?.period?.basket_size) }} {{ $t('common.currency') }}
                            </span>
                        </div>
                    </div>

                    <!-- Bar Chart -->
                    <div class="grid grid-cols-7 gap-3 items-end h-48 pt-4 pb-2">
                        <div
                            v-for="(day, dIdx) in analytics.daily_trend"
                            :key="dIdx"
                            class="flex flex-col items-center gap-2 h-full justify-end group relative"
                        >
                            <span class="text-xs font-mono font-bold text-slate-200">
                                {{ day.sales > 0 ? Number(day.sales).toFixed(0) : '0' }}
                            </span>

                            <div class="w-full bg-slate-800/80 rounded-2xl overflow-hidden flex items-end h-32">
                                <div
                                    :style="{ height: `${Math.max(8, Math.round((day.sales / maxDailySales) * 100))}%` }"
                                    class="w-full rounded-2xl transition-all duration-500 bg-gradient-to-t from-amber-500 to-amber-400 group-hover:from-amber-400 shadow-md shadow-amber-500/15"
                                ></div>
                            </div>

                            <span class="text-xs font-bold text-slate-300 truncate w-full text-center">
                                {{ day.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Peak Hours & Payment Split (1 Col) -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-5 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <h3 class="text-base font-black text-white flex items-center gap-2">
                                <span>⚡</span>
                                <span>{{ $t('dashboard.peak_hours_title') }}</span>
                            </h3>
                            <span v-if="analytics?.peak_hour?.label" class="text-xs font-black px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                {{ $t('dashboard.peak_hour_badge', { hour: analytics.peak_hour.label }) }}
                            </span>
                        </div>

                        <!-- 24-Hour Micro Heatmap -->
                        <div class="grid grid-cols-12 gap-1.5 pt-1">
                            <div
                                v-for="(h, hIdx) in (analytics.hourly_sales || [])"
                                :key="hIdx"
                                class="h-9 rounded-lg bg-slate-800 flex items-end overflow-hidden border border-slate-700/50"
                                :title="`${h.label}: ${h.sales_formatted}`"
                            >
                                <div
                                    v-if="h.intensity > 0"
                                    class="w-full bg-amber-500 rounded-b-lg"
                                    :style="{ height: `${Math.max(20, h.intensity)}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Split -->
                    <div class="space-y-3 pt-4 border-t border-slate-800">
                        <span class="text-sm font-bold text-slate-200 block">💳 {{ $t('dashboard.collection_methods') }}</span>
                        <div class="space-y-2">
                            <template v-for="(pm, pIdx) in (analytics.payment_distribution || [])" :key="pIdx">
                                <div v-if="pm.percentage > 0">
                                    <div class="flex items-center justify-between text-xs font-bold text-slate-300 mb-1">
                                        <span>{{ pm.label }}</span>
                                        <span class="font-mono text-white font-black">{{ pm.percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-gradient-to-r from-amber-500 to-amber-400 rounded-full"
                                            :style="{ width: `${pm.percentage}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Section: Recent Invoices & Low Stock Radar -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Recent Sales Invoices (2 Columns) -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">🧾</span>
                            <h2 class="text-base lg:text-lg font-black text-white">{{ $t('dashboard.recent_invoices_title') }}</h2>
                        </div>
                        <Link href="/invoices" class="text-xs font-black text-amber-400 hover:text-amber-300 transition">
                            {{ $t('dashboard.view_all') }}
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-sm">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                    <th class="pb-3">{{ $t('dashboard.invoice_number_col') }}</th>
                                    <th class="pb-3">{{ $t('dashboard.customer_col') }}</th>
                                    <th class="pb-3">{{ $t('dashboard.payment_method_col') }}</th>
                                    <th class="pb-3">{{ $t('dashboard.total_col') }}</th>
                                    <th class="pb-3">{{ $t('dashboard.paid_col') }}</th>
                                    <th class="pb-3 text-left">{{ $t('dashboard.time_col') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-sans">
                                <tr v-for="inv in recent_invoices" :key="inv.id" class="hover:bg-slate-800/40 transition">
                                    <td class="py-3.5 font-mono font-black text-amber-400">
                                        <Link :href="`/invoices/${inv.id}`" class="hover:underline">
                                            #{{ inv.invoice_number }}
                                        </Link>
                                    </td>
                                    <td class="py-3.5 font-bold text-slate-200 font-tajawal">{{ inv.customer_name }}</td>
                                    <td class="py-3.5 font-tajawal">
                                        <span class="px-2.5 py-1 rounded-xl text-xs font-black" :class="getPaymentTypeBadge(inv.payment_type).class">
                                            {{ getPaymentTypeBadge(inv.payment_type).label }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 font-mono font-bold text-white">{{ formatMoney(inv.net_total) }} {{ $t('common.currency') }}</td>
                                    <td class="py-3.5 font-mono font-bold text-emerald-400">{{ formatMoney(inv.paid_amount) }} {{ $t('common.currency') }}</td>
                                    <td class="py-3.5 font-mono text-slate-400 text-left text-xs">{{ inv.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="recent_invoices.length === 0" class="py-12 text-center text-slate-500 text-sm font-bold">
                            {{ $t('dashboard.no_invoices_today') }}
                        </div>
                    </div>
                </div>

                <!-- Right: Low Stock Radar -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">🚨</span>
                            <h2 class="text-base font-black text-white">{{ $t('dashboard.low_stock_radar_title') }}</h2>
                        </div>
                        <Link href="/purchases/smart-reorder" class="text-xs font-black text-amber-400 hover:text-amber-300 transition">
                            {{ $t('dashboard.purchases_assistant') }}
                        </Link>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="item in low_stock_items"
                            :key="item.id"
                            class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800/80 flex items-center justify-between gap-3 hover:border-amber-500/30 transition"
                        >
                            <div class="flex-1 truncate font-tajawal">
                                <div class="font-bold text-sm text-white truncate">{{ item.name }}</div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">
                                    {{ $t('dashboard.min_stock_level') }} {{ Number(item.min_stock_level).toFixed(1) }} {{ item.unit }}
                                </div>
                            </div>
                            <div class="text-left font-mono shrink-0">
                                <span class="px-3 py-1 rounded-xl text-xs font-black bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                    {{ Number(item.current_stock).toFixed(1) }} {{ item.unit }}
                                </span>
                            </div>
                        </div>

                        <div v-if="low_stock_items.length === 0" class="py-12 text-center text-slate-500 text-sm font-bold">
                            {{ $t('dashboard.all_items_safe_radar') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
