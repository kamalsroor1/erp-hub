<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import FilterDrawer from '@/Components/FilterDrawer.vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';

const props = defineProps({
    expenses: { type: Object, required: true },
    metrics: { type: Object, default: () => ({}) },
    cost_centers: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
});

const { formatMoney } = useMoney();

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || 'all');
const costCenter = ref(props.filters.cost_center || 'all');
const paymentMethod = ref(props.filters.payment_method || 'all');
const dateFrom = ref(props.filters.from || '');
const dateTo = ref(props.filters.to || '');
const isDrawerOpen = ref(false);

const quickCategories = computed(() => [
    trans('expenses.preset_packaging') || 'شنط وأكياس وتغليف',
    'أكواب ورقية وبلاستيكية',
    'لاصق وشرائط تغليف',
    trans('expenses.cc_hospitality') || 'بوفيه وضيافة',
    trans('expenses.cc_maintenance') || 'صيانة مطاحن ومعدات',
    trans('expenses.cc_rent') || 'إيجار وكهرباء ومرافق',
    trans('expenses.cc_operational') || 'نثريات ومصاريف تشغيل',
]);

const costCenterOptions = computed(() => {
    return [
        { id: 'all', name: trans('expenses.all_cost_centers') || 'كافة مراكز التكلفة' },
        ...Object.entries(props.cost_centers).map(([k, v]) => ({ id: k, name: v }))
    ];
});

const paymentMethodOptions = computed(() => [
    { id: 'all', name: trans('expenses.all_payment_methods') || 'كافة طرق الدفع' },
    { id: 'cash', name: `${trans('treasury.cash_drawer') || 'نقداً كاش'} 💵` },
    { id: 'instapay', name: `${trans('treasury.instapay') || 'انستاباي'} ⚡` },
    { id: 'e_wallet', name: `${trans('treasury.e_wallet') || 'محفظة إلكترونية'} 📱` },
    { id: 'visa', name: `${trans('treasury.visa') || 'فيزا وبطاقة بنكية'} 💳` },
    { id: 'bank_transfer', name: `${trans('treasury.bank_transfer') || 'تحويل بنكي'} 🏦` },
    { id: 'check', name: 'شيك 📄' },
]);

const activeFiltersCount = computed(() => {
    let count = 0;
    if (search.value) count++;
    if (category.value !== 'all') count++;
    if (costCenter.value !== 'all') count++;
    if (paymentMethod.value !== 'all') count++;
    if (dateFrom.value || dateTo.value) count++;
    return count;
});

