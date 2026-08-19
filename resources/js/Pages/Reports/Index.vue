<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    active_tab: { type: String, default: 'sales' },
    summary: { type: Object, required: true },
    item_profits: { type: Array, default: () => [] },
    store_breakdown: { type: Array, default: () => [] },
    customer_sales: { type: Array, default: () => [] },
    expenses_breakdown: { type: Array, default: () => [] },
    inventory_items: { type: Array, default: () => [] },
    abc_data: { type: Object, default: () => ({}) },
    treasury_data: { type: Object, default: () => ({}) },
    stores: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const currentTab = ref(props.active_tab || 'sales');
const { formatMoney } = useMoney();

const filterForm = ref({
    tab: currentTab.value,
    period: props.filters.period || 'this_month',
    from: props.filters.from || '',
    to: props.filters.to || '',
    store_id: props.filters.store_id || 'all',
    treasury_method: props.filters.treasury_method || 'all',
    stock_filter: props.filters.stock_filter || 'all',
});

const applyFilters = () => {
    filterForm.value.tab = currentTab.value;
    router.get('/reports', filterForm.value, {
        preserveState: true,
        replace: true,
    });
};

const switchTab = (tab) => {
    currentTab.value = tab;
    filterForm.value.tab = tab;
    applyFilters();
};

const setPeriod = (period) => {
    filterForm.value.period = period;
    if (period === 'today') {
        const today = new Date().toISOString().split('T')[0];
        filterForm.value.from = today;
        filterForm.value.to = today;
    } else if (period === 'yesterday') {
        const d = new Date();
        d.setDate(d.getDate() - 1);
        const y = d.toISOString().split('T')[0];
        filterForm.value.from = y;
        filterForm.value.to = y;
    } else if (period === 'this_week') {
        const d = new Date();
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1);
        const mon = new Date(d.setDate(diff)).toISOString().split('T')[0];
        filterForm.value.from = mon;
        filterForm.value.to = new Date().toISOString().split('T')[0];
    } else if (period === 'this_month') {
        const d = new Date();
        const firstDay = new Date(d.getFullYear(), d.getMonth(), 1).toISOString().split('T')[0];
        filterForm.value.from = firstDay;
        filterForm.value.to = new Date().toISOString().split('T')[0];
    } else if (period === 'this_year') {
        const d = new Date();
        const firstDay = new Date(d.getFullYear(), 0, 1).toISOString().split('T')[0];
        filterForm.value.from = firstDay;
        filterForm.value.to = new Date().toISOString().split('T')[0];
    }
    applyFilters();
};

const exportAbc = () => {
    const params = new URLSearchParams({
        from: filterForm.value.from,
        to: filterForm.value.to,
        store_id: filterForm.value.store_id,
    });
    window.location.href = `/reports/export-abc?${params.toString()}`;
};

const printReport = () => {
    const params = new URLSearchParams({
        from: filterForm.value.from,
        to: filterForm.value.to,
        store_id: filterForm.value.store_id,
    });
    window.open(`/reports/print?${params.toString()}`, '_blank');
};
</script>

