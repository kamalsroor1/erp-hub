<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';

const props = defineProps({
    suppliers: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
    prefill_items: { type: Array, default: () => [] },
});

const { formatMoney } = useMoney();

const initialItems = (props.prefill_items && props.prefill_items.length > 0)
    ? props.prefill_items.map(p => ({
        item_id: p.item_id,
        name: p.name,
        unit: p.unit || trans('inventory.unit_kg') || 'كجم',
        quantity: p.quantity || 10,
        unit_cost: Number(p.unit_cost) || 0,
    }))
    : [];

const form = useForm({
    supplier_id: props.suppliers[0]?.id || null,
    purchase_date: new Date().toISOString().split('T')[0],
    supplier_invoice_ref: '',
    paid_amount: '0.00',
    discount_amount: '0.00',
    notes: '',
    items: initialItems,
});

const availableItemOptions = computed(() => {
    return props.items.map(item => ({
        id: item.id,
        name: `${item.name} (${trans('inventory.current_stock') || 'المتوفر'}: ${item.current_stock} ${item.unit || ''}) - ${trans('inventory.cost_price') || 'سعر التكلفة'}: ${item.cost_price}`,
    }));
});

const selectedItemToAdd = ref(null);

const addItemRow = () => {
    if (!selectedItemToAdd.value) return;
    const item = props.items.find(i => i.id === selectedItemToAdd.value);
    if (!item) return;

    if (form.items.some(it => it.item_id === item.id)) {
        alert(trans('purchases.item_already_added') || 'هذا الصنف مضاف بالفعل بالفاتورة');
        return;
    }

    form.items.push({
        item_id: item.id,
        name: item.name,
        unit: item.unit || trans('inventory.unit_kg') || 'كجم',
        quantity: 10,
        unit_cost: Number(item.cost_price) || 0,
    });

    selectedItemToAdd.value = null;
};

const removeItemRow = (index) => {
    form.items.splice(index, 1);
};

const subtotal = computed(() => {
    return form.items.reduce((sum, it) => sum + ((Number(it.quantity) || 0) * (Number(it.unit_cost) || 0)), 0);
});

const netTotal = computed(() => {
    const sub = subtotal.value;
    const disc = Number(form.discount_amount) || 0;
    return Math.max(sub - disc, 0);
});

const remainingAmount = computed(() => {
    const net = netTotal.value;
    const paid = Number(form.paid_amount) || 0;
    return Math.max(net - paid, 0);
});

