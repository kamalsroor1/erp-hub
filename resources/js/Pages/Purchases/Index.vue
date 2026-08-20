<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    purchases: { type: Object, required: true },
    metrics: { type: Object, default: () => ({}) },
    suppliers: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const supplierId = ref(props.filters.supplier_id || 'all');
const dateFrom = ref(props.filters.from || '');
const dateTo = ref(props.filters.to || '');
const isDrawerOpen = ref(false);

const statusOptions = [
    { id: 'all', name: 'كافة الحالات' },
    { id: 'confirmed', name: 'مؤكدة ومستلمة بالمخزن 🟢' },
    { id: 'cancelled', name: 'ملغاة 🔴' },
];

const supplierOptions = computed(() => {
    return [
        { id: 'all', name: 'كافة الموردين' },
        ...props.suppliers
    ];
});

const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (status.value !== 'all') count++;
    if (supplierId.value && supplierId.value !== 'all') count++;
    if (dateFrom.value || dateTo.value) count++;
    return count;
});

const applyFilters = () => {
    router.get('/purchases', {
        search: search.value || undefined,
        status: status.value !== 'all' ? status.value : undefined,
        supplier_id: supplierId.value !== 'all' ? supplierId.value : undefined,
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
    status.value = 'all';
    supplierId.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

// Details Modal
const showDetailsModal = ref(false);
const selectedPurchase = ref(null);

const openDetailsModal = (p) => {
    selectedPurchase.value = p;
    showDetailsModal.value = true;
};

const cancelPurchase = (p) => {
    if (confirm(`هل أنت متأكد من إلغاء فاتورة الشراء (${p.purchase_number})؟\nسيتم خصم الكميات من المخزن وعكس المستحقات المالية للمورد.`)) {
        router.post(`/purchases/${p.id}/cancel`, {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="سجل فواتير المشتريات والتوريدات" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📦</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            سجل فواتير المشتريات وتوريد خامات البن
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        إدارة عمليات الشراء من الموردين، حساب تكاليف الإنزال والنقل، وزيادة المخزون
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <Link
                        href="/purchases/smart-reorder"
                        class="h-11 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 text-xs font-bold flex items-center gap-1.5 transition"
                    >
                        <span>🧠</span>
                        <span>اقتراح إعادة الطلب الذكي</span>
                    </Link>

                    <Link
                        href="/purchases/create"
                        class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                    >
                        <span class="text-base font-black">+</span>
                        <span>فاتورة شراء جديدة</span>
                    </Link>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">إجمالي المشتريات المؤكدة</span>
                    <div class="text-2xl font-black font-mono text-white">
                        {{ formatMoney(metrics.total_purchases) }} <span class="text-xs text-amber-400">ج.م</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">عدد فواتير التوريد المعتمدة</span>
                    <div class="text-2xl font-black font-mono text-emerald-400">
                        {{ metrics.confirmed_count || 0 }} <span class="text-xs text-slate-500 font-tajawal">فاتورة</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">مستحقات آجلة للموردين (متبقي سداده)</span>
                    <div class="text-2xl font-black font-mono text-rose-400">
                        {{ formatMoney(metrics.unpaid_total) }} <span class="text-xs text-white">ج.م</span>
                    </div>
                </div>
            </div>

            <!-- Quick Filter Bar -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="... بحث برقم الفاتورة أو اسم المورد"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-2xl text-xs text-white placeholder:text-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                            🔍
                        </span>
                    </div>

                    <div class="w-full md:w-auto flex flex-wrap items-center justify-between md:justify-end gap-2">
                        <div class="flex items-center gap-1 bg-slate-950/80 p-1 rounded-2xl border border-slate-800 text-xs">
                            <button
                                @click="status = 'all'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="status === 'all' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                            >
                                الكل
                            </button>
                            <button
                                @click="status = 'confirmed'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="status === 'confirmed' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                            >
                                معتمدة 🟢
                            </button>
                            <button
                                @click="status = 'cancelled'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="status === 'cancelled' ? 'bg-rose-500 text-white font-black' : 'text-slate-400 hover:text-white'"
                            >
                                ملغاة 🔴
                            </button>
                        </div>

                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                        >
                            <span>⚙️</span>
                            <span>تصفية متقدمة</span>
                            <span v-if="activeFiltersCount > 0" class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-mono font-black text-[11px] flex items-center justify-center">
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Purchases Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('invoices.invoice_number') }}</th>
                                <th class="pb-3">{{ $t('purchases.supplier') }}</th>
                                <th class="pb-3">{{ $t('common.date') }}</th>
                                <th class="pb-3 font-mono">{{ $t('purchases.total_cost') }}</th>
                                <th class="pb-3 font-mono">{{ $t('common.paid') }}</th>
                                <th class="pb-3 font-mono">{{ $t('common.remaining') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.status') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="p in purchases.data" :key="p.id" class="hover:bg-slate-800/30 transition">
                                <!-- Number -->
                                <td class="py-3.5 font-mono font-bold text-amber-400">
                                    {{ p.purchase_number }}
                                </td>

                                <!-- Supplier -->
                                <td class="py-3.5">
                                    <div class="font-black text-white font-tajawal">{{ p.supplier_name }}</div>
                                    <div v-if="p.company_name" class="text-[10px] text-slate-400 font-tajawal">{{ p.company_name }}</div>
                                </td>

                                <!-- Date -->
                                <td class="py-3.5 font-mono text-slate-300 text-[11px]">
                                    {{ p.purchase_date }}
                                </td>

                                <!-- Net Total -->
                                <td class="py-3.5 font-mono font-black text-white">
                                    {{ formatMoney(p.net_total) }} ج.م
                                </td>

                                <!-- Paid -->
                                <td class="py-3.5 font-mono font-bold text-emerald-400">
                                    {{ formatMoney(p.paid_amount) }}
                                </td>

                                <!-- Remaining -->
                                <td class="py-3.5 font-mono font-bold text-rose-400">
                                    {{ p.remaining_amount > 0 ? formatMoney(p.remaining_amount) : '—' }}
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 text-center">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold font-tajawal"
                                        :class="p.status === 'confirmed' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30'"
                                    >
                                        {{ p.status === 'confirmed' ? 'مؤكدة ومستلمة' : 'ملغاة' }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 font-tajawal">
                                        <!-- View Details -->
                                        <button
                                            @click="openDetailsModal(p)"
                                            type="button"
                                            class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition cursor-pointer"
                                        >
                                            تفاصيل ({{ p.items_count }})
                                        </button>

                                        <!-- Cancel -->
                                        <button
                                            v-if="p.status === 'confirmed'"
                                            @click="cancelPurchase(p)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition cursor-pointer"
                                            title="إلغاء فاتورة الشراء وعكس المخزون"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!purchases.data || purchases.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">📦</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">لا توجد فواتير مشتريات مسجلة مطابقة للبحث</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="purchases.links && purchases.links.length > 3" class="pt-4 border-t border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        عرض {{ purchases.from || 0 }} إلى {{ purchases.to || 0 }} من إجمالي {{ purchases.total || 0 }} فاتورة
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in purchases.links" :key="lIdx">
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
                    <label class="text-xs font-black text-slate-300">🔍 البحث بالرقم أو المورد</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="... اكتب للبحث"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">🏭 تصفية حسب المورد</label>
                    <SearchableSelect
                        v-model="supplierId"
                        :options="supplierOptions"
                        placeholder="اختر المورد..."
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">🟢 حالة الفاتورة</label>
                    <SearchableSelect
                        v-model="status"
                        :options="statusOptions"
                        placeholder="اختر الحالة..."
                    />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-300">من تاريخ</label>
                        <DatePicker v-model="dateFrom" placeholder="من..." />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-300">إلى تاريخ</label>
                        <DatePicker v-model="dateTo" placeholder="إلى..." />
                    </div>
                </div>
            </div>
        </FilterDrawer>

        <!-- Purchase Details Modal -->
        <div
            v-if="showDetailsModal && selectedPurchase"
            @click="showDetailsModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="font-black text-base text-white">تفاصيل فاتورة المشتريات: {{ selectedPurchase.purchase_number }}</h3>
                        <p class="text-xs text-amber-400 font-bold mt-0.5">المورد: {{ selectedPurchase.supplier_name }} | التاريخ: {{ selectedPurchase.purchase_date }}</p>
                    </div>
                    <button @click="showDetailsModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <!-- Items list -->
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-2">الصنف المستلم</th>
                                <th class="pb-2 font-mono">الكمية</th>
                                <th class="pb-2 font-mono">سعر الوحدة</th>
                                <th class="pb-2 font-mono">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="it in selectedPurchase.items" :key="it.id">
                                <td class="py-2.5 font-bold text-white font-tajawal">{{ it.item_name }}</td>
                                <td class="py-2.5 font-mono font-bold text-amber-400">{{ it.quantity }}</td>
                                <td class="py-2.5 font-mono text-slate-300">{{ formatMoney(it.unit_cost) }}</td>
                                <td class="py-2.5 font-mono font-black text-emerald-400">{{ formatMoney(it.subtotal) }} ج.م</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-slate-950/80 rounded-2xl border border-slate-800 flex items-center justify-between font-mono">
                    <span class="text-xs text-slate-400 font-tajawal">صافي إجمالي الفاتورة:</span>
                    <span class="text-lg font-black text-amber-400">{{ formatMoney(selectedPurchase.net_total) }} ج.م</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>