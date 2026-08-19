<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';

const props = defineProps({
    invoices: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    stores: { type: Array, default: () => [] },
});

const { formatMoney } = useMoney();

// Search & Filter state
const search = ref(props.filters.search || '');
const storeId = ref(props.filters.store_id || 'all');
const paymentType = ref(props.filters.payment_type || 'all');
const paymentMethod = ref(props.filters.payment_method || 'all');
const status = ref(props.filters.status || 'active');
const dateFrom = ref(props.filters.from || '');
const dateTo = ref(props.filters.to || '');

// Filter Drawer State
const isDrawerOpen = ref(false);

// Active filters count calculator
const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (storeId.value && storeId.value !== 'all') count++;
    if (paymentType.value && paymentType.value !== 'all') count++;
    if (paymentMethod.value && paymentMethod.value !== 'all') count++;
    if (status.value && status.value !== 'active') count++;
    if (dateFrom.value) count++;
    if (dateTo.value) count++;
    return count;
});

// Store Options formatted for SearchableSelect
const storeOptions = computed(() => [
    { id: 'all', name: '🏬 كافة الفروع ونقاط البيع' },
    ...props.stores.map(s => ({
        id: s.id,
        name: `${s.type === 'van' ? '🚐' : '🏬'} ${s.name}`,
        type: s.type,
    }))
]);

// Payment Types formatted for SearchableSelect
const paymentTypeOptions = [
    { id: 'all', name: 'الكل (كافة أنواع السداد)' },
    { id: 'cash', name: 'كاش 💵' },
    { id: 'credit', name: 'آجل ⏳' },
    { id: 'partial', name: 'جزئي 📊' },
];

const paymentMethodOptions = [
    { id: 'all', name: 'كافة وسائل التحصيل' },
    { id: 'cash', name: 'نقدي (كاش)' },
    { id: 'instapay', name: 'إستاباي ⚡' },
    { id: 'wallet', name: 'محفظة إلكترونية 📱' },
    { id: 'bank', name: 'تحويل بنكي 🏦' },
];

const statusOptions = [
    { id: 'active', name: 'الفواتير النشطة (غير المحذوفة)' },
    { id: 'confirmed', name: 'الفواتير المعتمدة فقط' },
    { id: 'cancelled', name: 'الفواتير الملغاة' },
    { id: 'trash', name: 'سلة المحذوفات' },
];

// Apply Filters to Inertia
const applyFilters = () => {
    router.get('/invoices', {
        search: search.value || undefined,
        store_id: storeId.value !== 'all' ? storeId.value : undefined,
        payment_type: paymentType.value !== 'all' ? paymentType.value : undefined,
        payment_method: paymentMethod.value !== 'all' ? paymentMethod.value : undefined,
        status: status.value !== 'active' ? status.value : undefined,
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

// Debounce search
let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        applyFilters();
    }, 400);
});

