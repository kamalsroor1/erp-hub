<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';
import {
    Receipt,
    Banknote,
    Wallet,
    Clock,
    Search,
    Filter,
    Plus,
    Eye,
    Pencil,
    Printer,
    FileText,
    Ban,
    RotateCcw,
    Trash2,
    X,
    AlertTriangle,
    Zap,
    Smartphone,
    Store
} from 'lucide-vue-next';

const props = defineProps({
    invoices: { type: Object, required: true },
    stats: { type: Object, default: () => ({ total_count: 0, total_net: 0, total_paid: 0, total_remaining: 0 }) },
    filters: { type: Object, default: () => ({}) },
    stores: { type: Array, default: () => [] },
});

const page = usePage();
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

// Cancel Modal State
const showCancelModal = ref(false);
const cancelTargetInvoice = ref(null);
const cancelReason = ref('');
const isCancelling = ref(false);

const openCancelModal = (inv) => {
    cancelTargetInvoice.value = inv;
    cancelReason.value = '';
    showCancelModal.value = true;
};

const confirmCancel = () => {
    if (!cancelReason.value || cancelReason.value.trim().length < 3) {
        alert(trans('invoices.cancel_reason_label') || 'يرجى كتابة سبب الإلغاء (3 أحرف على الأقل)');
        return;
    }
    isCancelling.value = true;
    router.post(`/invoices/${cancelTargetInvoice.value.id}/cancel`, {
        reason: cancelReason.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showCancelModal.value = false;
            cancelTargetInvoice.value = null;
        },
        onFinish: () => {
            isCancelling.value = false;
        },
    });
};

const confirmDelete = (inv) => {
    if (confirm(trans('common.confirm_delete') || `هل أنت متأكد من حذف الفاتورة رقم #${inv.invoice_number}؟`)) {
        router.delete(`/invoices/${inv.id}`, {
            preserveScroll: true,
        });
    }
};

const restoreInvoice = (inv) => {
    if (confirm(trans('trash.restore_confirm') || `هل ترغب في استعادة الفاتورة رقم #${inv.invoice_number} من سلة المحذوفات؟`)) {
        router.post(`/invoices/${inv.id}/restore`, {}, {
            preserveScroll: true,
        });
    }
};

// Compute Active Filters Count
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

// Dropdown Options
const storeOptions = computed(() => [
    { id: 'all', name: trans('common.all_stores') || 'كافة الفروع' },
    ...props.stores.map(s => ({ id: s.id, name: s.name })),
]);

const paymentTypeOptions = computed(() => [
    { id: 'all', name: trans('invoices.all_payment_types') || 'كافة أنواع الدفع' },
    { id: 'cash', name: trans('invoices.payment_cash') || 'نقدي (كاش)' },
    { id: 'credit', name: trans('invoices.payment_credit') || 'آجل (ذمم)' },
    { id: 'partial', name: trans('invoices.payment_partial') || 'سداد جزئي' },
]);

const paymentMethodOptions = computed(() => [
    { id: 'all', name: trans('invoices.all_payment_methods') || 'كافة طرق التحصيل' },
    { id: 'cash', name: 'كاش يدوي' },
    { id: 'instapay', name: 'إنستاباي (InstaPay)' },
    { id: 'wallet', name: 'محفظة إلكترونية' },
    { id: 'card', name: 'بطاقة بنكية / فيزا' },
]);

const statusOptions = computed(() => [
    { id: 'active', name: trans('invoices.active_invoices_only') || 'الفواتير النشطة' },
    { id: 'confirmed', name: trans('invoices.confirmed_only') || 'الفواتير المعتمدة فقط' },
    { id: 'cancelled', name: trans('invoices.cancelled_only') || 'الفواتير الملغاة' },
    { id: 'trash', name: trans('invoices.trash_invoices') || 'سلة المحذوفات' },
]);

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
        if (inv.payment_method === 'instapay') return { label: 'إستاباي', class: 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30' };
        if (inv.payment_method === 'wallet') return { label: 'محفظة', class: 'bg-teal-500/15 text-teal-400 border-teal-500/30' };
        return { label: trans('invoices.payment_cash') || 'كاش', class: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' };
    }
    if (inv.payment_type === 'credit') return { label: trans('invoices.payment_credit') || 'آجل', class: 'bg-rose-500/15 text-rose-400 border-rose-500/30' };
    if (inv.payment_type === 'partial') return { label: trans('invoices.payment_partial') || 'جزئي', class: 'bg-amber-500/15 text-amber-400 border-amber-500/30' };
    return { label: inv.payment_type, class: 'bg-slate-800 text-slate-400 border-slate-700' };
};

