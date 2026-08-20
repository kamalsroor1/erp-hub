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
    <Head :title="$t('inventory.store_stocks')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/stores" class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center font-bold text-sm transition active:scale-90 shadow-xs border border-slate-200 dark:border-transparent">
                            →
                        </Link>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>📊 {{ $t('inventory.store_stocks') }}</span>
                            </h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-bold mt-0.5">
                                {{ $t('inventory.store_stocks_subtitle') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto">
                    <Link
                        href="/stock-transfers/create"
                        class="w-full sm:w-auto h-11 px-5 rounded-2xl btn-primary-theme font-bold text-xs flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer shadow-theme-primary"
                    >
                        <span>🚚</span>
                        <span>{{ $t('inventory.new_transfer') }}</span>
                    </Link>
                </div>
            </div>

            <!-- Store Selector Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                <button
                    v-for="st in stores"
                    :key="st.id"
                    @click="switchStore(st.id)"
                    type="button"
                    class="h-11 px-4 rounded-2xl text-xs font-bold transition whitespace-nowrap cursor-pointer flex items-center gap-2 border active:scale-95 shadow-xs shrink-0"
                    :class="currentStoreId === st.id ? 'tab-theme-active border-theme-primary' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/80'"
                >
                    <span>{{ st.type === 'wholesale_van' || st.type === 'van' ? '🚚' : (st.type === 'main_warehouse' || st.type === 'warehouse' ? '🏭' : '🏬') }}</span>
                    <span>{{ st.name }}</span>
                </button>
            </div>

            <!-- Top KPI Cards (Bento Grid on Mobile) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-4 font-tajawal">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-3.5 sm:p-5 shadow-xs space-y-1">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('inventory.total_items_count') }}</span>
                    <div class="text-lg sm:text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ stocks.total || stocks.data?.length || 0 }} <span class="text-[11px] text-slate-400 font-tajawal">{{ $t('inventory.item_unit') }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-3.5 sm:p-5 shadow-xs space-y-1">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('inventory.low_stock_count') }} ⚠️</span>
                    <div class="text-lg sm:text-2xl font-black font-mono text-rose-600 dark:text-rose-400">
                        {{ lowStockCount }} <span class="text-[11px] text-slate-400 font-tajawal">{{ $t('inventory.item_unit') }}</span>
                    </div>
                </div>

                <div class="col-span-2 sm:col-span-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-3.5 sm:p-5 shadow-xs space-y-1">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('inventory.total_inventory_value') }}</span>
                    <div class="text-lg sm:text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                        {{ formatMoney(totalValuation) }} <span class="text-[11px] text-slate-700 dark:text-white">{{ $t('common.currency') }}</span>
                    </div>
                </div>
            </div>

            <!-- Filter Controls Bar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-3.5 sm:p-4 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 font-tajawal">
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-950 p-1 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs font-bold w-full sm:w-auto">
                        <button
                            @click="stockStatus = 'all'; applyFilters();"
                            type="button"
                            class="flex-1 sm:flex-none h-9 px-3 rounded-xl transition cursor-pointer active:scale-95"
                            :class="stockStatus === 'all' ? 'tab-theme-active' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        >
                            {{ $t('common.all') }}
                        </button>
                        <button
                            @click="stockStatus = 'low'; applyFilters();"
                            type="button"
                            class="flex-1 sm:flex-none h-9 px-3 rounded-xl transition cursor-pointer active:scale-95"
                            :class="stockStatus === 'low' ? 'bg-rose-500 text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        >
                            {{ $t('inventory.low_stock_only') }}
                        </button>
                        <button
                            @click="stockStatus = 'out'; applyFilters();"
                            type="button"
                            class="flex-1 sm:flex-none h-9 px-3 rounded-xl transition cursor-pointer active:scale-95"
                            :class="stockStatus === 'out' ? 'bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                        >
                            {{ $t('inventory.out_of_stock_only') }}
                        </button>
                    </div>
                </div>

                <div class="w-full md:w-64">
                    <input
                        v-model="search"
                        @input="applyFilters"
                        type="text"
                        :placeholder="$t('inventory.search_item_placeholder')"
                        class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none shadow-inner"
                    >
                </div>
            </div>

            <!-- Stocks Table & Mobile Cards -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-xs space-y-4 overflow-hidden font-tajawal">
                <!-- Desktop Table (Hidden on Mobile) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('inventory.item_name') }}</th>
                                <th class="pb-3 font-mono">{{ $t('inventory.current_stock') }}</th>
                                <th class="pb-3 font-mono">{{ $t('inventory.min_stock_level') }}</th>
                                <th class="pb-3 font-mono">{{ $t('inventory.purchase_price') }}</th>
                                <th class="pb-3 font-mono">{{ $t('inventory.total_inventory_value') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                            <tr v-for="st in stocks.data" :key="st.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="py-3.5">
                                    <div class="font-black text-slate-900 dark:text-white font-tajawal">{{ st.item_name }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ st.item_code }}</div>
                                </td>

                                <td class="py-3.5 font-mono font-black text-sm">
                                    <span
                                        class="px-2.5 py-1 rounded-xl border text-xs"
                                        :class="[
                                            st.quantity <= 0 ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400 border-rose-500/30' :
                                             (st.quantity <= st.min_stock_level ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30' : 'bg-slate-100 dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 border-slate-200 dark:border-slate-700')
                                        ]"
                                    >
                                        {{ st.quantity }} {{ st.unit || 'كجم' }}
                                    </span>
                                </td>

                                <td class="py-3.5 font-mono text-slate-500 dark:text-slate-400 font-bold">
                                    {{ st.min_stock_level }} {{ st.unit || 'كجم' }}
                                </td>

                                <td class="py-3.5 font-mono text-slate-600 dark:text-slate-300 font-bold">
                                    {{ formatMoney(st.cost_price) }} {{ $t('common.currency') }}
                                </td>

                                <td class="py-3.5 font-mono font-black text-slate-900 dark:text-white">
                                    {{ formatMoney(st.total_valuation) }} <span class="text-xs text-theme-primary">{{ $t('common.currency') }}</span>
                                </td>

                                <td class="py-3.5 text-center font-tajawal">
                                    <span
                                        v-if="st.quantity <= 0"
                                        class="px-2.5 py-1 rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30 text-[11px] font-bold"
                                    >
                                        {{ $t('inventory.out_of_stock_only') }}
                                    </span>
                                    <span
                                        v-else-if="st.quantity <= st.min_stock_level"
                                        class="px-2.5 py-1 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 text-[11px] font-bold"
                                    >
                                        {{ $t('inventory.low_stock_only') }}
                                    </span>
                                    <span
                                        v-else
                                        class="px-2.5 py-1 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 text-[11px] font-bold"
                                    >
                                        {{ $t('inventory.available_only') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards View (Visible on Small Screens) -->
                <div class="md:hidden space-y-3 font-tajawal">
                    <div
                        v-for="st in stocks.data"
                        :key="st.id"
                        class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 space-y-2.5 shadow-xs"
                    >
                        <!-- Top: Item Name + Status Badge -->
                        <div class="flex items-start justify-between gap-2 border-b border-slate-200 dark:border-slate-800/80 pb-2">
                            <div>
                                <div class="font-black text-xs text-slate-900 dark:text-white">{{ st.item_name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ st.item_code || '—' }}</div>
                            </div>

                            <span
                                v-if="st.quantity <= 0"
                                class="px-2 py-0.5 rounded-lg bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30 text-[10px] font-bold shrink-0"
                            >
                                {{ $t('inventory.out_of_stock_only') }}
                            </span>
                            <span
                                v-else-if="st.quantity <= st.min_stock_level"
                                class="px-2 py-0.5 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 text-[10px] font-bold shrink-0"
                            >
                                {{ $t('inventory.low_stock_only') }}
                            </span>
                            <span
                                v-else
                                class="px-2 py-0.5 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 text-[10px] font-bold shrink-0"
                            >
                                {{ $t('inventory.available_only') }}
                            </span>
                        </div>

                        <!-- Stock & Price Matrix -->
                        <div class="grid grid-cols-3 gap-2 text-xs font-mono py-1">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-tajawal">{{ $t('inventory.current_stock') }}</span>
                                <span
                                    class="font-black text-xs"
                                    :class="st.quantity <= 0 ? 'text-rose-600 dark:text-rose-400' : (st.quantity <= st.min_stock_level ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')"
                                >
                                    {{ st.quantity }} {{ st.unit || 'كجم' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block font-tajawal">{{ $t('inventory.min_stock_level') }}</span>
                                <span class="font-bold text-slate-600 dark:text-slate-400">{{ st.min_stock_level }}</span>
                            </div>
                            <div class="text-left">
                                <span class="text-[10px] text-slate-400 block font-tajawal">{{ $t('inventory.total_inventory_value') }}</span>
                                <span class="font-black text-theme-primary">{{ formatMoney(st.total_valuation) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!stocks.data || stocks.data.length === 0" class="py-16 text-center space-y-2">
                    <span class="text-3xl">📦</span>
                    <p class="text-xs font-bold text-slate-400 font-tajawal">{{ $t('inventory.no_items_found') }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
