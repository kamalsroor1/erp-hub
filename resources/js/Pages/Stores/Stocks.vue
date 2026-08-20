<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    stores: { type: Array, default: () => [] },
    selected_store_id: { type: [Number, String], required: true },
    stocks: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const currentStoreId = ref(props.selected_store_id);
const search = ref(props.filters.search || '');
const stockStatus = ref(props.filters.stock_status || 'all');

const applyFilters = () => {
    router.get('/store-stocks', {
        store_id: currentStoreId.value,
        search: search.value || undefined,
        stock_status: stockStatus.value !== 'all' ? stockStatus.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const switchStore = (storeId) => {
    currentStoreId.value = storeId;
    applyFilters();
};

const totalValuation = computed(() => {
    if (!props.stocks.data) return 0;
    return props.stocks.data.reduce((sum, item) => sum + (item.total_valuation || 0), 0);
});

const lowStockCount = computed(() => {
    if (!props.stocks.data) return 0;
    return props.stocks.data.filter(s => s.quantity <= s.min_stock_level).length;
});
</script>

<template>
    <Head title="أرصدة وجرد الفروع والمخازن" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/stores" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            →
                        </Link>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                                <span>📊 أرصدة وجرد بضاعة الفروع وعربيات التوزيع</span>
                            </h1>
                            <p class="text-xs text-slate-400 font-bold mt-0.5">
                                استعراض كميات المخزون المتوفرة، حدود الأمان، وتقييم البضاعة بكل فرع ومخزن
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <Link
                        href="/stock-transfers/create"
                        class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                    >
                        <span>🚚</span>
                        <span>إذن تحويل بضاعة للفرع</span>
                    </Link>
                </div>
            </div>

            <!-- Store Selector Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2">
                <button
                    v-for="st in stores"
                    :key="st.id"
                    @click="switchStore(st.id)"
                    type="button"
                    class="px-4 py-2.5 rounded-2xl text-xs font-bold transition whitespace-nowrap cursor-pointer flex items-center gap-2 border"
                    :class="currentStoreId === st.id ? 'bg-amber-500 text-slate-950 border-amber-400 font-black shadow-lg shadow-amber-500/20' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700'"
                >
                    <span>{{ st.type === 'wholesale_van' || st.type === 'van' ? '🚚' : (st.type === 'main_warehouse' || st.type === 'warehouse' ? '🏭' : '🏬') }}</span>
                    <span>{{ st.name }}</span>
                </button>
            </div>

            <!-- Top KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-1">
                    <span class="text-xs text-slate-400 font-bold">إجمالي عدد الأصناف المعروضة</span>
                    <div class="text-2xl font-black font-mono text-white">
                        {{ stocks.total || stocks.data?.length || 0 }} <span class="text-xs text-slate-500 font-tajawal">صنف</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-1">
                    <span class="text-xs text-slate-400 font-bold">أصناف تحت حد الأمان ⚠️</span>
                    <div class="text-2xl font-black font-mono text-rose-400">
                        {{ lowStockCount }} <span class="text-xs text-slate-500 font-tajawal">صنف</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-1">
                    <span class="text-xs text-slate-400 font-bold">إجمالي تقييم بضاعة الصفحة (سعر التكلفة)</span>
                    <div class="text-2xl font-black font-mono text-emerald-400">
                        {{ formatMoney(totalValuation) }} <span class="text-xs text-white">ج.م</span>
                    </div>
                </div>
            </div>

            <!-- Filter Controls Bar -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-2xl border border-slate-800 text-xs font-bold">
                        <button
                            @click="stockStatus = 'all'; applyFilters();"
                            type="button"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer"
                            :class="stockStatus === 'all' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                        >
                            كافة الأصناف
                        </button>
                        <button
                            @click="stockStatus = 'low'; applyFilters();"
                            type="button"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer"
                            :class="stockStatus === 'low' ? 'bg-rose-500 text-white font-black' : 'text-slate-400 hover:text-white'"
                        >
                            تحت حد الأمان 🚨
                        </button>
                        <button
                            @click="stockStatus = 'out'; applyFilters();"
                            type="button"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer"
                            :class="stockStatus === 'out' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'text-slate-400 hover:text-white'"
                        >
                            نافد من المخزن (0)
                        </button>
                    </div>
                </div>

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

            <!-- Stocks Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('inventory.item_name') }}</th>
                                <th class="pb-3 font-mono">{{ $t('inventory.current_stock') }}</th>
                                <th class="pb-3 font-mono">{{ $t('inventory.min_stock_level') }}</th>
                                <th class="pb-3 font-mono">{{ $t('common.unit_cost') }}</th>
                                <th class="pb-3 font-mono">{{ $t('reports.sales_summary') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="st in stocks.data" :key="st.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5">
                                    <div class="font-black text-white font-tajawal">{{ st.item_name }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ st.item_code }}</div>
                                </td>

                                <td class="py-3.5 font-mono font-black text-sm">
                                    <span
                                        class="px-2.5 py-1 rounded-xl border text-xs"
                                        :class="[
                                            st.quantity <= 0 ? 'bg-rose-500/20 text-rose-400 border-rose-500/30' :
                                            (st.quantity <= st.min_stock_level ? 'bg-amber-500/20 text-amber-400 border-amber-500/30' : 'bg-slate-800 text-emerald-400 border-slate-700')
                                        ]"
                                    >
                                        {{ st.quantity }} {{ st.unit || 'كجم' }}
                                    </span>
                                </td>

                                <td class="py-3.5 font-mono text-slate-400 font-bold">
                                    {{ st.min_stock_level }} {{ st.unit || 'كجم' }}
                                </td>

                                <td class="py-3.5 font-mono text-slate-300 font-bold">
                                    {{ formatMoney(st.cost_price) }} ج.م
                                </td>

                                <td class="py-3.5 font-mono font-black text-white">
                                    {{ formatMoney(st.total_valuation) }} <span class="text-xs text-amber-400">ج.م</span>
                                </td>

                                <td class="py-3.5 text-center font-tajawal">
                                    <span
                                        v-if="st.quantity <= 0"
                                        class="px-2.5 py-1 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 text-[11px] font-bold"
                                    >
                                        نافد 🚫
                                    </span>
                                    <span
                                        v-else-if="st.quantity <= st.min_stock_level"
                                        class="px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[11px] font-bold"
                                    >
                                        تحت الأمان ⚠️
                                    </span>
                                    <span
                                        v-else
                                        class="px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold"
                                    >
                                        متوفر وآمن ✅
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!stocks.data || stocks.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">📦</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">لا توجد أصناف مسجلة في هذا الفرع مطابقة للبحث</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
