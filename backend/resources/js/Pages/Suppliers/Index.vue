<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    suppliers: { type: Object, required: true },
    metrics: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

// Search & Filter state
import { trans } from '@/helpers/trans';

const search = ref(props.filters.search || '');
const debtStatus = ref(props.filters.debt_status || 'all');
const isDrawerOpen = ref(false);

const debtStatusOptions = computed(() => [
    { id: 'all', name: trans('contacts.all_customers') || 'كافة الموردين والشركات' },
    { id: 'creditor', name: trans('contacts.creditors_only') || 'موردين لهم مستحقات مالية (دائنون) 🚨' },
    { id: 'zero', name: trans('contacts.settled_only') || 'الحسابات المسواة (رصيد 0) ✅' },
]);

const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (debtStatus.value && debtStatus.value !== 'all') count++;
    return count;
});

const applyFilters = () => {
    router.get('/suppliers', {
        search: search.value || undefined,
        debt_status: debtStatus.value !== 'all' ? debtStatus.value : undefined,
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
    debtStatus.value = 'all';
    applyFilters();
};

// Add / Edit Supplier Modal
const showSupplierModal = ref(false);
const editingSupplier = ref(null);

const supplierForm = useForm({
    name: '',
    company_name: '',
    phone: '',
    address: '',
    opening_balance: '0.000',
    notes: '',
});

const openCreateModal = () => {
    editingSupplier.value = null;
    supplierForm.reset();
    supplierForm.clearErrors();
    showSupplierModal.value = true;
};

const openEditModal = (s) => {
    editingSupplier.value = s;
    supplierForm.clearErrors();
    supplierForm.name = s.name;
    supplierForm.company_name = s.company_name || '';
    supplierForm.phone = s.phone || '';
    supplierForm.address = s.address || '';
    supplierForm.notes = s.notes || '';
    showSupplierModal.value = true;
};

const saveSupplier = () => {
    if (editingSupplier.value) {
        supplierForm.put(`/suppliers/${editingSupplier.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showSupplierModal.value = false;
            }
        });
    } else {
        supplierForm.post('/suppliers', {
            preserveScroll: true,
            onSuccess: () => {
                showSupplierModal.value = false;
            }
        });
    }
};

// Payment to Supplier Modal
const showPaymentModal = ref(false);
const selectedSupplierForPayment = ref(null);

const paymentForm = useForm({
    amount: '',
    payment_method: 'cash',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
});

const paymentMethodOptions = [
    { id: 'cash', name: 'نقدي (كاش) 💵' },
    { id: 'instapay', name: 'إستاباي ⚡' },
    { id: 'wallet', name: 'محفظة إلكترونية 📱' },
    { id: 'bank', name: 'تحويل بنكي 🏦' },
];

const openPaymentModal = (s) => {
    selectedSupplierForPayment.value = s;
    paymentForm.reset();
    paymentForm.amount = s.current_balance > 0 ? s.current_balance : '';
    paymentForm.payment_date = new Date().toISOString().split('T')[0];
    showPaymentModal.value = true;
};

const submitPayment = () => {
    if (!selectedSupplierForPayment.value) return;
    paymentForm.post(`/suppliers/${selectedSupplierForPayment.value.id}/pay`, {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false;
        }
    });
};

const deleteSupplier = (s) => {
    if (!s.can_be_deleted) {
        alert('لا يمكن حذف المورد:\n- ' + s.deletion_blockers.join('\n- '));
        return;
    }
    if (confirm(`هل أنت متأكد من حذف المورد (${s.name})؟`)) {
        router.delete(`/suppliers/${s.id}`, {
            preserveScroll: true,
        });
    }
};

const toggleActive = (s) => {
    router.post(`/suppliers/${s.id}/toggle-active`, {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('contacts.suppliers_title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🏭</span>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            {{ $t('contacts.suppliers_title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                        {{ $t('contacts.suppliers_subtitle') }}
                    </p>
                </div>

                <button
                    @click="openCreateModal"
                    type="button"
                    class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                >
                    <span class="text-base font-black">+</span>
                    <span>{{ $t('contacts.add_new_supplier') }}</span>
                </button>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('contacts.total_payable_suppliers') }}</span>
                    <div class="text-2xl font-black font-mono text-amber-600 dark:text-amber-400">
                        {{ formatMoney(metrics.total_payable) }} <span class="text-xs text-slate-700 dark:text-white">{{ $t('common.currency') }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('contacts.creditors_count') }}</span>
                    <div class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400">
                        {{ metrics.creditors_count || 0 }} <span class="text-xs text-slate-400 font-tajawal">{{ $t('contacts.supplier_unit') }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-2">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">{{ $t('contacts.total_suppliers_count') }}</span>
                    <div class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                        {{ metrics.total_suppliers || 0 }} <span class="text-xs text-slate-400 font-tajawal">{{ $t('contacts.supplier_unit') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Filter Bar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-xs space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="$t('contacts.search_supplier_placeholder')"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition shadow-inner"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                            🔍
                        </span>
                    </div>

                    <div class="w-full md:w-auto flex flex-wrap items-center justify-between md:justify-end gap-2">
                        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-950/80 p-1 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs">
                            <button
                                @click="debtStatus = 'all'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="debtStatus === 'all' ? 'bg-amber-500 text-slate-950 font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('common.all') }}
                            </button>
                            <button
                                @click="debtStatus = 'creditor'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="debtStatus === 'creditor' ? 'bg-rose-500 text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('contacts.creditors_only') }}
                            </button>
                        </div>

                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                        >
                            <span>⚙️</span>
                            <span>{{ $t('common.filter') }}</span>
                            <span v-if="activeFiltersCount > 0" class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-mono font-black text-[11px] flex items-center justify-center">
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Suppliers Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('purchases.supplier') }}</th>
                                <th class="pb-3">{{ $t('contacts.company_name') }}</th>
                                <th class="pb-3">{{ $t('contacts.phone') }}</th>
                                <th class="pb-3">{{ $t('contacts.address') }}</th>
                                <th class="pb-3 font-mono">{{ $t('contacts.current_balance') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                            <tr v-for="s in suppliers.data" :key="s.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <!-- Name -->
                                <td class="py-3.5">
                                    <div class="font-black text-slate-900 dark:text-white font-tajawal text-sm">{{ s.name }}</div>
                                    <div v-if="s.notes" class="text-[10px] text-slate-500 font-tajawal">{{ s.notes }}</div>
                                </td>

                                <!-- Company -->
                                <td class="py-3.5 font-tajawal font-bold text-slate-700 dark:text-slate-300">
                                    {{ s.company_name || '—' }}
                                </td>

                                <!-- Phone -->
                                <td class="py-3.5 font-mono text-slate-600 dark:text-slate-300" dir="ltr">
                                    {{ s.phone || '—' }}
                                </td>

                                <!-- Address -->
                                <td class="py-3.5 font-tajawal text-slate-500 dark:text-slate-400">
                                    {{ s.address || '—' }}
                                </td>

                                <!-- Balance -->
                                <td class="py-3.5 font-mono font-black text-sm">
                                    <span
                                        class="px-2.5 py-1 rounded-xl border"
                                        :class="[
                                            s.current_balance > 0 ? 'bg-amber-500/15 border-amber-500/30 text-amber-600 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400'
                                        ]"
                                    >
                                        {{ formatMoney(s.current_balance) }} {{ $t('common.currency') }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 font-tajawal">
                                        <!-- Pay to Supplier Voucher -->
                                        <button
                                            @click="openPaymentModal(s)"
                                            type="button"
                                            class="px-2.5 py-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-bold transition cursor-pointer flex items-center gap-1"
                                            :title="$t('contacts.record_disbursement_voucher')"
                                        >
                                            <span>💸</span>
                                            <span>{{ $t('contacts.record_disbursement_voucher') }}</span>
                                        </button>

                                        <!-- Statement -->
                                        <Link
                                            :href="`/suppliers/${s.id}/statement`"
                                            class="p-1.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 transition"
                                            :title="$t('contacts.statement_title')"
                                        >
                                            📜
                                        </Link>

                                        <!-- Edit -->
                                        <button
                                            @click="openEditModal(s)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 transition cursor-pointer"
                                            :title="$t('common.edit')"
                                        >
                                            ✏️
                                        </button>

                                        <!-- Toggle Active -->
                                        <button
                                            @click="toggleActive(s)"
                                            type="button"
                                            class="p-1.5 rounded-xl transition cursor-pointer"
                                            :class="s.is_active ? 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 dark:text-slate-500'"
                                            :title="s.is_active ? $t('common.active') : $t('common.inactive')"
                                        >
                                            {{ s.is_active ? '🟢' : '⚪' }}
                                        </button>

                                        <!-- Delete -->
                                        <button
                                            @click="deleteSupplier(s)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 transition cursor-pointer"
                                            :class="!s.can_be_deleted ? 'opacity-40 cursor-not-allowed' : ''"
                                            :title="s.can_be_deleted ? $t('common.delete') : s.deletion_blockers.join(', ')"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!suppliers.data || suppliers.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">🏭</span>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 font-tajawal">{{ $t('contacts.no_suppliers_found') }}</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="suppliers.links && suppliers.links.length > 3" class="pt-4 border-t border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        {{ $t('common.actions') ? `عرض ${suppliers.from || 0} إلى ${suppliers.to || 0} من إجمالي ${suppliers.total || 0}` : `Showing ${suppliers.from || 0} to ${suppliers.to || 0} of ${suppliers.total || 0}` }}
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in suppliers.links" :key="lIdx">
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
                    <label class="text-xs font-black text-slate-300">🔍 {{ $t('contacts.search_supplier_placeholder') }}</label>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="$t('common.search')"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">💳 {{ $t('contacts.current_balance_status') }}</label>
                    <SearchableSelect
                        v-model="debtStatus"
                        :options="debtStatusOptions"
                        :placeholder="$t('contacts.all_customers')"
                    />
                </div>
            </div>
        </FilterDrawer>

        <!-- Add / Edit Supplier Modal -->
        <div
            v-if="showSupplierModal"
            @click="showSupplierModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-black text-base text-white">
                        {{ editingSupplier ? $t('contacts.supplier_updated') : $t('contacts.add_new_supplier') }}
                    </h3>
                    <button @click="showSupplierModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <form @submit.prevent="saveSupplier" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('contacts.supplier_name') }} *</label>
                        <input
                            v-model="supplierForm.name"
                            type="text"
                            required
                            :placeholder="$t('contacts.supplier_name')"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('contacts.company_name') }}</label>
                            <input
                                v-model="supplierForm.company_name"
                                type="text"
                                :placeholder="$t('contacts.company_placeholder')"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('common.phone') }}</label>
                            <input
                                v-model="supplierForm.phone"
                                type="text"
                                placeholder="01xxxxxxxxx"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('common.address') }}</label>
                        <input
                            v-model="supplierForm.address"
                            type="text"
                            :placeholder="$t('common.address')"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div v-if="!editingSupplier" class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('contacts.opening_balance') }} ({{ $t('common.currency') }})</label>
                        <input
                            v-model="supplierForm.opening_balance"
                            type="number"
                            step="0.001"
                            placeholder="0.00"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('common.notes') }}</label>
                        <textarea
                            v-model="supplierForm.notes"
                            rows="2"
                            class="w-full px-3.5 py-2 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showSupplierModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="supplierForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ supplierForm.processing ? '...' : (editingSupplier ? $t('common.save') : $t('contacts.add_new_supplier')) }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Supplier Payment Modal -->
        <div
            v-if="showPaymentModal"
            @click="showPaymentModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="font-black text-base text-white">{{ $t('contacts.record_disbursement_voucher') }}</h3>
                        <p class="text-xs text-amber-400 font-bold mt-0.5">{{ selectedSupplierForPayment?.name }}</p>
                    </div>
                    <button @click="showPaymentModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <form @submit.prevent="submitPayment" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('contacts.voucher_amount') }} ({{ $t('common.currency') }}) *</label>
                        <input
                            v-model="paymentForm.amount"
                            type="number"
                            step="0.01"
                            required
                            placeholder="0.00"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-sm text-emerald-400 font-mono font-black focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('contacts.payment_method') }} *</label>
                        <SearchableSelect
                            v-model="paymentForm.payment_method"
                            :options="paymentMethodOptions"
                            :placeholder="$t('contacts.payment_method')"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('common.date') }} *</label>
                        <DatePicker
                            v-model="paymentForm.payment_date"
                            :placeholder="$t('common.date')"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('common.notes') }}</label>
                        <input
                            v-model="paymentForm.notes"
                            type="text"
                            :placeholder="$t('common.notes')"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showPaymentModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="paymentForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow-lg shadow-emerald-500/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ paymentForm.processing ? '...' : $t('contacts.record_disbursement_voucher') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>