<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';

const props = defineProps({
    returns: { type: Object, required: true },
    metrics: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || 'all');
const dateFrom = ref(props.filters.from || '');
const dateTo = ref(props.filters.to || '');
const isDrawerOpen = ref(false);

const typeOptions = computed(() => [
    { id: 'all', name: trans('returns.all_returns') || 'كافة المرتجعات' },
    { id: 'sales_return', name: trans('returns.sales_return') || 'مرتجع مبيعات من عميل ↩️' },
    { id: 'purchase_return', name: trans('returns.purchase_return') || 'مرتجع مشتريات إلى مورد ↪️' },
]);

const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (type.value !== 'all') count++;
    if (dateFrom.value || dateTo.value) count++;
    return count;
});

const applyFilters = () => {
    router.get('/returns', {
        search: search.value || undefined,
        type: type.value !== 'all' ? type.value : undefined,
        from: dateFrom.value || undefined,
        to: dateTo.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
            isDrawerOpen.value = false;
        }
    });
};

let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        applyFilters();
    }, 400);
});

const resetFilters = () => {
    search.value = '';
    type.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

// Details Modal
const showDetailsModal = ref(false);
const selectedReturn = ref(null);

const openDetailsModal = (r) => {
    selectedReturn.value = r;
    showDetailsModal.value = true;
};

const deleteReturn = (r) => {
    if (confirm(trans('returns.confirm_archive', { number: r.return_number }) || `هل أنت متأكد من أرشفة مستند المرتجع (${r.return_number})؟`)) {
        router.delete(`/returns/${r.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="$t('returns.title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🔄</span>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            {{ $t('returns.title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                        {{ $t('returns.subtitle') }}
                    </p>
                </div>

                <Link
                    href="/returns/create"
                    class="h-11 px-5 rounded-2xl btn-primary-theme font-bold text-xs flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer"
                >
                    <span class="text-base font-black">+</span>
                    <span>{{ $t('returns.new_return_btn') }}</span>
                </Link>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('returns.total_returns_val') }}</span>
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-white">
                        {{ formatMoney(metrics.total_returns) }} <span class="text-xs text-theme-primary">{{ $t('common.currency') }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('returns.sales_returns_count') }}</span>
                    <div class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400">
                        {{ metrics.sales_returns_count || 0 }} <span class="text-xs text-slate-400 font-tajawal">{{ $t('returns.doc_unit') }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('returns.purchase_returns_count') }}</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                        {{ metrics.purchase_returns_count || 0 }} <span class="text-xs text-slate-400 font-tajawal">{{ $t('returns.doc_unit') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Filter Bar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-xs space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="$t('returns.search_placeholder')"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-theme-primary focus:outline-none transition shadow-inner"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                            🔍
                        </span>
                    </div>

                    <div class="w-full md:w-auto flex flex-wrap items-center justify-between md:justify-end gap-2">
                        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-950/80 p-1 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs">
                            <button
                                @click="type = 'all'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="type === 'all' ? 'tab-theme-active' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('common.all') }}
                            </button>
                            <button
                                @click="type = 'sales_return'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="type === 'sales_return' ? 'bg-rose-500 text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('returns.sales_return') }}
                            </button>
                            <button
                                @click="type = 'purchase_return'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="type === 'purchase_return' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('returns.purchase_return') }}
                            </button>
                        </div>

                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                        >
                            <span>⚙️</span>
                            <span>{{ $t('common.filter') }}</span>
                            <span v-if="activeFiltersCount > 0" class="w-5 h-5 rounded-full bg-theme-primary text-white font-mono font-black text-[11px] flex items-center justify-center">
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Returns Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('returns.return_number') }}</th>
                                <th class="pb-3">{{ $t('returns.return_type') }}</th>
                                <th class="pb-3">{{ $t('returns.party_name') }}</th>
                                <th class="pb-3">{{ $t('common.date') }}</th>
                                <th class="pb-3 font-mono">{{ $t('common.total') }}</th>
                                <th class="pb-3">{{ $t('returns.reason') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                            <tr v-for="r in returns.data" :key="r.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="py-3.5 font-mono font-black text-theme-primary">
                                    {{ r.return_number }}
                                </td>

                                <td class="py-3.5">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold font-tajawal"
                                        :class="r.return_type === 'sales_return' ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30'"
                                    >
                                        {{ r.return_type === 'sales_return' ? $t('returns.sales_return') : $t('returns.purchase_return') }}
                                    </span>
                                </td>

                                <td class="py-3.5 font-bold text-slate-900 dark:text-white font-tajawal">
                                    {{ r.party_name }}
                                </td>

                                <td class="py-3.5 font-mono text-slate-500 dark:text-slate-400 text-[11px]">
                                    {{ r.return_date }}
                                </td>

                                <td class="py-3.5 font-mono font-black text-slate-900 dark:text-white text-sm">
                                    {{ formatMoney(r.net_total) }} {{ $t('common.currency') }}
                                </td>

                                <td class="py-3.5 font-tajawal text-slate-500 dark:text-slate-400 text-[11px]">
                                    {{ r.reason || '—' }}
                                </td>

                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 font-tajawal">
                                        <button
                                            @click="openDetailsModal(r)"
                                            type="button"
                                            class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer"
                                        >
                                            {{ $t('returns.details_btn', { count: r.items_count }) }}
                                        </button>

                                        <button
                                            @click="deleteReturn(r)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 transition cursor-pointer"
                                            :title="$t('common.delete')"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!returns.data || returns.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">🔄</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">{{ $t('returns.no_returns_found') }}</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="returns.links && returns.links.length > 3" class="pt-4 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-tajawal">
                        {{ $t('common.actions') ? `عرض ${returns.from || 0} إلى ${returns.to || 0} من إجمالي ${returns.total || 0}` : `Showing ${returns.from || 0} to ${returns.to || 0} of ${returns.total || 0}` }}
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in returns.links" :key="lIdx">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                :class="link.active ? 'tab-theme-active' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-3 py-1.5 rounded-xl text-xs text-slate-400 dark:text-slate-600 font-bold"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Slide-Over Drawer -->
        <FilterDrawer
            :show="isDrawerOpen"
            :active-count="activeFiltersCount"
            @close="isDrawerOpen = false"
            @apply="applyFilters"
            @reset="resetFilters"
        >
            <div class="space-y-5">
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300">🔍 {{ $t('common.search') }}</label>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="$t('common.search')"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300">🔄 {{ $t('returns.return_type') }}</label>
                    <SearchableSelect
                        v-model="type"
                        :options="typeOptions"
                        :placeholder="$t('returns.return_type')"
                    />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $t('contacts.from_date') }}</label>
                        <DatePicker v-model="dateFrom" :placeholder="$t('contacts.from_date')" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $t('contacts.to_date') }}</label>
                        <DatePicker v-model="dateTo" :placeholder="$t('contacts.to_date')" />
                    </div>
                </div>
            </div>
        </FilterDrawer>

        <!-- Return Details Modal -->
        <div
            v-if="showDetailsModal && selectedReturn"
            @click="showDetailsModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4 text-slate-900 dark:text-white">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="font-black text-base text-slate-900 dark:text-white">{{ $t('returns.return_details') }}: {{ selectedReturn.return_number }}</h3>
                        <p class="text-xs text-theme-primary font-bold mt-0.5">{{ selectedReturn.party_name }} | {{ selectedReturn.return_date }}</p>
                    </div>
                    <button @click="showDetailsModal = false" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs dark:hover:text-white transition">✕</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                <th class="pb-2">{{ $t('inventory.item_name') }}</th>
                                <th class="pb-2 font-mono">{{ $t('common.quantity') }}</th>
                                <th class="pb-2 font-mono">{{ $t('invoices.unit_price') }}</th>
                                <th class="pb-2 font-mono">{{ $t('common.total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                            <tr v-for="it in selectedReturn.items" :key="it.id">
                                <td class="py-2.5 font-bold text-slate-900 dark:text-white font-tajawal">{{ it.item_name }}</td>
                                <td class="py-2.5 font-mono font-black text-theme-primary">{{ it.quantity }}</td>
                                <td class="py-2.5 font-mono text-slate-600 dark:text-slate-300">{{ formatMoney(it.unit_price) }}</td>
                                <td class="py-2.5 font-mono font-black text-emerald-600 dark:text-emerald-400">{{ formatMoney(it.subtotal) }} {{ $t('common.currency') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-950/80 rounded-2xl border border-slate-200 dark:border-slate-800 flex items-center justify-between font-mono">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-tajawal">{{ $t('returns.total_returns_val') }}:</span>
                    <span class="text-lg font-black text-theme-primary">{{ formatMoney(selectedReturn.net_total) }} {{ $t('common.currency') }}</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>