const getStatusBadge = (st) => {
    if (st === 'confirmed') return { label: trans('invoices.status_confirmed') || 'معتمدة', class: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' };
    if (st === 'cancelled') return { label: trans('invoices.status_cancelled') || 'ملغاة', class: 'bg-rose-500/10 text-rose-400 border-rose-500/20' };
    return { label: st, class: 'bg-slate-800 text-slate-400 border-slate-700' };
};

const getInvoiceActions = (inv) => [
    {
        label: trans('invoices.view_invoice') || 'عرض تفاصيل الفاتورة',
        icon: Eye,
        href: `/invoices/${inv.id}`,
        show: true
    },
    {
        label: trans('invoices.print_thermal') || 'طباعة إيصال حراري (80mm)',
        icon: Printer,
        variant: 'success',
        onClick: () => printThermal(inv.id),
        show: true
    },
    {
        label: trans('invoices.print_a4') || 'طباعة فاتورة ضريبية (A4)',
        icon: FileText,
        onClick: () => printA4(inv.id),
        show: true
    },
    {
        label: trans('invoices.edit_invoice') || 'تعديل الفاتورة',
        icon: Pencil,
        variant: 'warning',
        href: `/invoices/${inv.id}/edit`,
        show: inv.status !== 'cancelled' && status.value !== 'trash'
    },
    {
        label: trans('invoices.cancel_invoice') || 'إلغاء الفاتورة',
        icon: Ban,
        variant: 'danger',
        onClick: () => openCancelModal(inv),
        show: inv.status !== 'cancelled' && status.value !== 'trash'
    },
    {
        label: trans('trash.restore_btn') || 'استعادة الفاتورة',
        icon: RotateCcw,
        variant: 'success',
        onClick: () => restoreInvoice(inv),
        show: status.value === 'trash'
    },
    {
        label: trans('common.delete') || 'حذف الفاتورة',
        icon: Trash2,
        variant: 'danger',
        onClick: () => confirmDelete(inv),
        show: status.value !== 'trash'
    }
];

const printThermal = (id) => {
    window.open(`/invoices/${id}/print/thermal`, '_blank', 'width=400,height=600');
};

const printA4 = (id) => {
    window.open(`/invoices/${id}/print/a4`, '_blank', 'width=900,height=800');
};
</script>

<template>
    <Head :title="$t('invoices.title')" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Receipt class="w-6 h-6 text-theme-primary" />
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-tajawal">
                            {{ $t('invoices.title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                        {{ $t('invoices.subtitle') }}
                    </p>
                </div>

                <Link
                    href="/pos"
                    class="h-11 px-5 rounded-2xl btn-primary-theme font-black text-xs flex items-center justify-center gap-2 transition transform active:scale-95 font-tajawal cursor-pointer"
                >
                    <Plus class="w-4 h-4" />
                    <span>{{ $t('invoices.new_sale_invoice') }}</span>
                </Link>
            </div>

            <!-- 4 Top KPI Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 font-tajawal">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 space-y-1 shadow-xs">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold block">{{ $t('invoices.total_invoices_count') }}</span>
                    <div class="text-xl font-black font-mono text-slate-900 dark:text-white flex items-center gap-2">
                        <Receipt class="w-5 h-5 text-theme-primary" />
                        <span>{{ stats.total_count }}</span>
                        <span class="text-xs font-tajawal text-slate-400 font-normal">{{ $t('invoices.invoice_unit') }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 space-y-1 shadow-xs">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold block">{{ $t('invoices.total_sales_net') }}</span>
                    <div class="text-xl font-black font-mono text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                        <Banknote class="w-5 h-5 text-emerald-500" />
                        <span>{{ formatMoney(stats.total_net) }}</span>
                        <span class="text-xs font-tajawal text-slate-400 font-normal">{{ $t('common.currency') }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 space-y-1 shadow-xs">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold block">{{ $t('invoices.total_paid_actual') }}</span>
                    <div class="text-xl font-black font-mono text-amber-600 dark:text-amber-400 flex items-center gap-2">
                        <Wallet class="w-5 h-5 text-amber-500" />
                        <span>{{ formatMoney(stats.total_paid) }}</span>
                        <span class="text-xs font-tajawal text-slate-400 font-normal">{{ $t('common.currency') }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 space-y-1 shadow-xs">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-bold block">{{ $t('invoices.total_remaining_credit') }}</span>
                    <div class="text-xl font-black font-mono text-rose-600 dark:text-rose-400 flex items-center gap-2">
                        <Clock class="w-5 h-5 text-rose-500" />
                        <span>{{ formatMoney(stats.total_remaining) }}</span>
                        <span class="text-xs font-tajawal text-slate-400 font-normal">{{ $t('common.currency') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Bar & Drawer Toggle -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-xs font-tajawal space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <!-- Quick Search Input -->
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="$t('invoices.search_invoices_placeholder')"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition shadow-inner"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 pointer-events-none">
                            <Search class="w-4 h-4" />
                        </span>
                    </div>

                    <!-- Filter Pills & Drawer Toggle Button -->
                    <div class="w-full md:w-auto flex flex-wrap items-center justify-between md:justify-end gap-2">
                        <!-- Quick Payment Tabs -->
                        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-950/80 p-1 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs">
                            <button
                                @click="paymentType = 'all'; applyFilters();"
                                type="button"
                                class="h-9 px-3 rounded-xl font-bold transition cursor-pointer flex items-center justify-center active:scale-95"
                                :class="paymentType === 'all' ? 'bg-amber-500 text-slate-950 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('common.all') }}
                            </button>
                            <button
                                @click="paymentType = 'cash'; applyFilters();"
                                type="button"
                                class="h-9 px-3 rounded-xl font-bold transition cursor-pointer flex items-center justify-center active:scale-95"
                                :class="paymentType === 'cash' ? 'bg-emerald-500 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('invoices.payment_cash') }}
                            </button>
                            <button
                                @click="paymentType = 'credit'; applyFilters();"
                                type="button"
                                class="h-9 px-3 rounded-xl font-bold transition cursor-pointer flex items-center justify-center active:scale-95"
                                :class="paymentType === 'credit' ? 'bg-rose-500 text-white font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('invoices.payment_credit') }}
                            </button>
                        </div>

                        <!-- Open Filter Drawer Button -->
                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-11 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer shadow-xs active:scale-95"
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

                    <span v-if="storeId !== 'all'" class="px-2.5 py-1 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center gap-1.5 font-bold">
                        <span>{{ $t('common.store') }}: {{ stores.find(s => s.id == storeId)?.name }}</span>
                        <button @click="storeId = 'all'; applyFilters();" class="hover:text-rose-400 cursor-pointer">
                            <X class="w-3 h-3" />
                        </button>
                    </span>

                    <span v-if="paymentType !== 'all'" class="px-2.5 py-1 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center gap-1.5 font-bold">
                        <span>{{ $t('invoices.payment_type') }}: {{ paymentType === 'cash' ? $t('invoices.payment_cash') : $t('invoices.payment_credit') }}</span>
                        <button @click="paymentType = 'all'; applyFilters();" class="hover:text-rose-400 cursor-pointer">
                            <X class="w-3 h-3" />
                        </button>
                    </span>

                    <span v-if="status !== 'active'" class="px-2.5 py-1 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center gap-1.5 font-bold">
                        <span>{{ $t('common.status') }}: {{ status === 'cancelled' ? $t('invoices.status_cancelled') : status }}</span>
                        <button @click="status = 'active'; applyFilters();" class="hover:text-rose-400 cursor-pointer">
                            <X class="w-3 h-3" />
                        </button>
                    </span>

                    <button @click="resetFilters" class="text-slate-500 hover:text-rose-500 dark:text-slate-400 dark:hover:text-rose-400 text-xs underline font-bold mr-1 cursor-pointer">
                        {{ $t('common.clear_all') || 'مسح كافة الفلاتر' }}
                    </button>
                </div>
            </div>

            <!-- Invoices Data Table & Mobile Cards -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-xs space-y-4 font-tajawal overflow-hidden">
                <!-- Desktop Table (Hidden on Mobile) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('invoices.invoice_number') }}</th>
                                <th class="pb-3">{{ $t('invoices.customer') }}</th>
                                <th class="pb-3">{{ $t('invoices.store') }}</th>
                                <th class="pb-3">{{ $t('common.date') }}</th>
                                <th class="pb-3">{{ $t('invoices.payment_type') }}</th>
                                <th class="pb-3 font-mono">{{ $t('invoices.net_total') }}</th>
                                <th class="pb-3 font-mono">{{ $t('invoices.paid') }}</th>
                                <th class="pb-3 font-mono">{{ $t('invoices.remaining') }}</th>
                                <th class="pb-3">{{ $t('common.status') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                            <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <!-- Invoice Number -->
                                <td class="py-3.5 font-mono font-black text-slate-900 dark:text-white">
                                    <Link :href="`/invoices/${inv.id}`" class="hover:text-amber-600 dark:hover:text-amber-400 flex items-center gap-1">
                                        <span>#{{ inv.invoice_number }}</span>
                                    </Link>
                                </td>

                                <!-- Customer -->
                                <td class="py-3.5">
                                    <div class="font-bold text-slate-900 dark:text-slate-200 font-tajawal">{{ inv.customer_name }}</div>
                                    <div v-if="inv.customer_phone" class="text-[10px] text-slate-500 font-mono" dir="ltr">{{ inv.customer_phone }}</div>
                                </td>

                                <!-- Store -->
                                <td class="py-3.5">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 text-[11px] font-tajawal inline-flex items-center gap-1">
                                        <Store class="w-3 h-3 text-slate-400" />
                                        <span>{{ inv.store_name }}</span>
                                    </span>
                                </td>

                                <!-- Date -->
                                <td class="py-3.5 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                    {{ inv.formatted_created_at || inv.created_at }}
                                </td>

                                <!-- Payment Badge -->
                                <td class="py-3.5 font-tajawal">
                                    <span class="px-2.5 py-1 rounded-xl text-[10.5px] font-bold border" :class="getPaymentBadge(inv).class">
                                        {{ getPaymentBadge(inv).label }}
                                    </span>
                                </td>

                                <!-- Net Total -->
                                <td class="py-3.5 font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                                    {{ formatMoney(inv.net_total) }}
                                </td>

                                <!-- Paid -->
                                <td class="py-3.5 font-mono font-bold text-slate-900 dark:text-slate-200">
                                    {{ formatMoney(inv.paid_amount) }}
                                </td>

                                <!-- Remaining -->
                                <td class="py-3.5 font-mono font-bold">
                                    <span :class="Number(inv.remaining_amount) > 0 ? 'text-rose-600 dark:text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded-lg border border-rose-500/20' : 'text-slate-400'">
                                        {{ formatMoney(inv.remaining_amount) }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 font-tajawal">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black border" :class="getStatusBadge(inv.status).class">
                                        {{ getStatusBadge(inv.status).label }}
                                    </span>
                                </td>

                                <!-- Actions in Desktop Table -->
                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Link
                                            :href="`/invoices/${inv.id}`"
                                            class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition cursor-pointer"
                                            :title="$t('invoices.view_invoice')"
                                        >
                                            <Eye class="w-3.5 h-3.5" />
                                        </Link>

                                        <button
                                            @click="printThermal(inv.id)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 transition cursor-pointer"
                                            :title="$t('invoices.print_thermal')"
                                        >
                                            <Printer class="w-3.5 h-3.5" />
                                        </button>

                                        <ActionMenu
                                            :items="getInvoiceActions(inv)"
                                            :title="`#${inv.invoice_number}`"
                                            align="start"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards View (Visible on Small Screens) -->
                <div class="md:hidden space-y-3">
                    <div
                        v-for="inv in invoices.data"
                        :key="inv.id"
                        class="p-4 rounded-3xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 space-y-3 shadow-xs"
                    >
                        <!-- Card Header: Row 1 (Number + Status + Action Menu •••) -->
                        <div class="flex items-center justify-between gap-2 border-b border-slate-200/80 dark:border-slate-800/80 pb-2.5">
                            <Link :href="`/invoices/${inv.id}`" class="font-mono font-black text-xs sm:text-sm text-theme-primary hover:underline truncate" dir="ltr">
                                #{{ inv.invoice_number }}
                            </Link>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="px-2.5 py-0.5 rounded-xl text-[10px] font-black border shrink-0" :class="getStatusBadge(inv.status).class">
                                    {{ getStatusBadge(inv.status).label }}
                                </span>
                                <!-- Action Menu ••• (Header Action Button) -->
                                <ActionMenu
                                    :items="getInvoiceActions(inv)"
                                    :title="`#${inv.invoice_number}`"
                                    align="start"
                                    buttonClass="w-8 h-8 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-black shadow-xs hover:border-theme-primary shrink-0"
                                />
                            </div>
                        </div>

                        <!-- Card Meta: Store & Date -->
                        <div class="flex items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                            <span class="px-2 py-0.5 rounded-lg bg-slate-200/60 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 text-slate-700 dark:text-slate-300 font-bold inline-flex items-center gap-1.5 truncate max-w-[60%]">
                                <Store class="w-3.5 h-3.5 text-theme-primary shrink-0" />
                                <span class="truncate">{{ inv.store_name }}</span>
                            </span>
                            <span class="font-mono text-[10.5px] shrink-0 text-slate-400">
                                {{ inv.formatted_created_at || inv.created_at }}
                            </span>
                        </div>

                        <!-- Customer Details Block -->
                        <div class="space-y-0.5 bg-white dark:bg-slate-900/60 p-2.5 rounded-2xl border border-slate-200/70 dark:border-slate-800/60">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-black text-xs sm:text-sm text-slate-900 dark:text-white truncate">
                                    {{ inv.customer_name }}
                                </p>
                                <span v-if="inv.customer_phone" class="text-[11px] text-slate-400 font-mono shrink-0" dir="ltr">
                                    📱 {{ inv.customer_phone }}
                                </span>
                            </div>
                        </div>

                        <!-- Financials 3-Column Bento Row -->
                        <div class="grid grid-cols-3 gap-2 p-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center font-mono">
                            <div class="space-y-0.5">
                                <span class="text-[10px] text-slate-400 font-tajawal font-bold block">{{ $t('invoices.net_total') }}</span>
                                <span class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 block truncate">{{ formatMoney(inv.net_total) }}</span>
                            </div>
                            <div class="space-y-0.5">
                                <span class="text-[10px] text-slate-400 font-tajawal font-bold block">{{ $t('invoices.paid') }}</span>
                                <span class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 block truncate">{{ formatMoney(inv.paid_amount) }}</span>
                            </div>
                            <div class="space-y-0.5">
                                <span class="text-[10px] text-slate-400 font-tajawal font-bold block">{{ $t('invoices.remaining') }}</span>
                                <span class="text-xs sm:text-sm font-bold block truncate" :class="Number(inv.remaining_amount) > 0 ? 'text-rose-600 dark:text-rose-400 font-black' : 'text-slate-400'">
                                    {{ formatMoney(inv.remaining_amount) }}
                                </span>
                            </div>
                        </div>

                        <!-- Mobile Card Action Bar: Payment Badge + View CTA + Quick Thermal Print + ActionMenu Dropdown (•••) -->
                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-200/80 dark:border-slate-800/80">
                            <span class="px-2.5 py-1.5 rounded-xl text-[11px] font-black border shrink-0" :class="getPaymentBadge(inv).class">
                                {{ getPaymentBadge(inv).label }}
                            </span>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <!-- Quick View Link -->
                                <Link
                                    :href="`/invoices/${inv.id}`"
                                    class="h-9 px-2.5 sm:px-3 rounded-xl bg-slate-200/80 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-black flex items-center gap-1.5 transition active:scale-95 cursor-pointer shadow-xs shrink-0"
                                >
                                    <Eye class="w-4 h-4 text-theme-primary shrink-0" />
                                    <span>{{ $t('invoices.view_invoice') || 'عرض' }}</span>
                                </Link>

                                <!-- Quick Thermal Print Button -->
                                <button
                                    @click="printThermal(inv.id)"
                                    type="button"
                                    class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 flex items-center justify-center transition active:scale-90 cursor-pointer shadow-xs shrink-0"
                                    :title="$t('invoices.print_thermal') || 'طباعة حراري'"
                                >
                                    <Printer class="w-4 h-4" />
                                </button>

                                <!-- Action Menu ••• (Dropdown with all actions) -->
                                <ActionMenu
                                    :items="getInvoiceActions(inv)"
                                    :title="`#${inv.invoice_number}`"
                                    align="start"
                                    buttonClass="w-9 h-9 rounded-xl bg-slate-200/80 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-300/80 dark:border-slate-700/80 text-slate-700 dark:text-slate-200 font-black shadow-xs shrink-0"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!invoices.data || invoices.data.length === 0" class="py-16 text-center space-y-3">
                    <Receipt class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-700" />
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 font-tajawal">{{ $t('invoices.no_invoices_found') }}</p>
                </div>

                <!-- Pagination Links -->
                <div v-if="invoices.links && invoices.links.length > 3" class="pt-4 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        {{ $t('common.actions') ? `عرض ${invoices.from || 0} إلى ${invoices.to || 0} من إجمالي ${invoices.total || 0}` : `Showing ${invoices.from || 0} to ${invoices.to || 0} of ${invoices.total || 0}` }}
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
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <Search class="w-3.5 h-3.5" />
                        <span>{{ $t('invoices.search_invoices_placeholder') }}</span>
                    </label>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="$t('common.search')"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-amber-500 focus:outline-none transition"
                    >
                </div>

                <!-- 2. Store Selection (Searchable) -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <Store class="w-3.5 h-3.5" />
                        <span>{{ $t('invoices.store') }}</span>
                    </label>
                    <SearchableSelect
                        v-model="storeId"
                        :options="storeOptions"
                        :placeholder="$t('common.store')"
                    />
                </div>

                <!-- 3. Date Range Calendar (Flatpickr) -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <Clock class="w-3.5 h-3.5" />
                        <span>{{ $t('contacts.report_period') }}</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[11px] text-slate-500 block mb-1">{{ $t('contacts.from_date') }}:</span>
                            <DatePicker
                                v-model="dateFrom"
                                :placeholder="$t('contacts.from_date')"
                            />
                        </div>
                        <div>
                            <span class="text-[11px] text-slate-500 block mb-1">{{ $t('contacts.to_date') }}:</span>
                            <DatePicker
                                v-model="dateTo"
                                :placeholder="$t('contacts.to_date')"
                            />
                        </div>
                    </div>
                </div>

                <!-- 4. Payment Type -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <Wallet class="w-3.5 h-3.5" />
                        <span>{{ $t('invoices.payment_type') }}</span>
                    </label>
                    <SearchableSelect
                        v-model="paymentType"
                        :options="paymentTypeOptions"
                        :placeholder="$t('invoices.payment_type')"
                    />
                </div>

                <!-- 5. Payment Method -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <Zap class="w-3.5 h-3.5" />
                        <span>{{ $t('invoices.payment_method') }}</span>
                    </label>
                    <SearchableSelect
                        v-model="paymentMethod"
                        :options="paymentMethodOptions"
                        :placeholder="$t('invoices.payment_method')"
                    />
                </div>

                <!-- 6. Invoice Status -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <Filter class="w-3.5 h-3.5" />
                        <span>{{ $t('common.status') }}</span>
                    </label>
                    <SearchableSelect
                        v-model="status"
                        :options="statusOptions"
                        :placeholder="$t('common.status')"
                    />
                </div>
            </div>
        </FilterDrawer>

        <!-- Cancel Invoice Reason Modal (Smooth Native Pop) -->
        <Teleport to="body">
            <Transition name="modal-zoom">
                <div
                    v-if="showCancelModal"
                    @click="showCancelModal = false"
                    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-xs font-tajawal select-none"
                >
                    <div @click.stop class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 w-full max-w-md space-y-4 shadow-2xl font-tajawal max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <AlertTriangle class="w-5 h-5 text-rose-500" />
                                <span>{{ $t('invoices.cancel_modal_title') }}</span>
                            </h3>
                            <button @click="showCancelModal = false" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center font-bold transition active:scale-90 cursor-pointer shadow-xs">
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            {{ $t('invoices.cancel_modal_desc', { number: cancelTargetInvoice?.invoice_number }) }}
                        </p>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('invoices.cancel_reason_label') }}</label>
                            <textarea
                                v-model="cancelReason"
                                rows="3"
                                :placeholder="$t('invoices.cancel_reason_placeholder')"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-rose-500 focus:outline-none shadow-inner"
                            ></textarea>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <button
                                @click="showCancelModal = false"
                                type="button"
                                class="flex-1 h-11 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition active:scale-95 cursor-pointer shadow-xs"
                            >
                                {{ $t('common.cancel') }}
                            </button>
                            <button
                                :disabled="isCancelling || !cancelReason || cancelReason.trim().length < 3"
                                @click="confirmCancel"
                                type="button"
                                class="flex-1 h-11 rounded-2xl bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white text-xs font-black transition active:scale-95 shadow-lg shadow-rose-600/30 cursor-pointer"
                            >
                                {{ isCancelling ? '...' : $t('invoices.confirm_cancel_btn') }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
