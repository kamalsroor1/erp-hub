<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { trans } from '@/helpers/trans';

const props = defineProps({
    logs: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
    users: { type: Array, default: () => [] },
    stores: { type: Array, default: () => [] },
    modules_list: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const isFilterOpen = ref(false);
const viewMode = ref(props.filters.view || 'timeline');
const selectedLog = ref(null);

const filterForm = ref({
    search: props.filters.search || '',
    module: props.filters.module || 'all',
    action: props.filters.action || 'all',
    user_id: props.filters.user_id || 'all',
    store_id: props.filters.store_id || 'all',
    preset: props.filters.preset || 'all',
    from: props.filters.from || '',
    to: props.filters.to || '',
});

const applyFilters = () => {
    isFilterOpen.value = false;
    router.get('/activity-logs', {
        ...filterForm.value,
        view: viewMode.value,
    }, { preserveState: true, replace: true });
};

const setPreset = (preset) => {
    filterForm.value.preset = preset;
    if (preset === 'today') {
        const today = new Date().toISOString().split('T')[0];
        filterForm.value.from = today;
        filterForm.value.to = today;
    } else if (preset === 'yesterday') {
        const d = new Date();
        d.setDate(d.getDate() - 1);
        const y = d.toISOString().split('T')[0];
        filterForm.value.from = y;
        filterForm.value.to = y;
    } else if (preset === '7days') {
        const d = new Date();
        d.setDate(d.getDate() - 6);
        filterForm.value.from = d.toISOString().split('T')[0];
        filterForm.value.to = new Date().toISOString().split('T')[0];
    } else if (preset === 'all') {
        filterForm.value.from = '';
        filterForm.value.to = '';
    }
    applyFilters();
};

const resetFilters = () => {
    filterForm.value = {
        search: '',
        module: 'all',
        action: 'all',
        user_id: 'all',
        store_id: 'all',
        preset: 'all',
        from: '',
        to: '',
    };
    applyFilters();
};

const exportCsv = () => {
    const params = new URLSearchParams({
        ...filterForm.value,
    });
    window.location.href = `/activity-logs/export-csv?${params.toString()}`;
};

const getActionBadge = (action) => {
    switch (action) {
        case 'created': return { label: trans('activity.action_created') || 'إضافة جديدة ➕', class: 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' };
        case 'updated': return { label: trans('activity.action_updated') || 'تعديل بيانات ✏️', class: 'bg-blue-500/15 text-blue-400 border border-blue-500/30' };
        case 'deleted': return { label: trans('activity.action_deleted') || 'حذف 🗑️', class: 'bg-rose-500/15 text-rose-400 border border-rose-500/30' };
        case 'cancelled': return { label: trans('activity.action_cancelled') || 'إلغاء فاتورة 🚫', class: 'bg-rose-500/20 text-rose-300 border border-rose-500/40 font-bold' };
        case 'login': return { label: trans('activity.action_login') || 'تسجيل دخول 🔐', class: 'bg-teal-500/15 text-teal-400 border border-teal-500/30' };
        case 'login_failed': return { label: trans('activity.action_login_failed') || 'محاولة فاشلة ⚠️', class: 'bg-rose-500/25 text-rose-400 border border-rose-500/50' };
        default: return { label: action, class: 'bg-slate-800 text-slate-400' };
    }
};

const getModuleBadge = (module) => {
    return props.modules_list[module] || module;
};
</script>

<template>
    <Head :title="$t('activity.title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header Banner -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-theme-light border border-theme-primary text-theme-primary flex items-center justify-center text-2xl font-bold">
                        🛡️
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            {{ $t('activity.title') }}
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-bold mt-0.5">
                            {{ $t('activity.subtitle') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto">
                    <!-- Export CSV -->
                    <button
                        @click="exportCsv"
                        type="button"
                        class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs border border-slate-200 dark:border-slate-700 transition flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>📥</span>
                        <span>{{ $t('activity.export_excel') }}</span>
                    </button>

                    <!-- Filter Drawer Button -->
                    <button
                        @click="isFilterOpen = true"
                        type="button"
                        class="px-4 py-2.5 rounded-2xl btn-primary-theme font-black text-xs transition transform active:scale-95 flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>⚡</span>
                        <span>{{ $t('activity.advanced_filter') }}</span>
                    </button>
                </div>
            </div>

            <!-- 4 KPI Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 space-y-1 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $t('activity.today_total') }}</span>
                    <div class="text-xl font-black font-mono text-slate-900 dark:text-white">{{ stats.today_total || 0 }}</div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 space-y-1 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $t('activity.today_critical') }}</span>
                    <div class="text-xl font-black font-mono text-rose-600 dark:text-rose-400">{{ stats.today_critical || 0 }}</div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 space-y-1 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $t('activity.today_users') }}</span>
                    <div class="text-xl font-black font-mono text-emerald-600 dark:text-emerald-400">{{ stats.today_users || 0 }}</div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 space-y-1 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">{{ $t('activity.today_stores') }}</span>
                    <div class="text-xl font-black font-mono text-theme-primary">{{ stats.today_stores || 0 }}</div>
                </div>
            </div>

            <!-- Quick Presets Bar & View Switcher -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-2.5 rounded-2xl shadow-xs">
                <!-- Date Presets -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-slate-500 dark:text-slate-400 font-bold text-[11px] px-2">{{ $t('activity.period') }}</span>
                    <button
                        v-for="p in [
                            { id: 'all', label: $t('common.all') || 'الكل' },
                            { id: 'today', label: $t('common.today') || 'اليوم' },
                            { id: 'yesterday', label: $t('common.yesterday') || 'أمس' },
                            { id: '7days', label: '7 ' + ($t('common.days') || 'أيام') },
                        ]"
                        :key="p.id"
                        @click="setPreset(p.id)"
                        class="px-3 py-1 rounded-xl font-bold transition cursor-pointer text-xs"
                        :class="filterForm.preset === p.id ? 'tab-theme-active shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                    >
                        {{ p.label }}
                    </button>
                </div>

                <!-- View Switcher -->
                <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-950 p-1 rounded-xl border border-slate-200 dark:border-slate-800">
                    <button
                        @click="viewMode = 'timeline'"
                        class="px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1"
                        :class="viewMode === 'timeline' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400'"
                    >
                        <span>⏱️</span>
                        <span>{{ $t('activity.view_timeline') }}</span>
                    </button>
                    <button
                        @click="viewMode = 'table'"
                        class="px-3 py-1 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1"
                        :class="viewMode === 'table' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400'"
                    >
                        <span>📋</span>
                        <span>{{ $t('activity.view_table') }}</span>
                    </button>
                </div>
            </div>

            <!-- TIMELINE VIEW -->
            <div v-if="viewMode === 'timeline'" class="space-y-3">
                <div
                    v-for="log in logs.data"
                    :key="log.id"
                    @click="selectedLog = log"
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-theme-primary rounded-3xl p-4.5 transition cursor-pointer shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3 group"
                >
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-lg shrink-0 mt-0.5 group-hover:border-theme-primary">
                            🛡️
                        </div>

                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="getActionBadge(log.action).class">
                                    {{ getActionBadge(log.action).label }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ getModuleBadge(log.module) }}
                                </span>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ log.user_name }}</span>
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">• {{ log.store_name }}</span>
                            </div>

                            <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed font-sans">
                                {{ log.description }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between md:justify-end gap-3 text-[11px] text-slate-500 dark:text-slate-400 font-mono shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-200 dark:border-slate-800">
                        <span class="bg-slate-100 dark:bg-slate-950 px-2 py-0.5 rounded-lg border border-slate-200 dark:border-slate-800/80 text-slate-700 dark:text-slate-300">{{ log.ip_address || '127.0.0.1' }}</span>
                        <span>{{ log.time_ago }} ({{ log.created_at }})</span>
                    </div>
                </div>

                <div v-if="logs.data.length === 0" class="py-16 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl text-slate-500 dark:text-slate-400 text-xs">
                    {{ $t('activity.no_logs_filter') }}
                </div>
            </div>

            <!-- TABLE VIEW -->
            <div v-else class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 overflow-x-auto shadow-xs">
                <table class="w-full text-right text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                            <th class="pb-3 font-mono">#</th>
                            <th class="pb-3">{{ $t('common.date') }} & {{ $t('common.time') }}</th>
                            <th class="pb-3">{{ $t('common.user') }}</th>
                            <th class="pb-3">{{ $t('common.store') }}</th>
                            <th class="pb-3">{{ $t('inventory.category') }}</th>
                            <th class="pb-3">{{ $t('common.actions') }}</th>
                            <th class="pb-3">{{ $t('common.notes') }}</th>
                            <th class="pb-3">{{ $t('activity.ip_address') }}</th>
                            <th class="pb-3 text-left">{{ $t('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 font-mono text-slate-400">{{ log.id }}</td>
                            <td class="py-3 font-mono text-slate-600 dark:text-slate-300">{{ log.created_at }}</td>
                            <td class="py-3 font-bold text-slate-900 dark:text-white font-tajawal">{{ log.user_name }}</td>
                            <td class="py-3 text-slate-600 dark:text-slate-300 font-tajawal">{{ log.store_name }}</td>
                            <td class="py-3 font-tajawal">
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ getModuleBadge(log.module) }}
                                </span>
                            </td>
                            <td class="py-3 font-tajawal">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="getActionBadge(log.action).class">
                                    {{ getActionBadge(log.action).label }}
                                </span>
                            </td>
                            <td class="py-3 text-slate-700 dark:text-slate-200 max-w-xs truncate">{{ log.description }}</td>
                            <td class="py-3 font-mono text-slate-500 dark:text-slate-400">{{ log.ip_address || '-' }}</td>
                            <td class="py-3 text-left font-tajawal">
                                <button
                                    @click="selectedLog = log"
                                    class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-theme-primary font-bold text-[11px] cursor-pointer"
                                >
                                    {{ $t('activity.inspect_btn') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="logs.data.length === 0" class="py-12 text-center text-slate-500 text-xs font-bold">
                    {{ $t('activity.no_logs_data') }}
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="logs.links?.length > 3" class="flex justify-center gap-1.5 pt-2 font-sans">
                <button
                    v-for="(link, lIdx) in logs.links"
                    :key="lIdx"
                    :disabled="!link.url || link.active"
                    @click="router.visit(link.url)"
                    v-html="link.label"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition font-mono"
                    :class="link.active ? 'tab-theme-active' : (link.url ? 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' : 'opacity-40 text-slate-400 dark:text-slate-600')"
                ></button>
            </div>

            <!-- Filter Drawer Component -->
            <FilterDrawer
                :isOpen="isFilterOpen"
                @close="isFilterOpen = false"
                @apply="applyFilters"
                @reset="resetFilters"
            >
                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('activity.search_filter_desc') }}</label>
                        <input
                            v-model="filterForm.search"
                            type="text"
                            :placeholder="$t('activity.search_filter_placeholder')"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('activity.module_filter') }}</label>
                        <select
                            v-model="filterForm.module"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-theme-primary focus:outline-none"
                        >
                            <option value="all">{{ $t('activity.all_modules') }}</option>
                            <option v-for="(lbl, mod) in modules_list" :key="mod" :value="mod">{{ lbl }}</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('activity.action_filter') }}</label>
                        <select
                            v-model="filterForm.action"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-theme-primary focus:outline-none"
                        >
                            <option value="all">{{ $t('activity.all_actions') }}</option>
                            <option value="created">{{ $t('activity.action_created') }}</option>
                            <option value="updated">{{ $t('activity.action_updated') }}</option>
                            <option value="deleted">{{ $t('activity.action_deleted') }}</option>
                            <option value="cancelled">{{ $t('activity.action_cancelled') }}</option>
                            <option value="login">{{ $t('activity.action_login') }}</option>
                            <option value="login_failed">{{ $t('activity.action_login_failed') }}</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('activity.user_filter') }}</label>
                        <select
                            v-model="filterForm.user_id"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-theme-primary focus:outline-none"
                        >
                            <option value="all">{{ $t('activity.all_users') }}</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.phone }})</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('activity.store_filter') }}</label>
                        <select
                            v-model="filterForm.store_id"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-theme-primary focus:outline-none"
                        >
                            <option value="all">{{ $t('activity.all_stores') }}</option>
                            <option v-for="s in stores" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <DatePicker v-model="filterForm.from" :label="$t('common.date_from')" />
                        <DatePicker v-model="filterForm.to" :label="$t('common.date_to')" />
                    </div>
                </div>
            </FilterDrawer>

            <!-- Inspection Modal -->
            <div v-if="selectedLog" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-xl w-full p-6 space-y-4 shadow-2xl text-slate-900 dark:text-white">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span>🔍 {{ $t('activity.log_details_title', { id: selectedLog.id }) }}</span>
                        </h3>
                        <button @click="selectedLog = null" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs dark:hover:text-white transition">✕</button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-950 p-3 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <div><span class="text-slate-500 dark:text-slate-400">{{ $t('common.user') }}:</span> <strong class="text-slate-900 dark:text-white">{{ selectedLog.user_name }}</strong></div>
                            <div><span class="text-slate-500 dark:text-slate-400">{{ $t('common.store') }}:</span> <strong class="text-slate-900 dark:text-white">{{ selectedLog.store_name }}</strong></div>
                            <div><span class="text-slate-500 dark:text-slate-400">{{ $t('common.date') }}:</span> <span class="text-slate-700 dark:text-slate-300 font-mono">{{ selectedLog.created_at }}</span></div>
                            <div><span class="text-slate-500 dark:text-slate-400">{{ $t('activity.ip_address') }}:</span> <span class="text-slate-700 dark:text-slate-300 font-mono">{{ selectedLog.ip_address || '-' }}</span></div>
                        </div>

                        <div>
                            <span class="text-slate-700 dark:text-slate-300 font-bold block mb-1">{{ $t('activity.full_description') }}</span>
                            <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200 font-sans leading-relaxed">
                                {{ selectedLog.description }}
                            </div>
                        </div>

                        <div v-if="selectedLog.user_agent">
                            <span class="text-slate-700 dark:text-slate-300 font-bold block mb-1">{{ $t('activity.user_agent') }}</span>
                            <div class="bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-mono text-[10px] break-all">
                                {{ selectedLog.user_agent }}
                            </div>
                        </div>

                        <div v-if="selectedLog.payload">
                            <span class="text-slate-700 dark:text-slate-300 font-bold block mb-1">{{ $t('activity.payload_data') }}</span>
                            <pre class="bg-slate-50 dark:bg-slate-950 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 text-theme-primary font-mono text-[10px] overflow-x-auto max-h-48">{{ JSON.stringify(selectedLog.payload, null, 2) }}</pre>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button
                            @click="selectedLog = null"
                            class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs cursor-pointer transition border border-slate-200 dark:border-slate-700"
                        >
                            {{ $t('common.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>