<template>
    <Head title="التقارير المالية والأرباح والمبيعات" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header Banner -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-2xl font-bold">
                        📊
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            التقارير المالية والأرباح والمبيعات
                        </h1>
                        <p class="text-xs text-slate-400 font-bold mt-0.5">
                            قائمة الدخل P&L، ربحية الأصناف، جرد وتقييم المخزون، وتحليل الخزينة
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        @click="printReport"
                        type="button"
                        class="h-11 px-5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 font-bold text-xs flex items-center gap-2 transition cursor-pointer"
                    >
                        <span>🖨️</span>
                        <span>طباعة التقرير الشامل A4</span>
                    </button>
                </div>
            </div>

            <!-- Global Filter Bar -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <!-- Period Buttons -->
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-slate-400 font-bold text-[11px] px-1">الفترة الزمنية:</span>
                        <button
                            v-for="p in [
                                { id: 'today', label: 'اليوم' },
                                { id: 'yesterday', label: 'أمس' },
                                { id: 'this_week', label: 'هذا الأسبوع' },
                                { id: 'this_month', label: 'هذا الشهر' },
                                { id: 'this_year', label: 'هذا العام' },
                            ]"
                            :key="p.id"
                            @click="setPeriod(p.id)"
                            type="button"
                            class="px-3 py-1.5 rounded-xl font-bold transition cursor-pointer text-xs"
                            :class="filterForm.period === p.id ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-950 text-slate-300 border border-slate-800 hover:text-white'"
                        >
                            {{ p.label }}
                        </button>
                    </div>

                    <!-- Store Filter -->
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 font-bold text-xs hidden sm:inline">الفرع:</span>
                        <select
                            v-model="filterForm.store_id"
                            @change="applyFilters"
                            class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                            <option value="all">جميع الفروع والمخازن</option>
                            <option v-for="s in stores" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Date Range Pickers -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-2 border-t border-slate-800/80 items-end">
                    <DatePicker v-model="filterForm.from" label="من تاريخ" />
                    <DatePicker v-model="filterForm.to" label="إلى تاريخ" />
                    <div>
                        <button
                            @click="applyFilters"
                            type="button"
                            class="w-full h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition cursor-pointer"
                        >
                            تحديث التقرير 🔄
                        </button>
                    </div>
                </div>
            </div>

            <!-- 7 Navigation Tabs -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 bg-slate-950 p-1.5 rounded-2xl border border-slate-800 text-xs">
                <button
                    v-for="tab in [
                        { id: 'sales', label: 'المبيعات والأرباح', icon: '💵' },
                        { id: 'items', label: 'ربحية الأصناف', icon: '📦' },
                        { id: 'stores', label: 'مقارنة الفروع', icon: '🏢' },
                        { id: 'customers', label: 'مسحوبات العملاء', icon: '👥' },
                        { id: 'expenses', label: 'المصروفات', icon: '💸' },
                        { id: 'inventory', label: 'تقييم المخزون', icon: '📈' },
                        { id: 'treasury', label: 'الخزينة والتحصيل', icon: '💰' },
                    ]"
                    :key="tab.id"
                    @click="switchTab(tab.id)"
                    type="button"
                    class="py-2.5 px-2 rounded-xl font-bold transition cursor-pointer flex flex-col sm:flex-row items-center justify-center gap-1.5 text-center"
                    :class="currentTab === tab.id ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-slate-400 hover:text-white'"
                >
                    <span>{{ tab.icon }}</span>
                    <span>{{ tab.label }}</span>
                </button>
            </div>

            <!-- TAB 1: SALES & P&L SUMMARY -->
            <div v-if="currentTab === 'sales'" class="space-y-6">
                <!-- 4 Top KPI Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-2">
                        <span class="text-xs font-bold text-slate-400">إجمالي المبيعات الصادرة</span>
                        <div class="text-2xl font-black font-mono text-white">
                            {{ formatMoney(summary.total_sales) }} <span class="text-xs font-bold text-amber-400">ج.م</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold">
                            {{ summary.invoices_count }} فاتورة معتمدة
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-2">
                        <span class="text-xs font-bold text-slate-400">تكلفة البضاعة المباعة (COGS)</span>
                        <div class="text-2xl font-black font-mono text-indigo-300">
                            {{ formatMoney(summary.total_cogs) }} <span class="text-xs font-bold text-white">ج.م</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold">
                            متوسط الفاتورة: {{ formatMoney(summary.avg_invoice) }} ج.م
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-2">
                        <span class="text-xs font-bold text-slate-400">مجمل أرباح النشاط (Gross Profit)</span>
                        <div class="text-2xl font-black font-mono text-emerald-400">
                            {{ formatMoney(summary.gross_profit) }} <span class="text-xs font-bold text-white">ج.م</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold">
                            هامش الربح الإجمالي: <strong class="text-white">{{ summary.margin_percentage }}%</strong>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-2">
                        <span class="text-xs font-bold text-slate-400">صافي الربح بعد المصروفات (Net)</span>
                        <div class="text-2xl font-black font-mono" :class="summary.net_profit >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                            {{ formatMoney(summary.net_profit) }} <span class="text-xs font-bold text-white">ج.م</span>
                        </div>
                        <div class="text-[11px] text-slate-400 font-bold">
                            المصروفات: {{ formatMoney(summary.total_expenses) }} ج.م
                        </div>
                    </div>
                </div>

                <!-- Financial Statement Summary Table -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h3 class="text-sm font-black text-white flex items-center gap-2">
                        <span>📑</span>
                        <span>قائمة الدخل والأرباح التشغيلية (P&L Breakdown)</span>
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <tbody class="divide-y divide-slate-800">
                                <tr class="py-2.5">
                                    <td class="py-3 font-bold text-slate-300">إجمالي المبيعات الصادرة (Gross Sales)</td>
                                    <td class="py-3 font-mono font-bold text-white text-left">{{ formatMoney(summary.total_sales) }} ج.م</td>
                                </tr>
                                <tr class="py-2.5">
                                    <td class="py-3 font-bold text-slate-300">يُخصم: تكلفة البضاعة المباعة (Cost of Goods Sold)</td>
                                    <td class="py-3 font-mono font-bold text-rose-400 text-left">- {{ formatMoney(summary.total_cogs) }} ج.م</td>
                                </tr>
                                <tr class="py-2.5 bg-slate-950/50">
                                    <td class="py-3 font-black text-amber-400">مجمل الربح التجاري (Gross Profit)</td>
                                    <td class="py-3 font-mono font-black text-amber-400 text-left">{{ formatMoney(summary.gross_profit) }} ج.م</td>
                                </tr>
                                <tr class="py-2.5">
                                    <td class="py-3 font-bold text-slate-300">يُخصم: المصروفات التشغيلية والنثريات (Operating Expenses)</td>
                                    <td class="py-3 font-mono font-bold text-rose-400 text-left">- {{ formatMoney(summary.total_expenses) }} ج.م</td>
                                </tr>
                                <tr class="py-2.5 bg-emerald-500/10 border-t-2 border-emerald-500/30">
                                    <td class="py-3 font-black text-emerald-400 text-sm">صافي الربح النهائي (Net Operating Profit)</td>
                                    <td class="py-3 font-mono font-black text-emerald-400 text-sm text-left">{{ formatMoney(summary.net_profit) }} ج.م</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: ITEM PROFITS -->
            <div v-if="currentTab === 'items'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-white">ربحية ومبيعات الأصناف بالتفصيل</h3>
                    <span class="text-xs font-mono text-slate-400">{{ item_profits.length }} صنف تم بيعه</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">الصنف</th>
                                <th class="pb-3">القسم</th>
                                <th class="pb-3">الكمية المباعة</th>
                                <th class="pb-3">إجمالي الإيراد</th>
                                <th class="pb-3">تكلفة البضاعة</th>
                                <th class="pb-3">مجمل الربح</th>
                                <th class="pb-3 text-left">هامش الربح</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="item in item_profits" :key="item.item_id" class="hover:bg-slate-800/40 transition">
                                <td class="py-3 font-bold text-white font-tajawal">{{ item.name }}</td>
                                <td class="py-3 text-slate-400 font-tajawal">{{ item.category || 'عام' }}</td>
                                <td class="py-3 font-mono text-amber-400">{{ item.total_qty }} {{ item.unit }}</td>
                                <td class="py-3 font-mono font-bold text-white">{{ formatMoney(item.total_revenue) }} ج.م</td>
                                <td class="py-3 font-mono text-slate-400">{{ formatMoney(item.total_cogs) }} ج.م</td>
                                <td class="py-3 font-mono font-bold text-emerald-400">{{ formatMoney(item.profit) }} ج.م</td>
                                <td class="py-3 font-mono text-left font-bold text-amber-400">{{ item.margin }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: STORE COMPARISON -->
            <div v-if="currentTab === 'stores'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-sm">
                <h3 class="text-sm font-black text-white border-b border-slate-800 pb-3">مقارنة أداء الفروع والمخازن وعربيات التوزيع</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">الفرع / المخزن</th>
                                <th class="pb-3">الفواتير</th>
                                <th class="pb-3">إجمالي المبيعات</th>
                                <th class="pb-3">المحصل نقدياً</th>
                                <th class="pb-3">المتبقي (آجل)</th>
                                <th class="pb-3">مجمل الربح</th>
                                <th class="pb-3">هامش الربح</th>
                                <th class="pb-3 text-left">الحصة من المبيعات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="st in store_breakdown" :key="st.id" class="hover:bg-slate-800/40 transition">
                                <td class="py-3 font-bold text-white font-tajawal">{{ st.name }}</td>
                                <td class="py-3 font-mono text-slate-300">{{ st.invoice_count }}</td>
                                <td class="py-3 font-mono font-bold text-white">{{ formatMoney(st.total_sales) }} ج.م</td>
                                <td class="py-3 font-mono text-emerald-400">{{ formatMoney(st.total_paid) }} ج.م</td>
                                <td class="py-3 font-mono text-rose-400">{{ formatMoney(st.total_remaining) }} ج.م</td>
                                <td class="py-3 font-mono font-bold text-amber-400">{{ formatMoney(st.gross_profit) }} ج.م</td>
                                <td class="py-3 font-mono text-slate-300">{{ st.margin }}%</td>
                                <td class="py-3 font-mono text-left font-black text-amber-400">{{ st.share_pct }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: CUSTOMER SALES -->
            <div v-if="currentTab === 'customers'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-white">مبيعات ومسحوبات كبار العملاء</h3>
                    <div class="text-xs text-rose-400 font-bold">
                        إجمالي ديون كل العملاء: <span class="font-mono font-black">{{ formatMoney(summary.total_customers_debt) }} ج.م</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">العميل</th>
                                <th class="pb-3">رقم الهاتف</th>
                                <th class="pb-3">الفواتير</th>
                                <th class="pb-3">إجمالي المسحوبات</th>
                                <th class="pb-3">المدفوع بالفترة</th>
                                <th class="pb-3">المتبقي بالفترة</th>
                                <th class="pb-3 text-left">الرصيد التراكمي الحالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="c in customer_sales" :key="c.customer_id" class="hover:bg-slate-800/40 transition">
                                <td class="py-3 font-bold text-white font-tajawal">{{ c.name }}</td>
                                <td class="py-3 font-mono text-slate-400">{{ c.phone || '-' }}</td>
                                <td class="py-3 font-mono text-slate-300">{{ c.total_invoices }}</td>
                                <td class="py-3 font-mono font-bold text-white">{{ formatMoney(c.total_bought) }} ج.م</td>
                                <td class="py-3 font-mono text-emerald-400">{{ formatMoney(c.total_paid) }} ج.م</td>
                                <td class="py-3 font-mono text-rose-400">{{ formatMoney(c.total_debt_in_period) }} ج.م</td>
                                <td class="py-3 font-mono text-left font-black" :class="c.current_balance > 0 ? 'text-amber-400' : 'text-slate-400'">
                                    {{ formatMoney(c.current_balance) }} ج.م
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 5: EXPENSES BREAKDOWN -->
            <div v-if="currentTab === 'expenses'" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-white">توزيع المصروفات التشغيلية حسب التصنيف</h3>
                    <div class="text-xs text-rose-400 font-bold">
                        إجمالي المصروفات: <span class="font-mono font-black">{{ formatMoney(summary.total_expenses) }} ج.م</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="(exp, eIdx) in expenses_breakdown"
                        :key="eIdx"
                        class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-2"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">{{ exp.category || 'نثريات عامة' }}</span>
                            <span class="text-[10px] font-mono text-slate-400">{{ exp.count }} إيصال</span>
                        </div>
                        <div class="text-xl font-black font-mono text-rose-400">
                            {{ formatMoney(exp.amount) }} <span class="text-xs text-slate-400 font-normal">ج.م</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 6: INVENTORY VALUATION & ABC -->
            <div v-if="currentTab === 'inventory'" class="space-y-6">
                <!-- Valuation Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-1">
                        <span class="text-xs font-bold text-slate-400">تقييم المخزون بسعر التكلفة</span>
                        <div class="text-2xl font-black font-mono text-indigo-300">
                            {{ formatMoney(summary.stock_cost_valuation) }} <span class="text-xs font-bold text-white">ج.م</span>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-1">
                        <span class="text-xs font-bold text-slate-400">تقييم المخزون بسعر البيع</span>
                        <div class="text-2xl font-black font-mono text-white">
                            {{ formatMoney(summary.stock_selling_valuation) }} <span class="text-xs font-bold text-amber-400">ج.م</span>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-1">
                        <span class="text-xs font-bold text-slate-400">الربح المتوقع عند بيع المخزون</span>
                        <div class="text-2xl font-black font-mono text-emerald-400">
                            {{ formatMoney(summary.expected_stock_profit) }} <span class="text-xs font-bold text-white">ج.م</span>
                        </div>
                    </div>
                </div>

                <!-- ABC Analysis Section -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div>
                            <h3 class="text-sm font-black text-white">تحليل باريتو لحركة المخزون (ABC Analysis)</h3>
                            <p class="text-xs text-slate-400 mt-0.5">تصنيف الأصناف حسب المساهمة في الإيرادات الكلية</p>
                        </div>

                        <button
                            @click="exportAbc"
                            type="button"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs font-bold border border-slate-700 transition cursor-pointer flex items-center gap-1.5"
                        >
                            <span>📥</span>
                            <span>تصدير تحليل ABC (Excel)</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-4 space-y-1">
                            <span class="text-xs font-black text-emerald-400">الفئة A (الأعلى إيراداً - 70% إلى 80%)</span>
                            <div class="text-lg font-black font-mono text-white">{{ abc_data?.category_a?.length || 0 }} صنف</div>
                        </div>

                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-4 space-y-1">
                            <span class="text-xs font-black text-amber-400">الفئة B (المتوسطة - 15% إلى 20%)</span>
                            <div class="text-lg font-black font-mono text-white">{{ abc_data?.category_b?.length || 0 }} صنف</div>
                        </div>

                        <div class="bg-slate-800/60 border border-slate-700 rounded-2xl p-4 space-y-1">
                            <span class="text-xs font-black text-slate-400">الفئة C (الأقل تأثيراً - 5%)</span>
                            <div class="text-lg font-black font-mono text-white">{{ abc_data?.category_c?.length || 0 }} صنف</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 7: TREASURY & INFLOW / OUTFLOW -->
            <div v-if="currentTab === 'treasury'" class="space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h3 class="text-sm font-black text-white">حركة السيولة والتحصيلات في الخزينة</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                            <span class="text-xs font-bold text-slate-400">تحصيلات نقدية (Cash)</span>
                            <div class="text-xl font-black font-mono text-emerald-400">
                                {{ formatMoney(treasury_data?.inflows?.cash || 0) }} ج.م
                            </div>
                        </div>

                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                            <span class="text-xs font-bold text-slate-400">تحصيلات انستاباي (InstaPay)</span>
                            <div class="text-xl font-black font-mono text-purple-400">
                                {{ formatMoney(treasury_data?.inflows?.instapay || 0) }} ج.م
                            </div>
                        </div>

                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                            <span class="text-xs font-bold text-slate-400">تحصيلات محفظة (Vodafone Cash)</span>
                            <div class="text-xl font-black font-mono text-amber-400">
                                {{ formatMoney(treasury_data?.inflows?.e_wallet || 0) }} ج.م
                            </div>
                        </div>

                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                            <span class="text-xs font-bold text-slate-400">تحصيلات فيزا / كارت (Visa)</span>
                            <div class="text-xl font-black font-mono text-cyan-400">
                                {{ formatMoney(treasury_data?.inflows?.visa || 0) }} ج.م
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>