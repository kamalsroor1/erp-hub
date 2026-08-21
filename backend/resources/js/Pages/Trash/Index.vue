<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/Common/PageHeader.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';
import Pagination from '@/Components/Common/Pagination.vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';

const props = defineProps({
    tab: { type: String, default: 'items' },
    records: { type: Object, required: true },
    counts: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const currentTab = ref(props.tab);
const search = ref(props.filters.search || '');

const tabs = computed(() => [
    { id: 'items', name: trans('trash.tab_items') || 'الأصناف والمخزون 📦', countKey: 'items' },
    { id: 'customers', name: trans('trash.tab_customers') || 'العملاء 👥', countKey: 'customers' },
    { id: 'suppliers', name: trans('trash.tab_suppliers') || 'الموردين 🏭', countKey: 'suppliers' },
    { id: 'stores', name: trans('trash.tab_stores') || 'الفروع والمخازن 🏬', countKey: 'stores' },
    { id: 'expenses', name: trans('trash.tab_expenses') || 'المصروفات 💸', countKey: 'expenses' },
    { id: 'returns', name: trans('trash.tab_returns') || 'المرتجعات 🔄', countKey: 'returns' },
]);

const setTab = (t) => {
    currentTab.value = t;
    search.value = '';
    applyFilters();
};

const applyFilters = () => {
    router.get('/trash', {
        tab: currentTab.value,
        search: search.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        applyFilters();
    }, 400);
});

const restoreRecord = (id) => {
    router.post(`/trash/${currentTab.value}/${id}/restore`, {}, {
        preserveScroll: true,
    });
};

const forceDeleteRecord = (id) => {
    const confirmMsg = trans('trash.confirm_force_delete') || 'تحذير: الحذف النهائي لا يمكن التراجع عنه أبداً. هل أنت متأكد؟';
    if (confirm(confirmMsg)) {
        router.delete(`/trash/${currentTab.value}/${id}/force-delete`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="$t('trash.title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <PageHeader
                :title="$t('trash.title')"
                :subtitle="$t('trash.subtitle')"
                icon="🗑️"
            />

            <!-- Tabs Navigation -->
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
                <button
                    v-for="t in tabs"
                    :key="t.id"
                    @click="setTab(t.id)"
                    type="button"
                    class="px-4 py-2 rounded-2xl text-xs font-bold transition flex items-center gap-2 cursor-pointer"
                    :class="currentTab === t.id ? 'tab-theme-active shadow-xs' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    <span>{{ t.name }}</span>
                    <span
                        v-if="counts[t.countKey] > 0"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white"
                    >
                        {{ counts[t.countKey] }}
                    </span>
                </button>
            </div>

            <!-- Table & List View -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-3.5 sm:p-5 shadow-xs space-y-4 font-tajawal">
                <!-- Search -->
                <div class="w-full md:w-80 relative">
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="$t('trash.search_trash')"
                        class="w-full pr-10 pl-4 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-theme-primary focus:outline-none transition"
                    >
                    <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                        🔍
                    </span>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table v-if="records.data && records.data.length > 0" class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('trash.record_name_col') }}</th>
                                <th class="pb-3">{{ $t('trash.deleted_at_col') }}</th>
                                <th class="pb-3">{{ $t('trash.additional_info_col') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                            <tr v-for="r in records.data" :key="r.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="py-3.5 font-black text-slate-900 dark:text-white font-tajawal">
                                    {{ r.name || r.title || r.invoice_number || r.transfer_number || `#${r.id}` }}
                                </td>

                                <td class="py-3.5 font-mono text-rose-500 text-[11px]">
                                    {{ r.deleted_at }}
                                </td>

                                <td class="py-3.5 text-slate-500 dark:text-slate-400 font-tajawal">
                                    <span v-if="r.sku" class="font-mono text-xs">SKU: {{ r.sku }}</span>
                                    <span v-else-if="r.phone" class="font-mono text-xs">{{ r.phone }}</span>
                                    <span v-else-if="r.amount" class="font-mono text-xs font-bold">{{ formatMoney(r.amount) }} {{ $t('common.currency') }}</span>
                                    <span v-else>-</span>
                                </td>

                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-2 font-tajawal">
                                        <button
                                            @click="restoreRecord(r.id)"
                                            type="button"
                                            class="px-3 py-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 font-bold transition cursor-pointer"
                                        >
                                            {{ $t('trash.restore_btn') }}
                                        </button>

                                        <button
                                            @click="forceDeleteRecord(r.id)"
                                            type="button"
                                            class="px-3 py-1.5 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-600 dark:text-rose-400 border border-rose-500/30 font-bold transition cursor-pointer"
                                        >
                                            {{ $t('trash.force_delete_btn') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <EmptyState
                        v-if="!records.data || records.data.length === 0"
                        icon="🎉"
                        :title="$t('trash.empty_trash')"
                    />
                </div>

                <!-- Pagination -->
                <Pagination
                    :links="records.links"
                    :from="records.from"
                    :to="records.to"
                    :total="records.total"
                />
            </div>
        </div>
    </AppLayout>
</template>