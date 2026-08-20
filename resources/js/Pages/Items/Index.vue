<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import { useMoney } from '@/Composables/useMoney';

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
    { id: 'all', name: 'كافة التصنيفات والأقسام' },
    ...props.categories.map(c => ({ id: c, name: c }))
]);

const stockStatusOptions = [
    { id: 'all', name: 'كافة حالات المخزون' },
    { id: 'low', name: 'الأصناف الحرجة والنواقص 🚨' },
    { id: 'out', name: 'أصناف نفدت من المخزن (رصيد 0) ❌' },
    { id: 'in_stock', name: 'أصناف متوفرة بالمخزن ✅' },
];

const statusOptions = [
    { id: 'all', name: 'الكل (نشط وغير نشط)' },
    { id: 'active', name: 'الأصناف النشطة للبيع فقط' },
    { id: 'inactive', name: 'الأصناف المعطلة مؤقتاً' },
];

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
    <Head title="دليل الأصناف والأسعار" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📦</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            دليل الأصناف والأسعار والمخزون
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        إدارة خامات وتوليفات البن، مستويات الأمان، وتكلفة وأسعار البيع
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        @click="openCreateModal"
                        type="button"
                        class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                    >
                        <span class="text-base font-black">+</span>
                        <span>إضافة صنف جديد</span>
                    </button>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">إجمالي الأصناف المسجلة</span>
                    <div class="text-2xl font-black font-mono text-white">
                        {{ metrics.total_items || 0 }} <span class="text-xs text-slate-500 font-tajawal">صنف</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">أصناف تحت حد الطلب (حرجة)</span>
                    <div class="text-2xl font-black font-mono text-rose-400 flex items-center gap-2">
                        <span>{{ metrics.low_stock_count || 0 }}</span>
                        <span v-if="metrics.low_stock_count > 0" class="text-xs px-2 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 font-tajawal">
                            انتبه للنواقص
                        </span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">القيمة التقديرية للمخزون (سعر التكلفة)</span>
                    <div class="text-2xl font-black font-mono text-emerald-400">
                        {{ formatMoney(metrics.total_stock_value) }} <span class="text-xs text-white">ج.م</span>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Quick Bar -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="... بحث باسم الصنف أو الباركود"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-2xl text-xs text-white placeholder:text-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                            🔍
                        </span>
                    </div>

                    <div class="w-full md:w-auto flex flex-wrap items-center justify-between md:justify-end gap-2">
                        <!-- Quick Stock Status Tabs -->
                        <div class="flex items-center gap-1 bg-slate-950/80 p-1 rounded-2xl border border-slate-800 text-xs">
                            <button
                                @click="stockStatus = 'all'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="stockStatus === 'all' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                            >
                                الكل
                            </button>
                            <button
                                @click="stockStatus = 'low'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="stockStatus === 'low' ? 'bg-rose-500 text-white font-black' : 'text-slate-400 hover:text-white'"
                            >
                                النواقص 🚨
                            </button>
                        </div>

                        <!-- Open Filter Drawer Button -->
                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                        >
                            <span>⚙️</span>
                            <span>تصفية وفلاتر متقدمة</span>
                            <span
                                v-if="activeFiltersCount > 0"
                                class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-mono font-black text-[11px] flex items-center justify-center"
                            >
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Active Filters Chips -->
                <div v-if="activeFiltersCount > 0" class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-800/80 text-xs">
                    <span class="text-slate-500 text-[11px] font-bold">الفلاتر النشطة:</span>

                    <span v-if="category !== 'all'" class="px-2.5 py-1 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center gap-1.5 font-bold">
                        <span>القسم: {{ category }}</span>
                        <button @click="category = 'all'; applyFilters();" class="hover:text-rose-400">✕</button>
                    </span>

                    <button @click="resetFilters" class="text-slate-400 hover:text-rose-400 text-xs underline font-bold mr-1">
                        مسح كافة الفلاتر
                    </button>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
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
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="item in items.data" :key="item.id" class="hover:bg-slate-800/30 transition">
                                <!-- Code -->
                                <td class="py-3.5 font-mono text-slate-400 text-[11px]">
                                    {{ item.code || '—' }}
                                </td>

                                <!-- Name -->
                                <td class="py-3.5">
                                    <div class="font-black text-white font-tajawal flex items-center gap-1.5">
                                        <span>{{ item.name }}</span>
                                        <span v-if="item.is_low_stock" class="px-1.5 py-0.2 rounded bg-rose-500/20 text-rose-400 text-[10px] font-bold">
                                            حرج
                                        </span>
                                    </div>
                                    <div v-if="item.notes" class="text-[10px] text-slate-500 truncate max-w-xs font-tajawal">{{ item.notes }}</div>
                                </td>

                                <!-- Category -->
                                <td class="py-3.5 font-tajawal">
                                    <span v-if="item.category" class="px-2 py-0.5 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 text-[11px]">
                                        {{ item.category }}
                                    </span>
                                    <span v-else class="text-slate-600">—</span>
                                </td>

                                <!-- Stock -->
                                <td class="py-3.5 font-mono font-bold">
                                    <span
                                        class="px-2.5 py-1 rounded-xl border font-black text-xs"
                                        :class="item.is_low_stock ? 'bg-rose-500/15 border-rose-500/30 text-rose-400' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400'"
                                    >
                                        {{ item.current_stock }} {{ item.unit }}
                                    </span>
                                </td>

                                <!-- Cost Price -->
                                <td class="py-3.5 font-mono text-slate-400">
                                    {{ formatMoney(item.cost_price) }}
                                </td>

                                <!-- Selling Price -->
                                <td class="py-3.5 font-mono font-black text-emerald-400 text-sm">
                                    {{ formatMoney(item.selling_price) }}
                                </td>

                                <!-- Min Stock -->
                                <td class="py-3.5 font-mono text-slate-400">
                                    {{ item.min_stock_level }} {{ item.unit }}
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 font-tajawal">
                                        <!-- Edit Button -->
                                        <button
                                            @click="openEditModal(item)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 transition cursor-pointer"
                                            title="تعديل بيانات وسعر الصنف"
                                        >
                                            ✏️
                                        </button>

                                        <!-- Delete Button -->
                                        <button
                                            @click="deleteItem(item)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition cursor-pointer"
                                            :class="!item.can_be_deleted ? 'opacity-40 cursor-not-allowed' : ''"
                                            :title="item.can_be_deleted ? 'حذف الصنف' : item.deletion_blockers.join(', ')"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!items.data || items.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">📦</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">لا توجد أصناف مطابقة للبحث</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="items.links && items.links.length > 3" class="pt-4 border-t border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        عرض {{ items.from || 0 }} إلى {{ items.to || 0 }} من إجمالي {{ items.total || 0 }} صنف
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
                    <label class="text-xs font-black text-slate-300">🔍 البحث بالاسم أو الباركود</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="... اكتب للبحث"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">🗂️ التصنيف أو القسم</label>
                    <SearchableSelect
                        v-model="category"
                        :options="categoryOptions"
                        placeholder="اختر التصنيف..."
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">🚨 حالة المخزون</label>
                    <SearchableSelect
                        v-model="stockStatus"
                        :options="stockStatusOptions"
                        placeholder="اختر حالة المخزون..."
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">⚙️ حالة البيع والنشاط</label>
                    <SearchableSelect
                        v-model="status"
                        :options="statusOptions"
                        placeholder="اختر الحالة..."
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
            <div @click.stop class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📦</span>
                        <h3 class="font-black text-base text-white">
                            {{ editingItem ? 'تعديل بيانات الصنف' : 'إضافة صنف جديد للدليل' }}
                        </h3>
                    </div>
                    <button @click="showItemModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <form @submit.prevent="saveItem" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">اسم الصنف *</label>
                        <input
                            v-model="itemForm.name"
                            type="text"
                            required
                            placeholder="مثال: بن برازيلي كولومبي وسط..."
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                        <p v-if="itemForm.errors.name" class="text-rose-400 text-[10px]">{{ itemForm.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">كود الصنف / الباركود</label>
                            <input
                                v-model="itemForm.code"
                                type="text"
                                placeholder="مثال: COF-001"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">القسم / التصنيف</label>
                            <input
                                v-model="itemForm.category"
                                type="text"
                                placeholder="مثال: بن مطحون / خامات"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">الوحدة *</label>
                            <select
                                v-model="itemForm.unit"
                                class="w-full px-3 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                                <option value="كجم">كيلوجرام (كجم)</option>
                                <option value="جرام">جرام</option>
                                <option value="قطعة">قطعة / علبة</option>
                                <option value="شيكارة">شيكارة / كرتونة</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">سعر التكلفة *</label>
                            <input
                                v-model="itemForm.cost_price"
                                type="number"
                                step="0.001"
                                required
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">سعر البيع *</label>
                            <input
                                v-model="itemForm.selling_price"
                                type="number"
                                step="0.001"
                                required
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">حد الأمان (الحد الأدنى للرصيد)</label>
                        <input
                            v-model="itemForm.min_stock_level"
                            type="number"
                            step="0.001"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">ملاحظات إضافية</label>
                        <textarea
                            v-model="itemForm.notes"
                            rows="2"
                            class="w-full px-3.5 py-2 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showItemModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            إلغاء
                        </button>
                        <button
                            type="submit"
                            :disabled="itemForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ itemForm.processing ? 'جاري الحفظ...' : (editingItem ? 'حفظ التعديلات' : 'إضافة الصنف') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
