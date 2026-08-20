<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
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
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🗑️</span>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            {{ $t('trash.title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                        {{ $t('trash.subtitle') }}
                    </p>
                </div>
            </div>

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
                        class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black"
                        :class="currentTab === t.id ? 'bg-black/20 text-inherit' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'"
                    >
                        {{ counts[t.countKey] }}
                    </span>
                </button>
            </div>

            <!-- Quick Search Bar -->
            <div class="w-full md:w-96 relative">
                <input
                    v-model="search"
                    type="text"
                    :placeholder="$t('trash.search_placeholder')"
                    class="w-full pr-10 pl-4 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-theme-primary focus:outline-none transition shadow-inner"
                >
                <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                    🔍
                </span>
            </div>

            <!-- Trashed Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('trash.record_name') }}</th>
                                <th class="pb-3">{{ $t('trash.record_details') }}</th>
                                <th class="pb-3">{{ $t('trash.deleted_time') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                            <tr v-for="r in records.data" :key="r.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="py-3.5 font-bold text-slate-900 dark:text-white font-tajawal text-sm">
                                    {{ r.name || r.title || r.return_number }}
                                </td>

                                <td class="py-3.5 font-tajawal text-slate-600 dark:text-slate-400">
                                    <span v-if="r.code" class="font-mono text-theme-primary font-bold">{{ r.code }} | </span>
                                    <span v-if="r.category">{{ r.category }}</span>
                                    <span v-if="r.amount" class="font-mono font-bold text-rose-600 dark:text-rose-400">{{ formatMoney(r.amount) }} {{ $t('common.currency') }}</span>
                                    <span v-if="r.phone" class="font-mono">{{ r.phone }}</span>
                                    <span v-if="r.company_name">{{ r.company_name }}</span>
                                    <span v-if="r.net_total" class="font-mono text-emerald-600 dark:text-emerald-400">{{ formatMoney(r.net_total) }} {{ $t('common.currency') }}</span>
                                </td>

                                <td class="py-3.5 font-tajawal text-slate-500 dark:text-slate-400 text-[11px] font-mono">
                                    {{ r.deleted_at }}
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

                    <div v-if="!records.data || records.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">🎉</span>
                        <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-tajawal">{{ $t('trash.empty_trash') }}</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="records.links && records.links.length > 3" class="pt-4 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-tajawal">
                        {{ $t('common.showing') }} {{ records.from || 0 }} {{ $t('common.to') }} {{ records.to || 0 }} {{ $t('common.of') }} {{ records.total || 0 }}
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in records.links" :key="lIdx">
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
    </AppLayout>
</template>