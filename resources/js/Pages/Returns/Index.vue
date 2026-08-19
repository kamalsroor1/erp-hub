<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import { useMoney } from '@/Composables/useMoney';

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

const typeOptions = [
    { id: 'all', name: 'كافة المرتجعات' },
    { id: 'sales_return', name: 'مرتجع مبيعات من عميل ↩️' },
    { id: 'purchase_return', name: 'مرتجع مشتريات إلى مورد ↪️' },
];

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
    if (confirm(`هل أنت متأكد من أرشفة مستند المرتجع (${r.return_number})؟`)) {
        router.delete(`/returns/${r.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="سجل المرتجعات وإشعارات الخصم" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🔄</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            سجل المرتجعات وإشعارات الخصم والإرجاع
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        إدارة مرتجعات المبيعات من العملاء ومرتجعات المشتريات إلى الموردين وتعديل المخزون
                    </p>
                </div>

                <Link
                    href="/returns/create"
                    class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                >
                    <span class="text-base font-black">+</span>
                    <span>تسجيل مستند مرتجع جديد</span>
                </Link>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">إجمالي قيمة المرتجعات</span>
                    <div class="text-2xl font-black font-mono text-white">
                        {{ formatMoney(metrics.total_returns) }} <span class="text-xs text-amber-400">ج.م</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">مرتجعات مبيعات من عملاء</span>
                    <div class="text-2xl font-black font-mono text-rose-400">
                        {{ metrics.sales_returns_count || 0 }} <span class="text-xs text-slate-500 font-tajawal">مستند</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">مرتجعات مشتريات إلى موردين</span>
                    <div class="text-2xl font-black font-mono text-emerald-400">
                        {{ metrics.purchase_returns_count || 0 }} <span class="text-xs text-slate-500 font-tajawal">مستند</span>
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
                            placeholder="... بحث برقم المرتجع أو اسم الطرف أو السبب"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-2xl text-xs text-white placeholder:text-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                            🔍
                        </span>
                    </div>

                    <div class="w-full md:w-auto flex flex-wrap items-center justify-between md:justify-end gap-2">
                        <div class="flex items-center gap-1 bg-slate-950/80 p-1 rounded-2xl border border-slate-800 text-xs">
                            <button
                                @click="type = 'all'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="type === 'all' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                            >
                                الكل
                            </button>
                            <button
                                @click="type = 'sales_return'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="type === 'sales_return' ? 'bg-rose-500 text-white font-black' : 'text-slate-400 hover:text-white'"
                            >
                                مرتجع مبيعات ↩️
                            </button>
                            <button
                                @click="type = 'purchase_return'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="type === 'purchase_return' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                            >
                                مرتجع مشتريات ↪️
                            </button>
                        </div>

                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                        >
                            <span>⚙️</span>
                            <span>تصفية</span>
                            <span v-if="activeFiltersCount > 0" class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-mono font-black text-[11px] flex items-center justify-center">
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Returns Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">رقم المرتجع</th>
                                <th class="pb-3">النوع</th>
                                <th class="pb-3">الطرف المعني</th>
                                <th class="pb-3">التاريخ</th>
                                <th class="pb-3 font-mono">قيمة المرتجع</th>
                                <th class="pb-3">السبب</th>
                                <th class="pb-3 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="r in returns.data" :key="r.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 font-mono font-bold text-amber-400">
                                    {{ r.return_number }}
                                </td>

                                <td class="py-3.5">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold font-tajawal"
                                        :class="r.return_type === 'sales_return' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'"
                                    >
                                        {{ r.return_type === 'sales_return' ? 'مرتجع مبيعات ↩️' : 'مرتجع مشتريات ↪️' }}
                                    </span>
                                </td>

                                <td class="py-3.5 font-bold text-white font-tajawal">
                                    {{ r.party_name }}
                                </td>

                                <td class="py-3.5 font-mono text-slate-400 text-[11px]">
                                    {{ r.return_date }}
                                </td>

                                <td class="py-3.5 font-mono font-black text-white text-sm">
                                    {{ formatMoney(r.net_total) }} ج.م
                                </td>

                                <td class="py-3.5 font-tajawal text-slate-400 text-[11px]">
                                    {{ r.reason || '—' }}
                                </td>

                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 font-tajawal">
                                        <button
                                            @click="openDetailsModal(r)"
                                            type="button"
                                            class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition cursor-pointer"
                                        >
                                            تفاصيل ({{ r.items_count }})
                                        </button>

                                        <button
                                            @click="deleteReturn(r)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition cursor-pointer"
                                            title="أرشفة المرتجع"
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
                        <p class="text-xs font-bold text-slate-400 font-tajawal">لا توجد مستندات مرتجعات مطابقة للبحث</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="returns.links && returns.links.length > 3" class="pt-4 border-t border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        عرض {{ returns.from || 0 }} إلى {{ returns.to || 0 }} من إجمالي {{ returns.total || 0 }} مستند
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in returns.links" :key="lIdx">
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
                    <label class="text-xs font-black text-slate-300">🔍 البحث</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="... اكتب للبحث"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">🔄 نوع المرتجع</label>
                    <SearchableSelect
                        v-model="type"
                        :options="typeOptions"
                        placeholder="اختر النوع..."
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

        <!-- Return Details Modal -->
        <div
            v-if="showDetailsModal && selectedReturn"
            @click="showDetailsModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="font-black text-base text-white">تفاصيل المرتجع: {{ selectedReturn.return_number }}</h3>
                        <p class="text-xs text-amber-400 font-bold mt-0.5">{{ selectedReturn.party_name }} | {{ selectedReturn.return_date }}</p>
                    </div>
                    <button @click="showDetailsModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-2">الصنف المرتجع</th>
                                <th class="pb-2 font-mono">الكمية</th>
                                <th class="pb-2 font-mono">السعر</th>
                                <th class="pb-2 font-mono">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="it in selectedReturn.items" :key="it.id">
                                <td class="py-2.5 font-bold text-white font-tajawal">{{ it.item_name }}</td>
                                <td class="py-2.5 font-mono font-bold text-amber-400">{{ it.quantity }}</td>
                                <td class="py-2.5 font-mono text-slate-300">{{ formatMoney(it.unit_price) }}</td>
                                <td class="py-2.5 font-mono font-black text-emerald-400">{{ formatMoney(it.subtotal) }} ج.م</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-slate-950/80 rounded-2xl border border-slate-800 flex items-center justify-between font-mono">
                    <span class="text-xs text-slate-400 font-tajawal">إجمالي قيمة المستند:</span>
                    <span class="text-lg font-black text-amber-400">{{ formatMoney(selectedReturn.net_total) }} ج.م</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>