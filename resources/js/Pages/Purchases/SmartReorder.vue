<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    suggestions: { type: Array, default: () => [] },
    metrics: { type: Object, default: () => ({}) },
    stores: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const search = ref(props.filters.search || '');
const selectedStoreId = ref(props.filters.store_id || 'all');
const analysisDays = ref(props.filters.analysis_days || 14);
const targetCoverDays = ref(props.filters.target_cover_days || 15);
const urgencyFilter = ref(props.filters.urgency || 'all');

const selectedItemIds = ref([]);

const applyFilters = () => {
    router.get('/purchases/smart-reorder', {
        search: search.value || undefined,
        store_id: selectedStoreId.value !== 'all' ? selectedStoreId.value : undefined,
        analysis_days: analysisDays.value,
        target_cover_days: targetCoverDays.value,
        urgency: urgencyFilter.value !== 'all' ? urgencyFilter.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const selectAllCritical = () => {
    const criticalIds = props.suggestions
        .filter(it => it.urgency === 'critical' || it.urgency === 'warning')
        .map(it => it.id);
    selectedItemIds.value = criticalIds;
};

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedItemIds.value = props.suggestions.map(it => it.id);
    } else {
        selectedItemIds.value = [];
    }
};

const createPurchaseFromSelected = () => {
    if (selectedItemIds.value.length === 0) {
        alert('يرجى تحديد صنف واحد على الأقل لإنشاء فاتورة الشراء والتوريد');
        return;
    }

    const prefillData = props.suggestions
        .filter(it => selectedItemIds.value.includes(it.id))
        .map(it => ({
            item_id: it.id,
            quantity: Number(it.suggested_quantity) > 0 ? Number(it.suggested_quantity) : 10,
        }));

    router.get('/purchases/create', {
        prefill: JSON.stringify(prefillData),
    });
};
</script>

<template>
    <Head title="مساعد المشتريات الذكي والتنبؤ بالنواقص" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/purchases" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            →
                        </Link>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                                <span>🧠 مساعد المشتريات الذكي والتنبؤ بالنواقص</span>
                            </h1>
                            <p class="text-xs text-slate-400 font-bold mt-0.5">
                                خوارزمية ذكية تحلل معدل السحب اليومي وتتنبأ بموعد نفاد المخزون وتقترح كميات الشراء المثالية
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        @click="selectAllCritical"
                        type="button"
                        class="h-11 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 text-xs font-bold transition cursor-pointer"
                    >
                        ⚡ تحديد كل النواقص الحرجة
                    </button>

                    <button
                        @click="createPurchaseFromSelected"
                        :disabled="selectedItemIds.length === 0"
                        type="button"
                        class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <span>📥</span>
                        <span>توليد فاتورة شراء للأصناف المحددة ({{ selectedItemIds.length }})</span>
                    </button>
                </div>
            </div>

            <!-- Top Analytics Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400 font-bold">أصناف حرجة ونافدة</span>
                        <span class="text-sm">🚨</span>
                    </div>
                    <div class="text-2xl font-black font-mono text-rose-400">
                        {{ metrics.critical_count || 0 }}
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400 font-bold">أصناف تقترب من النفاد</span>
                        <span class="text-sm">⚠️</span>
                    </div>
                    <div class="text-2xl font-black font-mono text-amber-400">
                        {{ metrics.warning_count || 0 }}
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400 font-bold">أصناف في النطاق الآمن</span>
                        <span class="text-sm">✅</span>
                    </div>
                    <div class="text-2xl font-black font-mono text-emerald-400">
                        {{ metrics.safe_count || 0 }}
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400 font-bold">إجمالي تكلفة إعادة الطلب</span>
                        <span class="text-sm">💰</span>
                    </div>
                    <div class="text-2xl font-black font-mono text-white">
                        {{ formatMoney(metrics.total_estimated_cost || 0) }} <span class="text-xs text-amber-400">ج.م</span>
                    </div>
                </div>
            </div>

            <!-- Filter Controls Bar -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Urgency Filter Tabs -->
                    <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-2xl border border-slate-800 text-xs font-bold">
                        <button
                            @click="urgencyFilter = 'all'; applyFilters();"
                            type="button"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer"
                            :class="urgencyFilter === 'all' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                        >
                            الكل ({{ suggestions.length }})
                        </button>
                        <button
                            @click="urgencyFilter = 'critical'; applyFilters();"
                            type="button"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer"
                            :class="urgencyFilter === 'critical' ? 'bg-rose-500 text-white font-black' : 'text-slate-400 hover:text-white'"
                        >
                            حرج 🚨
                        </button>
                        <button
                            @click="urgencyFilter = 'warning'; applyFilters();"
                            type="button"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer"
                            :class="urgencyFilter === 'warning' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:text-white'"
                        >
                            تحذير ⚠️
                        </button>
                        <button
                            @click="urgencyFilter = 'safe'; applyFilters();"
                            type="button"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer"
                            :class="urgencyFilter === 'safe' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:text-white'"
                        >
                            آمن ✅
                        </button>
                    </div>

                    <!-- Store Filter -->
                    <select
                        v-if="stores.length > 0"
                        v-model="selectedStoreId"
                        @change="applyFilters"
                        class="h-10 px-3.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-slate-200 focus:border-amber-500 focus:outline-none"
                    >
                        <option value="all">كافة الفروع والمخازن</option>
                        <option v-for="st in stores" :key="st.id" :value="st.id">{{ st.name }}</option>
                    </select>

                    <!-- Analysis Period & Target Days -->
                    <div class="flex items-center gap-2 text-xs text-slate-300 font-bold">
                        <span>تحليل مبيعات:</span>
                        <select
                            v-model.number="analysisDays"
                            @change="applyFilters"
                            class="h-10 px-3 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none"
                        >
                            <option :value="7">آخر 7 أيام</option>
                            <option :value="14">آخر 14 يوم</option>
                            <option :value="30">آخر 30 يوم</option>
                        </select>

                        <span>تغطية تكفي:</span>
                        <select
                            v-model.number="targetCoverDays"
                            @change="applyFilters"
                            class="h-10 px-3 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none"
                        >
                            <option :value="7">7 أيام</option>
                            <option :value="15">15 يوم (أسبوعين)</option>
                            <option :value="30">30 يوم (شهر)</option>
                            <option :value="45">45 يوم</option>
                        </select>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="w-full sm:w-64">
                    <input
                        v-model="search"
                        @input="applyFilters"
                        type="text"
                        placeholder="بحث باسم أو كود الصنف..."
                        class="w-full h-10 px-3.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Reorder Suggestions Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3 text-center w-10">
                                    <input
                                        type="checkbox"
                                        @change="toggleSelectAll"
                                        class="rounded accent-amber-500 w-4 h-4 cursor-pointer"
                                    >
                                </th>
                                <th class="pb-3">الصنف / الخامة</th>
                                <th class="pb-3 font-mono">الرصيد الحالي</th>
                                <th class="pb-3 font-mono">المبيعات ({{ analysisDays }} يوم)</th>
                                <th class="pb-3 font-mono">الاستهلاك اليومي</th>
                                <th class="pb-3 font-mono">أيام كفاية المخزون</th>
                                <th class="pb-3 font-mono text-amber-400">الكمية المقترحة للطلب</th>
                                <th class="pb-3 font-mono">التكلفة التقديرية</th>
                                <th class="pb-3 text-center">مستوى الخطورة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="it in suggestions" :key="it.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3 text-center">
                                    <input
                                        type="checkbox"
                                        :value="it.id"
                                        v-model="selectedItemIds"
                                        class="rounded accent-amber-500 w-4 h-4 cursor-pointer"
                                    >
                                </td>

                                <td class="py-3">
                                    <div class="font-black text-white font-tajawal">{{ it.name }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ it.code }}</div>
                                </td>

                                <td class="py-3 font-mono font-black">
                                    <span
                                        class="px-2.5 py-1 rounded-xl text-xs border"
                                        :class="[
                                            Number(it.current_stock) <= 0 ? 'bg-rose-500/20 border-rose-500/30 text-rose-400' :
                                            (it.urgency === 'critical' ? 'bg-rose-500/10 border-rose-500/20 text-rose-300' :
                                            (it.urgency === 'warning' ? 'bg-amber-500/20 border-amber-500/30 text-amber-400' : 'bg-slate-800 border-slate-700 text-slate-300'))
                                        ]"
                                    >
                                        {{ it.current_stock }} {{ it.unit || 'كجم' }}
                                    </span>
                                </td>

                                <td class="py-3 font-mono text-slate-300 font-bold">
                                    {{ it.analysis_sales }} {{ it.unit || 'كجم' }}
                                </td>

                                <td class="py-3 font-mono text-slate-400">
                                    {{ it.daily_consumption }} / يوم
                                </td>

                                <td class="py-3 font-mono font-bold">
                                    <span :class="it.days_remaining <= 3 ? 'text-rose-400 font-black' : (it.days_remaining <= 7 ? 'text-amber-400' : 'text-emerald-400')">
                                        {{ it.days_remaining === 999 ? 'غير محدود' : it.days_remaining + ' يوم' }}
                                    </span>
                                </td>

                                <td class="py-3 font-mono font-black text-emerald-400 text-sm">
                                    {{ it.suggested_quantity }} {{ it.unit || 'كجم' }}
                                </td>

                                <td class="py-3 font-mono font-bold text-white">
                                    {{ formatMoney(it.estimated_cost) }} ج.م
                                </td>

                                <td class="py-3 text-center font-tajawal">
                                    <span
                                        v-if="it.urgency === 'critical'"
                                        class="px-2.5 py-1 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold text-[11px]"
                                    >
                                        حرج 🚨
                                    </span>
                                    <span
                                        v-else-if="it.urgency === 'warning'"
                                        class="px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 font-bold text-[11px]"
                                    >
                                        تحذير ⚠️
                                    </span>
                                    <span
                                        v-else
                                        class="px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-bold text-[11px]"
                                    >
                                        آمن ✅
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="suggestions.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">🎉</span>
                        <p class="text-xs font-bold text-emerald-400 font-tajawal">مستويات المخزون ممتازة! لا توجد نواقص مطابقة لمعايير الفلتر المحددة.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
