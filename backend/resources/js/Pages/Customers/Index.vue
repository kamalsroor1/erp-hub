<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import PageHeader from '@/Components/Common/PageHeader.vue';
import MetricCard from '@/Components/Common/MetricCard.vue';
import EmptyState from '@/Components/Common/EmptyState.vue';
import Pagination from '@/Components/Common/Pagination.vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    customers: { type: Object, required: true },
    metrics: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

import { trans } from '@/helpers/trans';

// Search & Filter state
const search = ref(props.filters.search || '');
const debtStatus = ref(props.filters.debt_status || 'all');
const isDrawerOpen = ref(false);

const debtStatusOptions = computed(() => [
    { id: 'all', name: trans('contacts.all_customers') || 'كافة العملاء والحسابات' },
    { id: 'debtor', name: trans('contacts.debtors_only') || 'العملاء المدينون (عليهم مديونية) 🚨' },
    { id: 'zero', name: trans('contacts.settled_only') || 'الحسابات المسواة (رصيد 0) ✅' },
    { id: 'creditor', name: trans('contacts.creditors_only') || 'العملاء الدائنون (لهم رصيد دائن)' },
]);

const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (debtStatus.value && debtStatus.value !== 'all') count++;
    return count;
});

const applyFilters = () => {
    router.get('/customers', {
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

// Add / Edit Customer Modal
const showCustomerModal = ref(false);
const editingCustomer = ref(null);

const customerForm = useForm({
    name: '',
    phone: '',
    address: '',
    tax_number: '',
    opening_balance: '0.000',
    notes: '',
});

const openCreateModal = () => {
    editingCustomer.value = null;
    customerForm.reset();
    customerForm.clearErrors();
    showCustomerModal.value = true;
};

const openEditModal = (c) => {
    editingCustomer.value = c;
    customerForm.clearErrors();
    customerForm.name = c.name;
    customerForm.phone = c.phone || '';
    customerForm.address = c.address || '';
    customerForm.tax_number = c.tax_number || '';
    customerForm.notes = c.notes || '';
    showCustomerModal.value = true;
};

const saveCustomer = () => {
    if (editingCustomer.value) {
        customerForm.put(`/customers/${editingCustomer.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showCustomerModal.value = false;
            }
        });
    } else {
        customerForm.post('/customers', {
            preserveScroll: true,
            onSuccess: () => {
                showCustomerModal.value = false;
            }
        });
    }
};

// Payment Collection Modal
const showPaymentModal = ref(false);
const selectedCustomerForPayment = ref(null);

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

const openPaymentModal = (c) => {
    selectedCustomerForPayment.value = c;
    paymentForm.reset();
    paymentForm.amount = c.current_balance > 0 ? c.current_balance : '';
    paymentForm.payment_date = new Date().toISOString().split('T')[0];
    showPaymentModal.value = true;
};

const submitPayment = () => {
    if (!selectedCustomerForPayment.value) return;
    paymentForm.post(`/customers/${selectedCustomerForPayment.value.id}/payments`, {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false;
        }
    });
};

const deleteCustomer = (c) => {
    if (!c.can_be_deleted) {
        alert('لا يمكن حذف العميل:\n- ' + c.deletion_blockers.join('\n- '));
        return;
    }
    if (confirm(`هل أنت متأكد من حذف العميل (${c.name})؟`)) {
        router.delete(`/customers/${c.id}`, {
            preserveScroll: true,
        });
    }
};

const toggleActive = (c) => {
    router.post(`/customers/${c.id}/toggle-active`, {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('contacts.customers_title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <PageHeader
                :title="$t('contacts.customers_title')"
                :subtitle="$t('contacts.customers_subtitle')"
                icon="👥"
            >
                <template #actions>
                    <button
                        @click="openCreateModal"
                        type="button"
                        class="h-11 px-5 rounded-2xl btn-primary-theme font-bold text-xs flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer shadow-theme-sm"
                    >
                        <span class="text-base font-black">+</span>
                        <span>{{ $t('contacts.add_new_customer') }}</span>
                    </button>
                </template>
            </PageHeader>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <MetricCard
                    :title="$t('contacts.total_customer_debts')"
                    :value="formatMoney(metrics.total_debt)"
                    :currency="$t('common.currency')"
                    variant="danger"
                />

                <MetricCard
                    :title="$t('contacts.debtors_count')"
                    :value="metrics.debtors_count || 0"
                    :currency="$t('contacts.customer_unit')"
                    variant="primary"
                />

                <MetricCard
                    :title="$t('contacts.total_customers_count')"
                    :value="metrics.total_customers || 0"
                    :currency="$t('contacts.customer_unit')"
                    variant="success"
                />
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-xs space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="$t('contacts.search_customer_placeholder')"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-theme-primary focus:outline-none transition shadow-inner"
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
                                :class="debtStatus === 'all' ? 'tab-theme-active' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('common.all') }}
                            </button>
                            <button
                                @click="debtStatus = 'debtor'; applyFilters();"
                                type="button"
                                class="px-2.5 py-1 rounded-xl font-bold transition cursor-pointer"
                                :class="debtStatus === 'debtor' ? 'bg-rose-500 text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                            >
                                {{ $t('contacts.debtors_only') }}
                            </button>
                        </div>

                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                        >
                            <span>⚙️</span>
                            <span>{{ $t('common.filter') }}</span>
                            <span v-if="activeFiltersCount > 0" class="w-5 h-5 rounded-full bg-theme-primary text-white font-mono font-black text-[11px] flex items-center justify-center">
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Customers Table & Mobile Cards -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-3.5 sm:p-5 shadow-xs space-y-4 overflow-hidden font-tajawal">
                <!-- Desktop Table (Hidden on Mobile) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('invoices.customer') }}</th>
                                <th class="pb-3">{{ $t('contacts.phone') }}</th>
                                <th class="pb-3">{{ $t('contacts.address') }}</th>
                                <th class="pb-3 font-mono">{{ $t('contacts.current_balance') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                            <tr v-for="c in customers.data" :key="c.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <!-- Name -->
                                <td class="py-3.5">
                                    <div class="font-black text-slate-900 dark:text-white font-tajawal text-sm">{{ c.name }}</div>
                                    <div v-if="c.notes" class="text-[10px] text-slate-500 font-tajawal">{{ c.notes }}</div>
                                </td>

                                <!-- Phone -->
                                <td class="py-3.5 font-mono text-slate-600 dark:text-slate-300" dir="ltr">
                                    {{ c.phone || '—' }}
                                </td>

                                <!-- Address -->
                                <td class="py-3.5 font-tajawal text-slate-500 dark:text-slate-400">
                                    {{ c.address || '—' }}
                                </td>

                                <!-- Balance -->
                                <td class="py-3.5 font-mono font-black text-sm">
                                    <span
                                        class="px-2.5 py-1 rounded-xl border"
                                        :class="[
                                            c.current_balance > 0 ? 'bg-rose-500/15 border-rose-500/30 text-rose-600 dark:text-rose-400' :
                                            (c.current_balance < 0 ? 'bg-indigo-500/15 border-indigo-500/30 text-indigo-600 dark:text-indigo-400' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400')
                                        ]"
                                    >
                                        {{ formatMoney(c.current_balance) }} {{ $t('common.currency') }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 font-tajawal">
                                        <!-- Quick Payment Voucher -->
                                        <button
                                            @click="openPaymentModal(c)"
                                            type="button"
                                            class="px-2.5 py-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-bold transition cursor-pointer flex items-center gap-1"
                                            :title="$t('contacts.record_receipt_voucher')"
                                        >
                                            <span>💵</span>
                                            <span>{{ $t('contacts.record_receipt_voucher') }}</span>
                                        </button>

                                        <!-- Statement -->
                                        <Link
                                            :href="`/customers/${c.id}/statement`"
                                            class="p-1.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/30 text-indigo-600 dark:text-indigo-400 transition"
                                            :title="$t('contacts.statement_title')"
                                        >
                                            📜
                                        </Link>

                                        <!-- Edit -->
                                        <button
                                            @click="openEditModal(c)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 transition cursor-pointer"
                                            :title="$t('common.edit')"
                                        >
                                            ✏️
                                        </button>

                                        <!-- Toggle Active -->
                                        <button
                                            @click="toggleActive(c)"
                                            type="button"
                                            class="p-1.5 rounded-xl transition cursor-pointer"
                                            :class="c.is_active ? 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 dark:text-slate-500'"
                                            :title="c.is_active ? $t('common.active') : $t('common.inactive')"
                                        >
                                            {{ c.is_active ? '🟢' : '⚪' }}
                                        </button>

                                        <!-- Delete -->
                                        <button
                                            @click="deleteCustomer(c)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 transition cursor-pointer"
                                            :class="!c.can_be_deleted ? 'opacity-40 cursor-not-allowed' : ''"
                                            :title="c.can_be_deleted ? $t('common.delete') : c.deletion_blockers.join(', ')"
                                        >
                                            🗑️
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
                        v-for="c in customers.data"
                        :key="c.id"
                        class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 space-y-3 shadow-xs font-tajawal"
                    >
                        <!-- Top Row: Name + Phone -->
                        <div class="flex items-start justify-between gap-2 border-b border-slate-200 dark:border-slate-800/80 pb-2.5">
                            <div class="space-y-0.5">
                                <div class="font-black text-sm text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>{{ c.name }}</span>
                                    <span :class="c.is_active ? 'text-emerald-500' : 'text-slate-400'" class="text-xs">●</span>
                                </div>
                                <p v-if="c.phone" class="text-xs text-slate-400 font-mono" dir="ltr">{{ c.phone }}</p>
                            </div>

                            <span
                                class="px-2.5 py-1 rounded-xl border font-mono font-black text-xs"
                                :class="[
                                    c.current_balance > 0 ? 'bg-rose-500/15 border-rose-500/30 text-rose-600 dark:text-rose-400' :
                                    (c.current_balance < 0 ? 'bg-indigo-500/15 border-indigo-500/30 text-indigo-600 dark:text-indigo-400' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400')
                                ]"
                            >
                                {{ formatMoney(c.current_balance) }} {{ $t('common.currency') }}
                            </span>
                        </div>

                        <!-- Address if present -->
                        <div v-if="c.address" class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <span>📍</span>
                            <span>{{ c.address }}</span>
                        </div>

                        <!-- Mobile Action Bar -->
                        <div class="flex items-center justify-between pt-1 gap-2">
                            <!-- Quick Payment Button -->
                            <button
                                @click="openPaymentModal(c)"
                                type="button"
                                class="flex-1 h-10 px-3 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-black transition active:scale-95 cursor-pointer flex items-center justify-center gap-1.5 shadow-xs"
                            >
                                <span>💵</span>
                                <span>{{ $t('contacts.record_receipt_voucher') }}</span>
                            </button>

                            <Link
                                :href="`/customers/${c.id}/statement`"
                                class="w-10 h-10 rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30 flex items-center justify-center transition active:scale-90 cursor-pointer shadow-xs"
                                :title="$t('contacts.statement_title')"
                            >
                                📜
                            </Link>

                            <button
                                @click="openEditModal(c)"
                                type="button"
                                class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 flex items-center justify-center transition active:scale-90 cursor-pointer shadow-xs"
                                :title="$t('common.edit')"
                            >
                                ✏️
                            </button>

                            <button
                                @click="deleteCustomer(c)"
                                type="button"
                                class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30 flex items-center justify-center transition active:scale-90 cursor-pointer shadow-xs"
                                :class="!c.can_be_deleted ? 'opacity-40 cursor-not-allowed' : ''"
                                :title="c.can_be_deleted ? $t('common.delete') : c.deletion_blockers.join(', ')"
                            >
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <EmptyState
                    v-if="!customers.data || customers.data.length === 0"
                    icon="👥"
                    :title="$t('contacts.no_customers_found')"
                    :action-label="$t('contacts.add_new_customer')"
                    @action="openCreateModal"
                />

                <!-- Pagination -->
                <Pagination
                    :links="customers.links"
                    :from="customers.from"
                    :to="customers.to"
                    :total="customers.total"
                />
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
                    <label class="text-xs font-black text-slate-300">🔍 {{ $t('contacts.search_customer_placeholder') }}</label>
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

        <!-- Add / Edit Customer Modal (Smooth Native Pop) -->
        <Teleport to="body">
            <Transition name="modal-zoom">
                <div
                    v-if="showCustomerModal"
                    @click="showCustomerModal = false"
                    class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 font-tajawal select-none"
                    dir="rtl"
                >
                    <div @click.stop class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                            <h3 class="font-black text-base text-slate-900 dark:text-white">
                                {{ editingCustomer ? $t('contacts.customer_updated') : $t('contacts.add_new_customer') }}
                            </h3>
                            <button
                                @click="showCustomerModal = false"
                                class="w-9 h-9 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-400 text-xs hover:text-slate-900 dark:hover:text-white cursor-pointer flex items-center justify-center transition active:scale-90 shadow-xs"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <form @submit.prevent="saveCustomer" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('contacts.customer_name') }} *</label>
                                <input
                                    v-model="customerForm.name"
                                    type="text"
                                    required
                                    :placeholder="$t('contacts.customer_name')"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('common.phone') }}</label>
                                    <input
                                        v-model="customerForm.phone"
                                        type="tel"
                                        inputmode="tel"
                                        placeholder="01xxxxxxxxx"
                                        class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white font-mono focus:border-amber-500 focus:outline-none shadow-inner"
                                    >
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('invoices.tax_number') || 'الرقم الضريبي' }}</label>
                                    <input
                                        v-model="customerForm.tax_number"
                                        type="text"
                                        class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white font-mono focus:border-amber-500 focus:outline-none shadow-inner"
                                    >
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('common.address') }}</label>
                                <input
                                    v-model="customerForm.address"
                                    type="text"
                                    :placeholder="$t('common.address')"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none shadow-inner"
                                >
                            </div>

                            <div v-if="!editingCustomer" class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('contacts.opening_balance') }} ({{ $t('common.currency') }})</label>
                                <input
                                    v-model="customerForm.opening_balance"
                                    type="number"
                                    step="0.001"
                                    placeholder="0.00"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white font-mono focus:border-amber-500 focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('common.notes') }}</label>
                                <textarea
                                    v-model="customerForm.notes"
                                    rows="2"
                                    class="w-full p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none shadow-inner"
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                                <button
                                    @click="showCustomerModal = false"
                                    type="button"
                                    class="h-11 px-5 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition active:scale-95 cursor-pointer shadow-xs"
                                >
                                    {{ $t('common.cancel') }}
                                </button>
                                <button
                                    type="submit"
                                    :disabled="customerForm.processing"
                                    class="h-11 px-6 rounded-2xl btn-primary-theme text-xs font-black transition transform active:scale-95 cursor-pointer disabled:opacity-50 shadow-theme-primary"
                                >
                                    {{ customerForm.processing ? '...' : (editingCustomer ? $t('common.save') : $t('contacts.add_new_customer')) }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Payment Collection Voucher Modal (Smooth Native Pop) -->
        <Teleport to="body">
            <Transition name="modal-zoom">
                <div
                    v-if="showPaymentModal"
                    @click="showPaymentModal = false"
                    class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 font-tajawal select-none"
                    dir="rtl"
                >
                    <div @click.stop class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                            <div>
                                <h3 class="font-black text-base text-slate-900 dark:text-white">{{ $t('contacts.record_receipt_voucher') }}</h3>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">{{ selectedCustomerForPayment?.name }}</p>
                            </div>
                            <button
                                @click="showPaymentModal = false"
                                class="w-9 h-9 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-400 text-xs hover:text-slate-900 dark:hover:text-white cursor-pointer flex items-center justify-center transition active:scale-90 shadow-xs"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <form @submit.prevent="submitPayment" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('contacts.voucher_amount') }} ({{ $t('common.currency') }}) *</label>
                                <input
                                    v-model="paymentForm.amount"
                                    type="number"
                                    step="0.01"
                                    required
                                    placeholder="0.00"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-sm text-emerald-600 dark:text-emerald-400 font-mono font-black focus:border-amber-500 focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('contacts.payment_method') }} *</label>
                                <SearchableSelect
                                    v-model="paymentForm.payment_method"
                                    :options="paymentMethodOptions"
                                    :placeholder="$t('contacts.payment_method')"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('common.date') }} *</label>
                                <DatePicker
                                    v-model="paymentForm.payment_date"
                                    :placeholder="$t('common.date')"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('common.notes') }}</label>
                                <input
                                    v-model="paymentForm.notes"
                                    type="text"
                                    :placeholder="$t('common.notes')"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                                <button
                                    @click="showPaymentModal = false"
                                    type="button"
                                    class="h-11 px-5 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition active:scale-95 cursor-pointer shadow-xs"
                                >
                                    {{ $t('common.cancel') }}
                                </button>
                                <button
                                    type="submit"
                                    :disabled="paymentForm.processing"
                                    class="h-11 px-6 rounded-2xl btn-primary-theme text-xs font-black shadow-theme-primary transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                                >
                                    {{ paymentForm.processing ? '...' : $t('contacts.record_receipt_voucher') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
