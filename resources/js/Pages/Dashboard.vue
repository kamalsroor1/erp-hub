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
    <Head :title="`لوحة التحكم الرئيسية | ${tenant?.name || 'سرور كوفي ERP'}`" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Welcome Header Banner -->
            <div class="bg-gradient-to-l from-amber-600/30 via-slate-900 to-slate-900 rounded-3xl p-6 border border-amber-500/20 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">☕</span>
                        <h1 class="text-xl md:text-2xl font-black text-white">
                            مرحباً بك في نظام سرور لإدارة الفواتير والمخزون
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        الفرع الحالي: <span class="text-amber-400 font-black">{{ activeStore?.name || 'المخزن الرئيسي الافتراضي' }}</span> • نظرة عامة على المبيعات، رصيد الخزينة، والمخزون
                    </p>
                </div>

                <div class="flex items-center gap-2.5 w-full md:w-auto">
                    <Link
                        href="/pos"
                        class="flex-1 md:flex-none h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-500/25 transition transform active:scale-95 cursor-pointer"
                    >
                        <span>⚡</span>
                        <span>شاشة البيع السريع (POS)</span>
                        <span class="px-1.5 py-0.2 rounded bg-slate-950/20 text-[10px] font-mono">F2</span>
                    </Link>

                    <Link
                        href="/purchases/create"
                        class="h-11 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs flex items-center justify-center gap-1.5 transition"
                    >
                        <span>🚛</span>
                        <span class="hidden sm:inline">فاتورة توريد</span>
                    </Link>
                </div>
            </div>

            <!-- 4 Key Metrics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Today Sales -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-emerald-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">مبيعات اليوم الصادرة</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-black">
                            💵
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black font-mono text-white">
                            {{ formatMoney(summary?.total_sales) }} <span class="text-xs font-bold text-emerald-400">ج.م</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">
                            {{ summary?.invoices_count || 0 }} فاتورة معتمدة اليوم
                        </div>
                    </div>
                </div>

                <!-- Card 2: Monthly Profit Margin -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-amber-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">مجمل أرباح الشهر الحالي</span>
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-black">
                            📈
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black font-mono text-amber-400">
                            {{ formatMoney(summary?.monthly_gross_profit) }} <span class="text-xs font-bold text-white">ج.م</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">
                            هامش الربح: <span class="text-white font-mono font-bold">{{ summary?.monthly_margin || '0.00' }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Customers Debt -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-rose-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">ديون العملاء (الآجل)</span>
                        <div class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center font-black">
                            👥
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black font-mono text-rose-400">
                            {{ formatMoney(summary?.total_customers_debt) }} <span class="text-xs font-bold text-white">ج.م</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">
                            مستحقات واجبة التحصيل
                        </div>
                    </div>
                </div>

                <!-- Card 4: Monthly Sales -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-3 relative overflow-hidden group hover:border-indigo-500/40 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">إجمالي مبيعات الشهر</span>
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-black">
                            📊
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black font-mono text-indigo-300">
                            {{ formatMoney(summary?.monthly_sales) }} <span class="text-xs font-bold text-white">ج.م</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold mt-1">
                            صافي تعاملات الشهر
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Analytics: 7-Day Trend & Peak Hours -->
            <div v-if="analytics?.daily_trend" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- 7-Day Trend (2 Cols) -->
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <span>📊 حركة ومبيعات آخر 7 أيام</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">معدل البيع اليومي وعدد الفواتير الصادرة</p>
                        </div>
                        <div class="text-left">
                            <span class="text-[10px] text-slate-400 font-bold block">متوسط قيمة الفاتورة:</span>
                            <span class="text-sm font-black font-mono text-emerald-400">
                                {{ formatMoney(analytics?.period?.basket_size) }} ج.م
                            </span>
                        </div>
                    </div>

                    <!-- Bar Chart -->
                    <div class="grid grid-cols-7 gap-2 items-end h-44 pt-6 pb-2">
                        <div
                            v-for="(day, dIdx) in analytics.daily_trend"
                            :key="dIdx"
                            class="flex flex-col items-center gap-1.5 h-full justify-end group relative"
                        >
                            <span class="text-[10px] font-mono font-bold text-slate-300">
                                {{ day.sales > 0 ? Number(day.sales).toFixed(0) : '0' }}
                            </span>

                            <div class="w-full bg-slate-800/80 rounded-xl overflow-hidden flex items-end h-28">
                                <div
                                    :style="{ height: `${Math.max(8, Math.round((day.sales / maxDailySales) * 100))}%` }"
                                    class="w-full rounded-xl transition-all duration-500 bg-gradient-to-t from-amber-500 to-amber-400 group-hover:from-amber-400"
                                ></div>
                            </div>

                            <span class="text-[10px] font-bold text-slate-400 truncate w-full text-center">
                                {{ day.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Peak Hours & Payment Split (1 Col) -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <span>⚡ توزيع ساعات الذروة</span>
                            </h3>
                            <span v-if="analytics?.peak_hour?.label" class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-amber-500/20 text-amber-400">
                                الذروة: {{ analytics.peak_hour.label }}
                            </span>
                        </div>

                        <!-- 24-Hour Micro Heatmap -->
                        <div class="grid grid-cols-12 gap-1 pt-1">
                            <div
                                v-for="(h, hIdx) in (analytics.hourly_sales || [])"
                                :key="hIdx"
                                class="h-8 rounded-md bg-slate-800 flex items-end overflow-hidden border border-slate-700/50"
                                :title="`${h.label}: ${h.sales_formatted}`"
                            >
                                <div
                                    v-if="h.intensity > 0"
                                    class="w-full bg-amber-500"
                                    :style="{ height: `${Math.max(15, h.intensity)}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Split -->
                    <div class="space-y-2 pt-3 border-t border-slate-800">
                        <span class="text-xs font-bold text-slate-300 block mb-2">💳 طرق التحصيل:</span>
                        <div class="space-y-1.5">
                            <template v-for="(pm, pIdx) in (analytics.payment_distribution || [])" :key="pIdx">
                                <div v-if="pm.percentage > 0">
                                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-300 mb-0.5">
                                        <span>{{ pm.label }}</span>
                                        <span class="font-mono text-white">{{ pm.percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-amber-500 rounded-full"
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
                <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🧾</span>
                            <h2 class="text-sm font-black text-white">آخر فواتير المبيعات الصادرة</h2>
                        </div>
                        <Link href="/invoices" class="text-xs font-bold text-amber-400 hover:underline">
                            عرض الكل ←
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                    <th class="pb-3">رقم الفاتورة</th>
                                    <th class="pb-3">العميل</th>
                                    <th class="pb-3">طريقة الدفع</th>
                                    <th class="pb-3">الإجمالي</th>
                                    <th class="pb-3">المدفوع</th>
                                    <th class="pb-3 text-left">الوقت</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-sans">
                                <tr v-for="inv in recent_invoices" :key="inv.id" class="hover:bg-slate-800/40 transition">
                                    <td class="py-3 font-mono font-black text-amber-400">
                                        <Link :href="`/invoices/${inv.id}`" class="hover:underline">
                                            #{{ inv.invoice_number }}
                                        </Link>
                                    </td>
                                    <td class="py-3 font-bold text-slate-300 font-tajawal">{{ inv.customer_name }}</td>
                                    <td class="py-3 font-tajawal">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="getPaymentTypeBadge(inv.payment_type).class">
                                            {{ getPaymentTypeBadge(inv.payment_type).label }}
                                        </span>
                                    </td>
                                    <td class="py-3 font-mono font-bold text-white">{{ formatMoney(inv.net_total) }} ج.م</td>
                                    <td class="py-3 font-mono font-bold text-emerald-400">{{ formatMoney(inv.paid_amount) }} ج.م</td>
                                    <td class="py-3 font-mono text-slate-400 text-left">{{ inv.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="recent_invoices.length === 0" class="py-12 text-center text-slate-500 text-xs font-bold">
                            لا توجد فواتير مسجلة اليوم
                        </div>
                    </div>
                </div>

                <!-- Right: Low Stock Radar -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🚨</span>
                            <h2 class="text-sm font-black text-white">تنبيهات النواقص بالمخزن</h2>
                        </div>
                        <Link href="/purchases/smart-reorder" class="text-xs font-bold text-amber-400 hover:underline">
                            مساعد المشتريات ←
                        </Link>
                    </div>

                    <div class="space-y-2.5">
                        <div
                            v-for="item in low_stock_items"
                            :key="item.id"
                            class="p-3 rounded-2xl bg-slate-950 border border-slate-800/80 flex items-center justify-between gap-3 hover:border-amber-500/30 transition"
                        >
                            <div class="flex-1 truncate font-tajawal">
                                <div class="font-bold text-xs text-white truncate">{{ item.name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    الحد الأدنى: {{ Number(item.min_stock_level).toFixed(1) }} {{ item.unit }}
                                </div>
                            </div>
                            <div class="text-left font-mono shrink-0">
                                <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                    {{ Number(item.current_stock).toFixed(1) }} {{ item.unit }}
                                </span>
                            </div>
                        </div>

                        <div v-if="low_stock_items.length === 0" class="py-12 text-center text-slate-500 text-xs font-bold">
                            جميع الأصناف متوفرة فوق الحد الأدنى 👍
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>