const submitPurchase = () => {
    if (form.items.length === 0) {
        alert(trans('purchases.add_at_least_one') || 'يرجى إضافة صنف واحد على الأقل لفاتورة الشراء');
        return;
    }
    form.post('/purchases', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('purchases.create_po_title')" />

    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/purchases" class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center font-bold text-sm transition active:scale-90 shadow-xs border border-slate-200 dark:border-transparent">
                            →
                        </Link>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            {{ $t('purchases.create_po_title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                        {{ $t('purchases.create_po_subtitle') }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitPurchase" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Cols: Form Info & Items Table -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Supplier & Invoice Details -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-6 shadow-xs space-y-4">
                        <h2 class="text-sm font-black text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2.5 flex items-center gap-2">
                            <span>🏢</span>
                            <span>{{ $t('purchases.supplier') }} & {{ $t('purchases.purchase_date') }}</span>
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('purchases.supplier') }} *</label>
                                <SearchableSelect
                                    v-model="form.supplier_id"
                                    :options="suppliers"
                                    :placeholder="$t('purchases.select_supplier')"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('purchases.purchase_date') }} *</label>
                                <DatePicker v-model="form.purchase_date" :placeholder="$t('purchases.purchase_date')" />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('purchases.supplier_invoice_ref') }}</label>
                                <input
                                    v-model="form.supplier_invoice_ref"
                                    type="text"
                                    placeholder="INV-9908"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white font-mono focus:border-amber-500 focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('invoices.notes') }}</label>
                                <input
                                    v-model="form.notes"
                                    type="text"
                                    :placeholder="$t('invoices.notes')"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none shadow-inner"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Items Selection -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-6 shadow-xs space-y-4">
                        <h2 class="text-sm font-black text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2.5 flex items-center gap-2">
                            <span>📦</span>
                            <span>{{ $t('purchases.items_to_supply') }}</span>
                        </h2>

                        <!-- Add Item Row Selector -->
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <SearchableSelect
                                    v-model="selectedItemToAdd"
                                    :options="availableItemOptions"
                                    :placeholder="$t('purchases.search_placeholder')"
                                />
                            </div>
                            <button
                                @click="addItemRow"
                                type="button"
                                class="h-11 px-5 rounded-2xl btn-primary-theme text-xs font-black transition active:scale-95 cursor-pointer shrink-0 shadow-theme-primary"
                            >
                                + {{ $t('common.add') }}
                            </button>
                        </div>

                        <!-- Items Table -->
                        <div class="overflow-x-auto pt-2">
                            <table class="w-full text-right text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                        <th class="pb-2.5">{{ $t('inventory.item_name') }}</th>
                                        <th class="pb-2.5 w-28">{{ $t('common.quantity') }}</th>
                                        <th class="pb-2.5 w-28">{{ $t('purchases.unit_cost') }}</th>
                                        <th class="pb-2.5 font-mono">{{ $t('common.total') }}</th>
                                        <th class="pb-2.5 text-center w-10">✕</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                                    <tr v-for="(it, itIdx) in form.items" :key="it.item_id">
                                        <td class="py-3 font-bold text-slate-900 dark:text-white font-tajawal">
                                            {{ it.name }}
                                            <span class="text-[10px] text-slate-500 mr-1">({{ it.unit }})</span>
                                        </td>

                                        <td class="py-3">
                                            <input
                                                v-model.number="it.quantity"
                                                type="number"
                                                step="0.001"
                                                min="0.001"
                                                required
                                                class="w-24 h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-theme-primary focus:outline-none shadow-inner"
                                            >
                                        </td>

                                        <td class="py-3">
                                            <input
                                                v-model.number="it.unit_cost"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                class="w-24 h-10 px-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none shadow-inner"
                                            >
                                        </td>

                                        <td class="py-3 font-mono font-black text-emerald-600 dark:text-emerald-400">
                                            {{ formatMoney((it.quantity || 0) * (it.unit_cost || 0)) }} {{ $t('common.currency') }}
                                        </td>

                                        <td class="py-3 text-center">
                                            <button
                                                @click="removeItemRow(itIdx)"
                                                type="button"
                                                class="w-9 h-9 rounded-xl bg-rose-500/15 hover:bg-rose-500/30 text-rose-600 dark:text-rose-400 flex items-center justify-center transition active:scale-90 cursor-pointer"
                                            >
                                                ✕
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div v-if="form.items.length === 0" class="py-8 text-center text-slate-400 text-xs font-bold font-tajawal">
                                {{ $t('purchases.empty_items') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Col: Financial Summary & Confirmation -->
                <div class="space-y-5">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 sticky top-20">
                        <h2 class="text-base font-black text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-3">{{ $t('purchases.payment_summary') }}</h2>

                        <div class="space-y-3 font-mono">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400 font-tajawal">{{ $t('common.subtotal') }}:</span>
                                <span class="text-slate-900 dark:text-white font-bold">{{ formatMoney(subtotal) }} {{ $t('common.currency') }}</span>
                            </div>

                            <div class="space-y-1.5 pt-2 border-t border-slate-200 dark:border-slate-800">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300 font-tajawal">{{ $t('common.discount') }} ({{ $t('common.currency') }})</label>
                                <input
                                    v-model.number="form.discount_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-theme-primary font-mono font-bold focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200 dark:border-slate-800">
                                <span class="text-slate-700 dark:text-slate-300 font-bold font-tajawal">{{ $t('common.net') }}:</span>
                                <span class="text-base font-black text-theme-primary">{{ formatMoney(netTotal) }} {{ $t('common.currency') }}</span>
                            </div>

                            <div class="space-y-1.5 pt-2 border-t border-slate-200 dark:border-slate-800">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300 font-tajawal">{{ $t('common.paid') }} ({{ $t('common.currency') }})</label>
                                <input
                                    v-model.number="form.paid_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-emerald-600 dark:text-emerald-400 font-mono font-black focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200 dark:border-slate-800">
                                <span class="text-slate-500 dark:text-slate-400 font-tajawal">{{ $t('common.remaining') }}:</span>
                                <span class="text-rose-600 dark:text-rose-400 font-black">{{ formatMoney(remainingAmount) }} {{ $t('common.currency') }}</span>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing || form.items.length === 0"
                            class="w-full h-12 rounded-2xl btn-primary-theme font-black text-sm flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer disabled:opacity-50 shadow-theme-primary"
                        >
                            <span>📥</span>
                            <span>{{ form.processing ? $t('common.save') + '...' : $t('purchases.confirm_purchase') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