const resetFilters = () => {
    search.value = '';
    storeId.value = 'all';
    paymentType.value = 'all';
    paymentMethod.value = 'all';
    status.value = 'active';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

const getPaymentBadge = (inv) => {
    if (inv.payment_type === 'cash') {
        if (inv.payment_method === 'instapay') return { label: 'إستاباي ⚡', class: 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30' };
        if (inv.payment_method === 'wallet') return { label: 'محفظة 📱', class: 'bg-teal-500/15 text-teal-400 border-teal-500/30' };
        return { label: 'كاش 💵', class: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' };
    }
    if (inv.payment_type === 'credit') return { label: 'آجل', class: 'bg-rose-500/15 text-rose-400 border-rose-500/30' };
    if (inv.payment_type === 'partial') return { label: 'جزئي', class: 'bg-amber-500/15 text-amber-400 border-amber-500/30' };
    return { label: inv.payment_type, class: 'bg-slate-800 text-slate-400 border-slate-700' };
};

const getStatusBadge = (st) => {
    if (st === 'confirmed') return { label: 'معتمدة', class: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' };
    if (st === 'cancelled') return { label: 'ملغاة', class: 'bg-rose-500/10 text-rose-400 border-rose-500/20' };
    return { label: st, class: 'bg-slate-800 text-slate-400 border-slate-700' };
};

const printThermal = (id) => {
    window.open(`/invoices/${id}/print/thermal`, '_blank', 'width=400,height=600');
};

const printA4 = (id) => {
    window.open(`/invoices/${id}/print/a4`, '_blank', 'width=900,height=800');
};
</script>

<template>
    <Head :title="$t('nav.invoices_log')" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🧾</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white font-tajawal">
                            {{ $t('nav.invoices_log') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        متابعة الفواتير المعتمدة، حالات السداد والتحصيل، وإلغاء الفواتير وفق الأصول المحاسبية
                    </p>
                </div>

                <Link
                    href="/pos"
                    class="h-11 px-5 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20 transition transform active:scale-95 font-tajawal cursor-pointer"
                >
                    <span class="text-base font-black">+</span>
                    <span>فاتورة بيع جديدة (F2)</span>
                </Link>
            </div>

            <!-- Quick Action Bar & Drawer Toggle -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm font-tajawal space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <!-- Quick Search Input -->
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="... بحث سريع برقم الفاتورة أو اسم العميل"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-2xl text-xs text-white placeholder:text-slate-500 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                            🔍
                        </span>
                    </div>

                    <!-- Filter Pills & Drawer Toggle Button -->
                    <div class="w-full md:w-auto flex flex-wrap items-center justify-between md:justify-end gap-2">
                        <!-- Quick Payment Tabs -->
                        <div class="flex items-center gap-1 bg-slate-950/80 p-1 rounded-2xl border border-slate-800 text-xs">
                            <button
                                @click="paymentType = 'all'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="paymentType === 'all' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                            >
                                الكل
                            </button>
                            <button
                                @click="paymentType = 'cash'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="paymentType === 'cash' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                            >
                                كاش 💵
                            </button>
                            <button
                                @click="paymentType = 'credit'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="paymentType === 'credit' ? 'bg-rose-500 text-white font-black' : 'text-slate-400 hover:text-white'"
                            >
                                آجل
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

                <!-- Active Filters Chips List (Removable) -->
                <div v-if="activeFiltersCount > 0" class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-800/80 text-xs">
                    <span class="text-slate-500 text-[11px] font-bold">الفلاتر النشطة:</span>

                    <span v-if="storeId !== 'all'" class="px-2.5 py-1 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center gap-1.5 font-bold">
                        <span>الفرع: {{ storeOptions.find(s => s.id == storeId)?.name }}</span>
                        <button @click="storeId = 'all'; applyFilters();" class="hover:text-rose-400">✕</button>
                    </span>

                    <span v-if="dateFrom || dateTo" class="px-2.5 py-1 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 flex items-center gap-1.5 font-mono font-bold">
                        <span>التاريخ: {{ dateFrom || '...' }} إلى {{ dateTo || '...' }}</span>
                        <button @click="dateFrom = ''; dateTo = ''; applyFilters();" class="hover:text-rose-400">✕</button>
                    </span>

                    <span v-if="status !== 'active'" class="px-2.5 py-1 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center gap-1.5 font-bold">
                        <span>الحالة: {{ statusOptions.find(s => s.id === status)?.name }}</span>
                        <button @click="status = 'active'; applyFilters();" class="hover:text-rose-400">✕</button>
                    </span>

                    <button @click="resetFilters" class="text-slate-400 hover:text-rose-400 text-xs underline font-bold mr-1">
                        مسح كافة الفلاتر
                    </button>
                </div>
            </div>

            <!-- Invoices Data Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 font-tajawal overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">رقم الفاتورة</th>
                                <th class="pb-3">العميل</th>
                                <th class="pb-3">الفرع / نقطة البيع</th>
                                <th class="pb-3">التاريخ</th>
                                <th class="pb-3">نوع الدفع</th>
                                <th class="pb-3 font-mono">الصافي المطلوب</th>
                                <th class="pb-3 font-mono">المدفوع</th>
                                <th class="pb-3 font-mono">المتبقي</th>
                                <th class="pb-3">الحالة</th>
                                <th class="pb-3 text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-slate-800/40 transition">
                                <!-- Invoice Number -->
                                <td class="py-3.5 font-mono font-black text-white">
                                    <Link :href="`/invoices/${inv.id}`" class="hover:text-amber-400 flex items-center gap-1">
                                        <span>#{{ inv.invoice_number }}</span>
                                    </Link>
                                </td>

                                <!-- Customer -->
                                <td class="py-3.5">
                                    <div class="font-bold text-slate-200 font-tajawal">{{ inv.customer_name }}</div>
                                    <div v-if="inv.customer_phone" class="text-[10px] text-slate-500 font-mono" dir="ltr">{{ inv.customer_phone }}</div>
                                </td>

                                <!-- Store -->
                                <td class="py-3.5">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-800 border border-slate-700/60 text-slate-300 text-[11px] font-tajawal">
                                        🏬 {{ inv.store_name }}
                                    </span>
                                </td>

                                <!-- Date -->
                                <td class="py-3.5 text-slate-400 font-mono text-[11px]">
                                    {{ inv.formatted_created_at || inv.created_at }}
                                </td>

                                <!-- Payment Badge -->
                                <td class="py-3.5 font-tajawal">
                                    <span class="px-2.5 py-1 rounded-xl text-[10.5px] font-bold border" :class="getPaymentBadge(inv).class">
                                        {{ getPaymentBadge(inv).label }}
                                    </span>
                                </td>

                                <!-- Net Total -->
                                <td class="py-3.5 font-mono font-bold text-emerald-400 text-sm">
                                    {{ formatMoney(inv.net_total) }}
                                </td>

                                <!-- Paid -->
                                <td class="py-3.5 font-mono font-bold text-slate-200">
                                    {{ formatMoney(inv.paid_amount) }}
                                </td>

                                <!-- Remaining -->
                                <td class="py-3.5 font-mono font-bold">
                                    <span :class="Number(inv.remaining_amount) > 0 ? 'text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded-lg border border-rose-500/20' : 'text-slate-400'">
                                        {{ formatMoney(inv.remaining_amount) }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 font-tajawal">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black border" :class="getStatusBadge(inv.status).class">
                                        {{ getStatusBadge(inv.status).label }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Link
                                            :href="`/invoices/${inv.id}`"
                                            class="p-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition"
                                            title="عرض تفاصيل الفاتورة"
                                        >
                                            👁️
                                        </Link>

                                        <button
                                            @click="printThermal(inv.id)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-400 transition cursor-pointer"
                                            title="طباعة إيصال كاشير حراري (80mm)"
                                        >
                                            🖨️
                                        </button>

                                        <button
                                            @click="printA4(inv.id)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/30 text-indigo-400 transition cursor-pointer"
                                            title="طباعة فاتورة رسمية A4"
                                        >
                                            📄
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="!invoices.data || invoices.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">🧾</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">لا توجد فواتير مسجلة مطابقة للفلاتر المحددة</p>
                    </div>
                </div>

                <!-- Pagination Links -->
                <div v-if="invoices.links && invoices.links.length > 3" class="pt-4 border-t border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        عرض {{ invoices.from || 0 }} إلى {{ invoices.to || 0 }} من إجمالي {{ invoices.total || 0 }} فاتورة
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in invoices.links" :key="lIdx">
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

        <!-- Advanced Filter Slide-Over Drawer -->
        <FilterDrawer
            :show="isDrawerOpen"
            :active-count="activeFiltersCount"
            @close="isDrawerOpen = false"
            @apply="applyFilters"
            @reset="resetFilters"
        >
            <div class="space-y-5">
                <!-- 1. Search Box -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">🔍 البحث بالرقم أو العميل أو الهاتف</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="... اكتب للبحث"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-white placeholder:text-slate-500 focus:border-amber-500 focus:outline-none transition"
                    >
                </div>

                <!-- 2. Store Selection (Searchable) -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">🏬 الفرع أو نقطة البيع أو عربة التوزيع</label>
                    <SearchableSelect
                        v-model="storeId"
                        :options="storeOptions"
                        placeholder="اختر الفرع..."
                        search-placeholder="... ابحث في الفروع"
                    />
                </div>

                <!-- 3. Date Range Calendar (Flatpickr) -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-300">📅 المدى الزمني للفواتير</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[11px] text-slate-500 block mb-1">من تاريخ:</span>
                            <DatePicker
                                v-model="dateFrom"
                                placeholder="من..."
                            />
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 block mb-1">إلى تاريخ:</span>
                            <DatePicker
                                v-model="dateTo"
                                placeholder="إلى..."
                            />
                        </div>
                    </div>
                </div>

                <!-- 4. Payment Type -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">💳 نوع السداد والحساب</label>
                    <SearchableSelect
                        v-model="paymentType"
                        :options="paymentTypeOptions"
                        placeholder="اختر نوع السداد..."
                    />
                </div>

                <!-- 5. Payment Method -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">⚡ وسيلة التحصيل (كاش / إستاباي / محفظة)</label>
                    <SearchableSelect
                        v-model="paymentMethod"
                        :options="paymentMethodOptions"
                        placeholder="اختر وسيلة التحصيل..."
                    />
                </div>

                <!-- 6. Invoice Status -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">📋 حالة الفاتورة والأرشيف</label>
                    <SearchableSelect
                        v-model="status"
                        :options="statusOptions"
                        placeholder="اختر الحالة..."
                    />
                </div>
            </div>
        </FilterDrawer>
    </AppLayout>
</template>
