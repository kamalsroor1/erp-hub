<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';
import {
    Package,
    Plus,
    AlertTriangle,
    Search,
    Filter,
    Pencil,
    Trash2,
    History,
    X,
    Check,
    Boxes,
    Barcode,
    FolderTree
} from 'lucide-vue-next';

const props = defineProps({
    items: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    metrics: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

// Search & Filter state
const search = ref(props.filters.search || '');
const category = ref(props.filters.category || 'all');
const stockStatus = ref(props.filters.stock_status || 'all');
const status = ref(props.filters.status || 'all');

const isDrawerOpen = ref(false);

const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (category.value && category.value !== 'all') count++;
    if (stockStatus.value && stockStatus.value !== 'all') count++;
    if (status.value && status.value !== 'all') count++;
    return count;
});

// Category Options for SearchableSelect
const categoryOptions = computed(() => [
    { id: 'all', name: trans('inventory.all_categories') || 'كافة التصنيفات والأقسام' },
    ...props.categories.map(c => ({ id: c, name: c }))
]);

const stockStatusOptions = computed(() => [
    { id: 'all', name: trans('inventory.all_stock') || 'كافة حالات المخزون' },
    { id: 'low', name: trans('inventory.low_stock_only') || 'الأصناف الحرجة والنواقص' },
    { id: 'out', name: trans('inventory.out_of_stock_only') || 'أصناف نفدت من المخزن (رصيد 0)' },
    { id: 'in_stock', name: trans('inventory.available_only') || 'أصناف متوفرة بالمخزن' },
]);

const statusOptions = computed(() => [
    { id: 'all', name: trans('common.all') || 'الكل' },
    { id: 'active', name: trans('common.active') || 'الأصناف النشطة' },
    { id: 'inactive', name: trans('common.inactive') || 'الأصناف المعطلة' },
]);

