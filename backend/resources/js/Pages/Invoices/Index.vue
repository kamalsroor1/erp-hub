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
    { id: 'all', name: `🏬 ${trans('common.all') || 'كافة الفروع ونقاط البيع'}` },
    ...props.stores.map(s => ({
        id: s.id,
        name: `${s.type === 'van' ? '🚐' : '🏬'} ${s.name}`,
        type: s.type,
    }))
]);

// Payment Types formatted for SearchableSelect
const paymentTypeOptions = computed(() => [
    { id: 'all', name: trans('invoices.all_payment_types') || 'الكل (كافة أنواع السداد)' },
    { id: 'cash', name: `💵 ${trans('invoices.payment_cash') || 'كاش'}` },
    { id: 'credit', name: `⏳ ${trans('invoices.payment_credit') || 'آجل'}` },
    { id: 'partial', name: `📊 ${trans('invoices.payment_partial') || 'جزئي'}` },
]);

const paymentMethodOptions = computed(() => [
    { id: 'all', name: trans('invoices.all_payment_methods') || 'كافة وسائل التحصيل' },
    { id: 'cash', name: trans('invoices.payment_cash') || 'نقدي (كاش)' },
    { id: 'instapay', name: `⚡ ${trans('invoices.payment_instapay') || 'إستاباي'}` },
    { id: 'wallet', name: `📱 ${trans('invoices.payment_wallet') || 'محفظة إلكترونية'}` },
    { id: 'bank', name: `🏦 ${trans('invoices.payment_bank') || 'تحويل بنكي'}` },
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
        if (inv.payment_method === 'instapay') return { label: 'إستاباي ⚡', class: 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30' };
        if (inv.payment_method === 'wallet') return { label: 'محفظة 📱', class: 'bg-teal-500/15 text-teal-400 border-teal-500/30' };
        return { label: trans('invoices.payment_cash') || 'كاش 💵', class: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' };
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
                        <span class="text-2xl">🧾</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white font-tajawal">
                            {{ $t('invoices.title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        {{ $t('invoices.subtitle') }}
                    </p>
                </div>

                <Link
                    href="/pos"
                    class="h-11 px-5 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20 transition transform active:scale-95 font-tajawal cursor-pointer"
                >
                    <span class="text-base font-black">+</span>
                    <span>{{ $t('invoices.new_sale_invoice') }}</span>
                </Link>
            </div>

            <!-- 4 Top KPI Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 font-tajawal">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 space-y-1">
                    <span class="text-[11px] text-slate-400 font-bold block">{{ $t('invoices.total_invoices_count') }}</span>
                    <div class="text-xl font-black font-mono text-white flex items-center gap-1.5">
                        <span>🧾</span>
                        <span>{{ stats.total_count }}</span>
                        <span class="text-xs font-tajawal text-slate-400 font-normal">{{ $t('invoices.invoice_unit') }}</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 space-y-1">
                    <span class="text-[11px] text-slate-400 font-bold block">{{ $t('invoices.total_sales_net') }}</span>
                    <div class="text-xl font-black font-mono text-emerald-400 flex items-center gap-1.5">
                        <span>💰</span>
                        <span>{{ formatMoney(stats.total_net) }}</span>
                        <span class="text-xs font-tajawal text-slate-400 font-normal">{{ $t('common.currency') }}</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 space-y-1">
                    <span class="text-[11px] text-slate-400 font-bold block">{{ $t('invoices.total_paid_actual') }}</span>
                    <div class="text-xl font-black font-mono text-amber-400 flex items-center gap-1.5">
                        <span>💵</span>
                        <span>{{ formatMoney(stats.total_paid) }}</span>
                        <span class="text-xs font-tajawal text-slate-400 font-normal">{{ $t('common.currency') }}</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 space-y-1">
                    <span class="text-[11px] text-slate-400 font-bold block">{{ $t('invoices.total_remaining_credit') }}</span>
                    <div class="text-xl font-black font-mono text-rose-400 flex items-center gap-1.5">
                        <span>⏳</span>
                        <span>{{ formatMoney(stats.total_remaining) }}</span>
                        <span class="text-xs font-tajawal text-slate-400 font-normal">{{ $t('common.currency') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Bar & Drawer Toggle -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-sm font-tajawal space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <!-- Quick Search Input -->
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="$t('invoices.search_invoices_placeholder')"
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
                                {{ $t('common.all') }}
                            </button>
                            <button
                                @click="paymentType = 'cash'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="paymentType === 'cash' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-slate-400 hover:text-white'"
                            >
                                {{ $t('invoices.payment_cash') }} 💵
                            </button>
                            <button
                                @click="paymentType = 'credit'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="paymentType === 'credit' ? 'bg-rose-500 text-white font-black' : 'text-slate-400 hover:text-white'"
                            >
                                {{ $t('invoices.payment_credit') }}
                            </button>
                        </div>

                        <!-- Open Filter Drawer Button -->
                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                        >
                            <span>⚙️</span>
                            <span>{{ $t('common.filter') }}</span>
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
                    <span class="text-slate-500 text-[11px] font-bold">{{ $t('dashboard.quick_filter') || 'الفلاتر النشطة' }}:</span>

                    <span v-if="storeId !== 'all'" class="px-2.5 py-1 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center gap-1.5 font-bold">
                        <span>{{ $t('common.store') }}: {{ storeOptions.find(s => s.id == storeId)?.name }}</span>
                        <button @click="storeId = 'all'; applyFilters();" class="hover:text-rose-400">✕</button>
                    </span>

                    <span v-if="dateFrom || dateTo" class="px-2.5 py-1 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 flex items-center gap-1.5 font-mono font-bold">
                        <span>{{ $t('common.date') }}: {{ dateFrom || '...' }} إلى {{ dateTo || '...' }}</span>
                        <button @click="dateFrom = ''; dateTo = ''; applyFilters();" class="hover:text-rose-400">✕</button>
                    </span>

                    <span v-if="status !== 'active'" class="px-2.5 py-1 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center gap-1.5 font-bold">
                        <span>{{ $t('common.status') }}: {{ statusOptions.find(s => s.id === status)?.name }}</span>
                        <button @click="status = 'active'; applyFilters();" class="hover:text-rose-400">✕</button>
                    </span>

                    <button @click="resetFilters" class="text-slate-400 hover:text-rose-400 text-xs underline font-bold mr-1">
                        {{ $t('common.clear_all') || 'مسح كافة الفلاتر' }}
                    </button>
                </div>
            </div>

            <!-- Invoices Data Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 font-tajawal overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
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
                                        <!-- View Show Page -->
                                        <Link
                                            :href="`/invoices/${inv.id}`"
                                            class="p-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition cursor-pointer"
                                            :title="$t('invoices.view_invoice')"
                                        >
                                            👁️
                                        </Link>

                                        <!-- Edit Invoice (Only if not cancelled and not deleted) -->
                                        <Link
                                            v-if="inv.status !== 'cancelled' && status !== 'trash'"
                                            :href="`/invoices/${inv.id}/edit`"
                                            class="p-1.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-400 transition cursor-pointer"
                                            :title="$t('invoices.edit_invoice')"
                                        >
                                            ✏️
                                        </Link>

                                        <!-- Print Thermal Receipt -->
                                        <button
                                            @click="printThermal(inv.id)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-400 transition cursor-pointer"
                                            :title="$t('invoices.print_thermal')"
                                        >
                                            🖨️
                                        </button>

                                        <!-- Print A4 Invoice -->
                                        <button
                                            @click="printA4(inv.id)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/30 text-indigo-400 transition cursor-pointer"
                                            :title="$t('invoices.print_a4')"
                                        >
                                            📄
                                        </button>

                                        <!-- Cancel Invoice Action (if not cancelled) -->
                                        <button
                                            v-if="inv.status !== 'cancelled' && status !== 'trash'"
                                            @click="openCancelModal(inv)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 text-rose-400 transition cursor-pointer"
                                            :title="$t('invoices.cancel_invoice')"
                                        >
                                            🚫
                                        </button>

                                        <!-- Restore from Trash -->
                                        <button
                                            v-if="status === 'trash'"
                                            @click="restoreInvoice(inv)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-400 transition cursor-pointer"
                                            :title="$t('trash.restore_btn') || 'استعادة الفاتورة'"
                                        >
                                            ♻️
                                        </button>

                                        <!-- Delete / Archive -->
                                        <button
                                            v-if="status !== 'trash'"
                                            @click="confirmDelete(inv)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition cursor-pointer"
                                            :title="$t('common.delete')"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="!invoices.data || invoices.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">🧾</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">{{ $t('invoices.no_invoices_found') }}</p>
                    </div>
                </div>

                <!-- Pagination Links -->
                <div v-if="invoices.links && invoices.links.length > 3" class="pt-4 border-t border-slate-800/80 flex items-center justify-between font-sans">
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
                    <label class="text-xs font-black text-slate-300">🔍 {{ $t('invoices.search_invoices_placeholder') }}</label>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="$t('common.search')"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-white placeholder:text-slate-500 focus:border-amber-500 focus:outline-none transition"
                    >
                </div>

                <!-- 2. Store Selection (Searchable) -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">🏬 {{ $t('invoices.store') }}</label>
                    <SearchableSelect
                        v-model="storeId"
                        :options="storeOptions"
                        :placeholder="$t('common.store')"
                    />
                </div>

                <!-- 3. Date Range Calendar (Flatpickr) -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-300">📅 {{ $t('contacts.report_period') }}</label>
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
                    <label class="text-xs font-black text-slate-300">💳 {{ $t('invoices.payment_type') }}</label>
                    <SearchableSelect
                        v-model="paymentType"
                        :options="paymentTypeOptions"
                        :placeholder="$t('invoices.payment_type')"
                    />
                </div>

                <!-- 5. Payment Method -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">⚡ {{ $t('invoices.payment_method') }}</label>
                    <SearchableSelect
                        v-model="paymentMethod"
                        :options="paymentMethodOptions"
                        :placeholder="$t('invoices.payment_method')"
                    />
                </div>

                <!-- 6. Invoice Status -->
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">📋 {{ $t('common.status') }}</label>
                    <SearchableSelect
                        v-model="status"
                        :options="statusOptions"
                        :placeholder="$t('common.status')"
                    />
                </div>
            </div>
        </FilterDrawer>

        <!-- Cancel Invoice Reason Modal -->
        <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 w-full max-w-md space-y-4 shadow-2xl font-tajawal animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-base font-black text-white flex items-center gap-2">
                        <span>⚠️ {{ $t('invoices.cancel_modal_title') }}</span>
                    </h3>
                    <button @click="showCancelModal = false" class="text-slate-400 hover:text-white font-bold cursor-pointer">✕</button>
                </div>

                <p class="text-xs text-slate-300">
                    {{ $t('invoices.cancel_modal_desc', { number: cancelTargetInvoice?.invoice_number }) }}
                </p>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-300">{{ $t('invoices.cancel_reason_label') }}</label>
                    <textarea
                        v-model="cancelReason"
                        rows="3"
                        :placeholder="$t('invoices.cancel_reason_placeholder')"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white placeholder:text-slate-500 focus:border-rose-500 focus:outline-none"
                    ></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button
                        @click="showCancelModal = false"
                        type="button"
                        class="flex-1 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition cursor-pointer"
                    >
                        {{ $t('common.cancel') }}
                    </button>
                    <button
                        :disabled="isCancelling || !cancelReason || cancelReason.trim().length < 3"
                        @click="confirmCancel"
                        type="button"
                        class="flex-1 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white text-xs font-black transition shadow-lg shadow-rose-600/30 cursor-pointer"
                    >
                        {{ isCancelling ? '...' : $t('invoices.confirm_cancel_btn') }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