const applyFilters = () => {
    router.get('/expenses', {
        search: search.value || undefined,
        category: category.value !== 'all' ? category.value : undefined,
        cost_center: costCenter.value !== 'all' ? costCenter.value : undefined,
        payment_method: paymentMethod.value !== 'all' ? paymentMethod.value : undefined,
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
    category.value = 'all';
    costCenter.value = 'all';
    paymentMethod.value = 'all';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

// Add / Edit Modal
const showModal = ref(false);
const editingExpense = ref(null);

const expenseForm = useForm({
    title: '',
    category: 'نثريات ومصاريف تشغيل',
    cost_center: 'operational',
    amount: '',
    expense_date: new Date().toISOString().split('T')[0],
    payment_method: 'cash',
    notes: '',
});

const openCreateModal = () => {
    editingExpense.value = null;
    expenseForm.reset();
    expenseForm.clearErrors();
    expenseForm.expense_date = new Date().toISOString().split('T')[0];
    showModal.value = true;
};

const openEditModal = (e) => {
    editingExpense.value = e;
    expenseForm.clearErrors();
    expenseForm.title = e.title;
    expenseForm.category = e.category;
    expenseForm.cost_center = e.cost_center;
    expenseForm.amount = e.amount;
    expenseForm.expense_date = e.expense_date;
    expenseForm.payment_method = e.payment_method;
    expenseForm.notes = e.notes || '';
    showModal.value = true;
};

const saveExpense = () => {
    if (editingExpense.value) {
        expenseForm.put(`/expenses/${editingExpense.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
            }
        });
    } else {
        expenseForm.post('/expenses', {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
            }
        });
    }
};

const deleteExpense = (e) => {
    const confirmMsg = trans('expenses.delete_confirm', { title: e.title }) || `هل أنت متأكد من حذف المصروف (${e.title})؟`;
    if (confirm(confirmMsg)) {
        router.delete(`/expenses/${e.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="$t('expenses.title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">💸</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            {{ $t('expenses.title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        {{ $t('expenses.expenses_breakdown') }}
                    </p>
                </div>

                <button
                    @click="openCreateModal"
                    type="button"
                    class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                >
                    <span class="text-base font-black">+</span>
                    <span>{{ $t('expenses.add_expense') }}</span>
                </button>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">{{ $t('expenses.total_month') }}</span>
                    <div class="text-2xl font-black font-mono text-rose-400">
                        {{ formatMoney(metrics.total_month) }} <span class="text-xs text-white">{{ $t('common.currency') }}</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">{{ $t('expenses.total_cash') }}</span>
                    <div class="text-2xl font-black font-mono text-amber-400">
                        {{ formatMoney(metrics.total_cash) }} <span class="text-xs text-white">{{ $t('common.currency') }}</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-2">
                    <span class="text-xs text-slate-400 font-bold">{{ $t('expenses.total_filtered') }}</span>
                    <div class="text-2xl font-black font-mono text-white">
                        {{ formatMoney(metrics.total_filtered) }} <span class="text-xs text-amber-400">{{ $t('common.currency') }}</span>
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
                            :placeholder="$t('expenses.search_placeholder')"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-2xl text-xs text-white placeholder:text-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                            🔍
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            @click="isDrawerOpen = true"
                            type="button"
                            class="h-10 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold flex items-center gap-2 transition cursor-pointer"
                        >
                            <span>⚙️</span>
                            <span>{{ $t('common.advanced_filter') }}</span>
                            <span v-if="activeFiltersCount > 0" class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 font-mono font-black text-[11px] flex items-center justify-center">
                                {{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Expenses Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('invoices.invoice_number') }}</th>
                                <th class="pb-3">{{ $t('expenses.expense_item') }}</th>
                                <th class="pb-3">{{ $t('expenses.cost_center') }} & {{ $t('expenses.category') }}</th>
                                <th class="pb-3">{{ $t('common.date') }}</th>
                                <th class="pb-3 font-mono">{{ $t('common.amount') }}</th>
                                <th class="pb-3">{{ $t('invoices.payment_method') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-sans">
                            <tr v-for="e in expenses.data" :key="e.id" class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 font-mono font-bold text-amber-400">
                                    {{ e.expense_number }}
                                </td>

                                <td class="py-3.5">
                                    <div class="font-black text-white font-tajawal">{{ e.title }}</div>
                                    <div v-if="e.notes" class="text-[10px] text-slate-500 font-tajawal">{{ e.notes }}</div>
                                </td>

                                <td class="py-3.5 font-tajawal">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 text-[10px] font-bold">
                                        {{ e.cost_center_label }}
                                    </span>
                                    <div class="text-[10px] text-amber-400/80 font-bold mt-0.5">{{ e.category }}</div>
                                </td>

                                <td class="py-3.5 font-mono text-slate-400 text-[11px]">
                                    {{ e.expense_date }}
                                </td>

                                <td class="py-3.5 font-mono font-black text-rose-400 text-sm">
                                    {{ formatMoney(e.amount) }} {{ $t('common.currency') }}
                                </td>

                                <td class="py-3.5 font-tajawal">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300">
                                        {{ e.payment_method === 'cash' ? `${$t('treasury.cash_drawer')} 💵` : e.payment_method }}
                                    </span>
                                </td>

                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 font-tajawal">
                                        <button
                                            @click="openEditModal(e)"
                                            type="button"
                                            class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs font-bold transition cursor-pointer"
                                        >
                                            {{ $t('common.edit') }} ✏️
                                        </button>

                                        <button
                                            @click="deleteExpense(e)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition cursor-pointer"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!expenses.data || expenses.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">💸</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">{{ $t('expenses.no_expenses') }}</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="expenses.links && expenses.links.length > 3" class="pt-4 border-t border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        {{ $t('common.showing') }} {{ expenses.from || 0 }} {{ $t('common.to') }} {{ expenses.to || 0 }} {{ $t('common.of') }} {{ expenses.total || 0 }}
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in expenses.links" :key="lIdx">
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
                    <label class="text-xs font-black text-slate-300">🔍 {{ $t('common.search') }}</label>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="$t('expenses.search_placeholder')"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">🏢 {{ $t('expenses.cost_center') }}</label>
                    <SearchableSelect
                        v-model="costCenter"
                        :options="costCenterOptions"
                        :placeholder="$t('expenses.cost_center')"
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-300">💳 {{ $t('invoices.payment_method') }}</label>
                    <SearchableSelect
                        v-model="paymentMethod"
                        :options="paymentMethodOptions"
                        :placeholder="$t('invoices.payment_method')"
                    />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-300">{{ $t('common.date_from') }}</label>
                        <DatePicker v-model="dateFrom" :placeholder="$t('common.date_from')" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-black text-slate-300">{{ $t('common.date_to') }}</label>
                        <DatePicker v-model="dateTo" :placeholder="$t('common.date_to')" />
                    </div>
                </div>
            </div>
        </FilterDrawer>

        <!-- Add / Edit Expense Modal -->
        <div
            v-if="showModal"
            @click="showModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-black text-base text-white">
                        {{ editingExpense ? $t('expenses.edit_expense') : $t('expenses.new_expense') }}
                    </h3>
                    <button @click="showModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <form @submit.prevent="saveExpense" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('expenses.expense_item') }} *</label>
                        <input
                            v-model="expenseForm.title"
                            type="text"
                            required
                            placeholder="مثال: شراء كراتين شحن / صيانة طاحونة رقم 2..."
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('expenses.amount') }} *</label>
                            <input
                                v-model.number="expenseForm.amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                placeholder="0.00"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs font-mono font-black text-rose-400 focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('common.date') }} *</label>
                            <DatePicker v-model="expenseForm.expense_date" :placeholder="$t('common.date')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('expenses.cost_center') }} *</label>
                            <select
                                v-model="expenseForm.cost_center"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                                <option v-for="(label, key) in cost_centers" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('invoices.payment_method') }} *</label>
                            <select
                                v-model="expenseForm.payment_method"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                                <option value="cash">{{ $t('treasury.cash_drawer') }} 💵</option>
                                <option value="instapay">{{ $t('treasury.instapay') }} ⚡</option>
                                <option value="e_wallet">{{ $t('treasury.e_wallet') }} 📱</option>
                                <option value="visa">{{ $t('treasury.visa') }} 💳</option>
                                <option value="bank_transfer">{{ $t('treasury.bank_transfer') }} 🏦</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('expenses.quick_category') }}</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="c in quickCategories"
                                :key="c"
                                @click="expenseForm.category = c"
                                type="button"
                                class="px-2.5 py-1 rounded-xl text-[11px] font-bold border transition cursor-pointer"
                                :class="expenseForm.category === c ? 'bg-amber-500/20 text-amber-400 border-amber-500/40' : 'bg-slate-950 text-slate-400 border-slate-800 hover:text-white'"
                            >
                                {{ c }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">{{ $t('invoices.notes') }}</label>
                        <input
                            v-model="expenseForm.notes"
                            type="text"
                            :placeholder="$t('invoices.notes')"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="expenseForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ expenseForm.processing ? $t('common.save') + '...' : $t('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>