const applyFilters = () => {
    router.get('/items', {
        search: search.value || undefined,
        category: category.value !== 'all' ? category.value : undefined,
        stock_status: stockStatus.value !== 'all' ? stockStatus.value : undefined,
        status: status.value !== 'all' ? status.value : undefined,
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
    category.value = 'all';
    stockStatus.value = 'all';
    status.value = 'all';
    applyFilters();
};

// Add / Edit Modal State
const showItemModal = ref(false);
const editingItem = ref(null);

const itemForm = useForm({
    name: '',
    code: '',
    category: '',
    unit: 'كجم',
    cost_price: '',
    selling_price: '',
    min_stock_level: '5.000',
    notes: '',
});

const openCreateModal = () => {
    editingItem.value = null;
    itemForm.reset();
    itemForm.clearErrors();
    showItemModal.value = true;
};

const openEditModal = (item) => {
    editingItem.value = item;
    itemForm.clearErrors();
    itemForm.name = item.name;
    itemForm.code = item.code || '';
    itemForm.category = item.category || '';
    itemForm.unit = item.unit || 'كجم';
    itemForm.cost_price = item.cost_price;
    itemForm.selling_price = item.selling_price;
    itemForm.min_stock_level = item.min_stock_level;
    itemForm.notes = item.notes || '';
    showItemModal.value = true;
};

const saveItem = () => {
    if (editingItem.value) {
        itemForm.put(`/items/${editingItem.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showItemModal.value = false;
            }
        });
    } else {
        itemForm.post('/items', {
            preserveScroll: true,
            onSuccess: () => {
                showItemModal.value = false;
            }
        });
    }
};

const deleteItem = (item) => {
    if (!item.can_be_deleted) {
        alert('لا يمكن حذف الصنف:\n- ' + item.deletion_blockers.join('\n- '));
        return;
    }
    if (confirm(`هل أنت متأكد من حذف الصنف (${item.name})؟`)) {
        router.delete(`/items/${item.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="$t('inventory.title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Package class="w-6 h-6 text-theme-primary" />
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            {{ $t('inventory.title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                        {{ $t('inventory.subtitle') }}
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        @click="openCreateModal"
                        type="button"
                        class="h-11 px-5 rounded-2xl btn-primary-theme font-bold text-xs flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer"
                    >
                        <Plus class="w-4 h-4" />
                        <span>{{ $t('inventory.add_new_item') }}</span>
                    </button>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('inventory.total_items_count') }}</span>
                    <div class="text-2xl font-black font-mono text-slate-900 dark:text-white flex items-center gap-2">
                        <Package class="w-5 h-5 text-theme-primary" />
                        <span>{{ metrics.total_items || 0 }}</span>
                        <span class="text-xs text-slate-400 font-tajawal">{{ $t('inventory.item_unit') }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('inventory.low_stock_count') }}</span>
                    <div class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400 flex items-center gap-2">
                        <AlertTriangle class="w-5 h-5 text-rose-500" />
                        <span>{{ metrics.low_stock_count || 0 }}</span>
                        <span v-if="metrics.low_stock_count > 0" class="text-xs px-2 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 font-tajawal">
                            {{ $t('inventory.low_stock_warning') }}
                        </span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('inventory.total_inventory_value') }}</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                        <Boxes class="w-5 h-5 text-emerald-500" />
                        <span>{{ formatMoney(metrics.total_stock_value) }}</span>
                        <span class="text-xs text-slate-700 dark:text-white">{{ $t('common.currency') }}</span>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Quick Bar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-xs space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="$t('inventory.search_item_placeholder')"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-theme-primary focus:outline-none transition shadow-inner"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 pointer-events-none">
                            <Search class="w-4 h-4" />
                        </span>
                    </div>

                    <div class="w-full md:w-auto flex flex-wrap items-center justify-between md:justify-end gap-2">
                        <!-- Quick Stock Status Tabs -->
                        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-950/80 p-1 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs">
                            <button
                                @click="stockStatus = 'all'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="stockStatus === 'all' ? 'tab-theme-active' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('common.all') }}
                            </button>
                            <button
                                @click="stockStatus = 'low'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="stockStatus === 'low' ? 'bg-rose-500 text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('inventory.low_stock_only') }}
                            </button>
                        </div>

                        <!-- Open Filter Drawer Button -->
                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer shadow-xs"
                        >
                            <Filter class="w-4 h-4" />
                            <span>{{ $t('common.filter') }}</span>
                            <span
                                v-if="activeFiltersCount > 0"
                                class="w-5 h-5 rounded-full bg-theme-primary text-white font-mono font-black text-[11px] flex items-center justify-center"
                            >
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Active Filters Chips -->
                <div v-if="activeFiltersCount > 0" class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-200 dark:border-slate-800/80 text-xs">
                    <span class="text-slate-500 text-[11px] font-bold">{{ $t('dashboard.quick_filter') || 'الفلاتر النشطة' }}:</span>

                    <span v-if="category !== 'all'" class="px-2.5 py-1 rounded-xl bg-theme-light border border-theme-light text-theme-primary flex items-center gap-1.5 font-bold">
                        <span>{{ $t('inventory.category') }}: {{ category }}</span>
                        <button @click="category = 'all'; applyFilters();" class="hover:text-rose-400 cursor-pointer">
                            <X class="w-3 h-3" />
                        </button>
                    </span>

                    <button @click="resetFilters" class="text-slate-500 hover:text-rose-500 dark:text-slate-400 dark:hover:text-rose-400 text-xs underline font-bold mr-1 cursor-pointer">
                        {{ $t('common.clear_all') || 'مسح كافة الفلاتر' }}
                    </button>
                </div>
            </div>

            <!-- Items Table & Mobile Cards -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-xs space-y-4 overflow-hidden">
                <!-- Desktop Table (Hidden on Mobile) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('inventory.item_code') }}</th>
                                <th class="pb-3">{{ $t('inventory.item_name') }}</th>
                                <th class="pb-3">{{ $t('inventory.category') }}</th>
                                <th class="pb-3 font-mono">{{ $t('inventory.current_stock') }}</th>
                                <th class="pb-3 font-mono">{{ $t('common.unit_cost') }}</th>
                                <th class="pb-3 font-mono">{{ $t('common.unit_price') }}</th>
                                <th class="pb-3 font-mono">{{ $t('inventory.min_stock_level') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                            <tr v-for="item in items.data" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <!-- Code -->
                                <td class="py-3.5 font-mono text-slate-500 dark:text-slate-400 text-[11px]">
                                    {{ item.code || '—' }}
                                </td>

                                <!-- Name -->
                                <td class="py-3.5">
                                    <div class="font-black text-slate-900 dark:text-white font-tajawal flex items-center gap-1.5">
                                        <span>{{ item.name }}</span>
                                        <span v-if="item.is_low_stock" class="px-1.5 py-0.2 rounded bg-rose-500/20 text-rose-600 dark:text-rose-400 text-[10px] font-bold">
                                            {{ $t('inventory.low_stock_only') }}
                                        </span>
                                    </div>
                                    <div v-if="item.notes" class="text-[10px] text-slate-500 truncate max-w-xs font-tajawal">{{ item.notes }}</div>
                                </td>

                                <!-- Category -->
                                <td class="py-3.5 font-tajawal">
                                    <span v-if="item.category" class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-[11px]">
                                        {{ item.category }}
                                    </span>
                                    <span v-else class="text-slate-400 dark:text-slate-600">—</span>
                                </td>

                                <!-- Stock -->
                                <td class="py-3.5 font-mono font-bold">
                                    <span
                                        class="px-2.5 py-1 rounded-xl border font-black text-xs"
                                        :class="item.is_low_stock ? 'bg-rose-500/15 border-rose-500/30 text-rose-600 dark:text-rose-400' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400'"
                                    >
                                        {{ item.current_stock }} {{ item.unit }}
                                    </span>
                                </td>

                                <!-- Cost Price -->
                                <td class="py-3.5 font-mono text-slate-500 dark:text-slate-400">
                                    {{ formatMoney(item.cost_price) }}
                                </td>

                                <!-- Selling Price -->
                                <td class="py-3.5 font-mono font-black text-emerald-600 dark:text-emerald-400 text-sm">
                                    {{ formatMoney(item.selling_price) }}
                                </td>

                                <!-- Min Stock -->
                                <td class="py-3.5 font-mono text-slate-500 dark:text-slate-400">
                                    {{ item.min_stock_level }} {{ item.unit }}
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 font-tajawal">
                                        <!-- Movements Link -->
                                        <Link
                                            :href="`/items/${item.id}/movements`"
                                            class="p-1.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 transition"
                                            :title="$t('inventory.view_movements')"
                                        >
                                            <History class="w-3.5 h-3.5" />
                                        </Link>

                                        <!-- Edit Button -->
                                        <button
                                            @click="openEditModal(item)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 transition cursor-pointer"
                                            :title="$t('common.edit')"
                                        >
                                            <Pencil class="w-3.5 h-3.5" />
                                        </button>

                                        <!-- Delete Button -->
                                        <button
                                            @click="deleteItem(item)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 transition cursor-pointer"
                                            :class="!item.can_be_deleted ? 'opacity-40 cursor-not-allowed' : ''"
                                            :title="item.can_be_deleted ? $t('common.delete') : item.deletion_blockers.join(', ')"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards View (Visible on Small Screens) -->
                <div class="md:hidden space-y-3">
                    <div
                        v-for="item in items.data"
                        :key="item.id"
                        class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 space-y-3 shadow-xs font-tajawal"
                    >
                        <!-- Top Row: Name + Code + Badge -->
                        <div class="flex items-start justify-between gap-2 border-b border-slate-200 dark:border-slate-800/80 pb-2.5">
                            <div class="space-y-1">
                                <div class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span>{{ item.name }}</span>
                                    <span v-if="item.is_low_stock" class="px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-600 dark:text-rose-400 text-[10px] font-bold">
                                        {{ $t('inventory.low_stock_only') }}
                                    </span>
                                </div>
                                <p v-if="item.code" class="text-[11px] text-slate-400 font-mono" dir="ltr">#{{ item.code }}</p>
                            </div>

                            <span v-if="item.category" class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-[11px]">
                                {{ item.category }}
                            </span>
                        </div>

                        <!-- Metrics Grid -->
                        <div class="grid grid-cols-3 gap-2 p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center font-mono">
                            <div>
                                <span class="text-[10px] text-slate-400 font-tajawal block">{{ $t('inventory.current_stock') }}</span>
                                <span
                                    class="text-xs font-black"
                                    :class="item.is_low_stock ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'"
                                >
                                    {{ item.current_stock }} {{ item.unit }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-tajawal block">{{ $t('common.unit_cost') }}</span>
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ formatMoney(item.cost_price) }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-tajawal block">{{ $t('common.unit_price') }}</span>
                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">{{ formatMoney(item.selling_price) }}</span>
                            </div>
                        </div>

                        <!-- Mobile Card Action Bar -->
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-[11px] text-slate-400 font-mono">
                                {{ $t('inventory.min_stock_level') }}: {{ item.min_stock_level }} {{ item.unit }}
                            </span>

                            <div class="flex items-center gap-1.5">
                                <Link
                                    :href="`/items/${item.id}/movements`"
                                    class="p-2 rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30"
                                    :title="$t('inventory.view_movements')"
                                >
                                    <History class="w-4 h-4" />
                                </Link>

                                <button
                                    @click="openEditModal(item)"
                                    type="button"
                                    class="p-2 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 cursor-pointer"
                                    :title="$t('common.edit')"
                                >
                                    <Pencil class="w-4 h-4" />
                                </button>

                                <button
                                    @click="deleteItem(item)"
                                    type="button"
                                    class="p-2 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30 cursor-pointer"
                                    :class="!item.can_be_deleted ? 'opacity-40 cursor-not-allowed' : ''"
                                    :title="item.can_be_deleted ? $t('common.delete') : item.deletion_blockers.join(', ')"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!items.data || items.data.length === 0" class="py-16 text-center space-y-3">
                    <Package class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-700" />
                    <p class="text-xs font-bold text-slate-400 font-tajawal">{{ $t('inventory.no_items_found') }}</p>
                </div>

                <!-- Pagination -->
                <div v-if="items.links && items.links.length > 3" class="pt-4 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        {{ $t('common.actions') ? `عرض ${items.from || 0} إلى ${items.to || 0} من إجمالي ${items.total || 0}` : `Showing ${items.from || 0} to ${items.to || 0} of ${items.total || 0}` }}
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in items.links" :key="lIdx">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                :class="link.active ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-3 py-1.5 rounded-xl text-xs text-slate-600 font-bold"
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
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <Search class="w-3.5 h-3.5" />
                        <span>{{ $t('inventory.search_item_placeholder') }}</span>
                    </label>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="$t('common.search')"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <FolderTree class="w-3.5 h-3.5" />
                        <span>{{ $t('inventory.category') }}</span>
                    </label>
                    <SearchableSelect
                        v-model="category"
                        :options="categoryOptions"
                        :placeholder="$t('inventory.all_categories')"
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <AlertTriangle class="w-3.5 h-3.5" />
                        <span>{{ $t('inventory.stock_status') }}</span>
                    </label>
                    <SearchableSelect
                        v-model="stockStatus"
                        :options="stockStatusOptions"
                        :placeholder="$t('inventory.all_stock')"
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <Filter class="w-3.5 h-3.5" />
                        <span>{{ $t('common.status') }}</span>
                    </label>
                    <SearchableSelect
                        v-model="status"
                        :options="statusOptions"
                        :placeholder="$t('common.all')"
                    />
                </div>
            </div>
        </FilterDrawer>

        <!-- Add / Edit Item Modal -->
        <div
            v-if="showItemModal"
            @click="showItemModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <Package class="w-5 h-5 text-theme-primary" />
                        <h3 class="font-black text-base text-slate-900 dark:text-white">
                            {{ editingItem ? $t('inventory.item_updated') : $t('inventory.add_new_item') }}
                        </h3>
                    </div>
                    <button @click="showItemModal = false" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-400 text-xs hover:text-slate-900 dark:hover:text-white cursor-pointer flex items-center justify-center">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="saveItem" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.item_name') }} *</label>
                        <input
                            v-model="itemForm.name"
                            type="text"
                            required
                            :placeholder="$t('inventory.item_name')"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none"
                        >
                        <p v-if="itemForm.errors.name" class="text-rose-400 text-[10px]">{{ itemForm.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.item_code') }} / {{ $t('inventory.barcode') }}</label>
                            <input
                                v-model="itemForm.code"
                                type="text"
                                :placeholder="$t('inventory.barcode_placeholder')"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.category') }}</label>
                            <input
                                v-model="itemForm.category"
                                type="text"
                                :placeholder="$t('inventory.category_placeholder')"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.unit') }} *</label>
                            <select
                                v-model="itemForm.unit"
                                class="w-full px-3 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none"
                            >
                                <option value="كجم">{{ $t('inventory.unit_weight_short') }} (كجم)</option>
                                <option value="جرام">جرام</option>
                                <option value="قطعة">{{ $t('inventory.unit_piece_short') }}</option>
                                <option value="شيكارة">شيكارة / كرتونة</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.purchase_price') }} *</label>
                            <input
                                v-model="itemForm.cost_price"
                                type="number"
                                step="0.001"
                                required
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.retail_price') }} *</label>
                            <input
                                v-model="itemForm.selling_price"
                                type="number"
                                step="0.001"
                                required
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.min_stock_level') }}</label>
                        <input
                            v-model="itemForm.min_stock_level"
                            type="number"
                            step="0.001"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white font-mono focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('common.notes') }}</label>
                        <textarea
                            v-model="itemForm.notes"
                            rows="2"
                            class="w-full px-3.5 py-2 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button
                            @click="showItemModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="itemForm.processing"
                            class="px-5 py-2.5 rounded-2xl btn-primary-theme text-xs font-black transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ itemForm.processing ? '...' : (editingItem ? $t('common.save') : $t('inventory.add_new_item')) }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
