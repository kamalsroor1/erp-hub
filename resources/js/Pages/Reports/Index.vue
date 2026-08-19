<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    summary: { type: Object, required: true },
    top_items: { type: Array, default: () => [] },
    expenses_breakdown: { type: Array, default: () => [] },
    stores: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const period = ref(props.filters.period || 'this_month');
const dateFrom = ref(props.filters.from || '');
const dateTo = ref(props.filters.to || '');
const storeId = ref(props.filters.store_id || 'all');

const storeOptions = [
    { id: 'all', name: 'كافة الفروع والمخازن' },
    ...props.stores
];

const setPeriod = (p) => {
    period.value = p;
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

const applyFilters = () => {
    router.get('/reports', {
        period: period.value,
        from: dateFrom.value || undefined,
        to: dateTo.value || undefined,
        store_id: storeId.value !== 'all' ? storeId.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="التقارير المالية وتحليل الأرباح" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header (Hidden on print) -->
            <div class="print:hidden flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📈</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            التقارير المالية الشاملة وتحليل صافي الأرباح
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        حساب المبيعات، تكلفة البضاعة المباعة (COGS)، المصروفات، وصافي الأرباح الحقيقية
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        @click="printReport"
                        type="button"
                        class="h-11 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                    >
                        <span>🖨️</span>
                        <span>طباعة التقرير (A4)</span>
                    </button>
                </div>
            </div>

            <!-- Filter Preset Bar (Hidden on print) -->
            <div class="print:hidden bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <!-- Period Buttons -->
                    <div class="flex flex-wrap items-center gap-1 bg-slate-950/80 p-1 rounded-2xl border border-slate-800 text-xs">
                        <button
                            @click="setPeriod('today')"
                            type="button"
                            class="px-3 py-1.5 rounded-xl font-bold transition cursor-pointer"
                            :class="period === 'today' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                        >
                            اليوم ☀️
                        </button>
                        <button
                            @click="setPeriod('yesterday')"
                            type="button"
                            class="px-3 py-1.5 rounded-xl font-bold transition cursor-pointer"
                            :class="period === 'yesterday' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                        >
                            الأمس 🌙
                        </button>
                        <button
                            @click="setPeriod('this_week')"
                            type="button"
                            class="px-3 py-1.5 rounded-xl font-bold transition cursor-pointer"
                            :class="period === 'this_week' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                        >
                            هذا الأسبوع 📅
                        </button>
                        <button
                            @click="setPeriod('this_month')"
                            type="button"
                            class="px-3 py-1.5 rounded-xl font-bold transition cursor-pointer"
                            :class="period === 'this_month' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                        >
                            هذا الشهر 📊
                        </button>
                        <button
                            @click="setPeriod('last_month')"
                            type="button"
                            class="px-3 py-1.5 rounded-xl font-bold transition cursor-pointer"
                            :class="period === 'last_month' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                        >
                            الشهر الماضي 🗓️
                        </button>
                    </div>

                    <!-- Custom Range -->
                    <div class="flex items-center gap-2">
                        <div class="w-36">
                            <DatePicker v-model="dateFrom" placeholder="من تاريخ..." />
                        </div>
                        <span class="text-slate-500 text-xs">←</span>
                        <div class="w-36">
                            <DatePicker v-model="dateTo" placeholder="إلى تاريخ..." />
                        </div>
                        <button
                            @click="period = 'custom'; applyFilters();"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition cursor-pointer"
                        >
                            تطبيق
                        </button>
                    </div>
                </div>
            </div>

            <!-- Printable Header (Visible only on print) -->
            <div class="hidden print:block text-center border-b pb-4 mb-6">
                <h1 class="text-2xl font-black">سرور كوفي - التقرير المالي وتحليل الأرباح</h1>
                <p class="text-xs text-gray-600 mt-1">الفترة من: {{ filters.from }} إلى: {{ filters.to }}</p>
            </div>

            <!-- Financial Profit Matrix (The 5 Main Pillars) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- 1. Total Sales -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">1. إجمالي المبيعات ({{ summary.invoices_count }} فاتورة)</span>
                    <div class="text-xl font-black font-mono text-white">
                        {{ formatMoney(summary.total_sales) }} <span class="text-xs text-amber-400">ج.م</span>
                    </div>
                </div>

                <!-- 2. Total COGS -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">2. تكلفة الخامات المباعة (COGS)</span>
                    <div class="text-xl font-black font-mono text-slate-300">
                        {{ formatMoney(summary.total_cogs) }} <span class="text-xs text-slate-500">ج.م</span>
                    </div>
                </div>

                <!-- 3. Gross Profit -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">3. مجمل ربح النشاط</span>
                    <div class="text-xl font-black font-mono text-amber-400">
                        {{ formatMoney(summary.gross_profit) }} <span class="text-xs text-white">ج.م</span>
                    </div>
                </div>

                <!-- 4. Operational Expenses -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">4. إجمالي المصروفات والنثريات</span>
                    <div class="text-xl font-black font-mono text-rose-400">
                        {{ formatMoney(summary.total_expenses) }} <span class="text-xs text-white">ج.م</span>
                    </div>
                </div>

                <!-- 5. Net Profit -->
                <div class="bg-slate-900 border-2 border-emerald-500/50 bg-gradient-to-br from-slate-900 to-emerald-950/20 rounded-3xl p-5 shadow-lg space-y-2">
                    <span class="text-xs text-emerald-400 font-black">5. صافي الربح الحقيقي 🏆</span>
                    <div class="text-2xl font-black font-mono" :class="summary.net_profit >= 0 ? 'text-emerald-400' : 'text-rose-500'">
                        {{ formatMoney(summary.net_profit) }} <span class="text-xs text-white">ج.م</span>
                    </div>
                </div>
            </div>

            <!-- Inflows by Payment Type -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 flex items-center justify-between">
                    <div class="space-y-1">
                        <span class="text-[11px] text-slate-400 font-bold">مبيعات نقدية (كاش)</span>
                        <div class="text-base font-black font-mono text-emerald-400">{{ formatMoney(summary.cash_sales) }} ج.م</div>
                    </div>
                    <span class="text-2xl">💵</span>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 flex items-center justify-between">
                    <div class="space-y-1">
                        <span class="text-[11px] text-slate-400 font-bold">مبيعات InstaPay</span>
                        <div class="text-base font-black font-mono text-purple-400">{{ formatMoney(summary.instapay_sales) }} ج.م</div>
                    </div>
                    <span class="text-2xl">⚡</span>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 flex items-center justify-between">
                    <div class="space-y-1">
                        <span class="text-[11px] text-slate-400 font-bold">مبيعات فيزا / بطاقات</span>
                        <div class="text-base font-black font-mono text-sky-400">{{ formatMoney(summary.visa_sales) }} ج.م</div>
                    </div>
                    <span class="text-2xl">💳</span>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 flex items-center justify-between">
                    <div class="space-y-1">
                        <span class="text-[11px] text-slate-400 font-bold">إجمالي المشتريات من الموردين</span>
                        <div class="text-base font-black font-mono text-amber-400">{{ formatMoney(summary.total_purchases) }} ج.م</div>
                    </div>
                    <span class="text-2xl">📦</span>
                </div>
            </div>

            <!-- Two Columns: Top Selling Items & Expenses Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top 10 Best Selling Items -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-white border-b border-slate-800 pb-3 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span>☕</span>
                            <span>الأصناف الأكثر مبيعاً وتحقيقاً للإيراد</span>
                        </span>
                        <span class="text-xs text-amber-400 font-mono font-bold">Top 10</span>
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                    <th class="pb-2">الصنف</th>
                                    <th class="pb-2 font-mono">الكمية المباعة</th>
                                    <th class="pb-2 font-mono">إجمالي الإيراد</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-sans">
                                <tr v-for="(it, itIdx) in top_items" :key="itIdx" class="hover:bg-slate-800/30">
                                    <td class="py-2.5 font-bold text-white font-tajawal">
                                        <span class="text-slate-500 font-mono ml-2">#{{ itIdx + 1 }}</span>
                                        {{ it.name }}
                                    </td>
                                    <td class="py-2.5 font-mono font-bold text-amber-400">
                                        {{ it.quantity }} {{ it.unit }}
                                    </td>
                                    <td class="py-2.5 font-mono font-black text-emerald-400">
                                        {{ formatMoney(it.revenue) }} ج.م
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="top_items.length === 0" class="py-8 text-center text-slate-500 text-xs font-bold font-tajawal">
                            لا توجد مبيعات مسجلة في الفترة المختارة.
                        </div>
                    </div>
                </div>

                <!-- Expenses Breakdown -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                    <h2 class="text-sm font-black text-white border-b border-slate-800 pb-3 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span>📊</span>
                            <span>توزيع المصروفات التشغيلية حسب التصنيف</span>
                        </span>
                        <span class="text-xs text-rose-400 font-mono font-bold">{{ expenses_breakdown.length }} تصنيف</span>
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                    <th class="pb-2">التصنيف</th>
                                    <th class="pb-2 text-center">عدد الإيصالات</th>
                                    <th class="pb-2 font-mono">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-sans">
                                <tr v-for="(e, eIdx) in expenses_breakdown" :key="eIdx" class="hover:bg-slate-800/30">
                                    <td class="py-2.5 font-bold text-white font-tajawal">
                                        {{ e.category }}
                                    </td>
                                    <td class="py-2.5 text-center font-mono text-slate-400 font-bold">
                                        {{ e.count }}
                                    </td>
                                    <td class="py-2.5 font-mono font-black text-rose-400">
                                        {{ formatMoney(e.amount) }} ج.م
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="expenses_breakdown.length === 0" class="py-8 text-center text-slate-500 text-xs font-bold font-tajawal">
                            لا توجد مصروفات مسجلة في الفترة المختارة.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>