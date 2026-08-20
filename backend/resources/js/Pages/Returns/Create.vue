<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';

const props = defineProps({
    customers: { type: Array, default: () => [] },
    suppliers: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
});

const { formatMoney } = useMoney();

const form = useForm({
    return_type: 'sales_return', // sales_return or purchase_return
    customer_id: props.customers[0]?.id || null,
    supplier_id: props.suppliers[0]?.id || null,
    return_date: new Date().toISOString().split('T')[0],
    refund_amount: '0.00',
    reason: '',
    items: [],
});

const customerOptions = computed(() => {
    return props.customers.map(c => ({
        id: c.id,
        name: `${c.name} ${c.phone ? '(' + c.phone + ')' : ''}`,
    }));
});

const supplierOptions = computed(() => {
    return props.suppliers.map(s => ({
        id: s.id,
        name: `${s.name} ${s.phone ? '(' + s.phone + ')' : ''}`,
    }));
});

const availableItemOptions = computed(() => {
    return props.items.map(item => ({
        id: item.id,
        name: `${item.name} (${item.unit || ''}) - ${trans('inventory.selling_price') || 'سعر البيع'}: ${item.selling_price} | ${trans('inventory.cost_price') || 'التكلفة'}: ${item.cost_price}`,
    }));
});

const selectedItemToAdd = ref(null);

const addItemRow = () => {
    if (!selectedItemToAdd.value) return;
    const item = props.items.find(i => i.id === selectedItemToAdd.value);
    if (!item) return;

    if (form.items.some(it => it.item_id === item.id)) {
        alert(trans('returns.item_already_added') || 'هذا الصنف مضاف بالفعل بالمستند');
        return;
    }

    const defaultPrice = form.return_type === 'sales_return' ? Number(item.selling_price) : Number(item.cost_price);

    form.items.push({
        item_id: item.id,
        name: item.name,
        unit: item.unit || trans('inventory.unit_kg') || 'كجم',
        quantity: 1,
        unit_price: defaultPrice || 0,
    });

    selectedItemToAdd.value = null;
};

const removeItemRow = (index) => {
    form.items.splice(index, 1);
};

const netTotal = computed(() => {
    return form.items.reduce((sum, it) => sum + ((Number(it.quantity) || 0) * (Number(it.unit_price) || 0)), 0);
});

const submitReturn = () => {
    if (form.items.length === 0) {
        alert(trans('returns.add_at_least_one_item') || 'يرجى إضافة صنف واحد على الأقل للمرتجع');
        return;
    }
    form.post('/returns', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('returns.create_title')" />

    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/returns" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            →
                        </Link>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            {{ $t('returns.create_title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        {{ $t('returns.create_subtitle') }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submitReturn" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Cols: Form Info & Items Table -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Return Type & Party -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                        <h2 class="text-sm font-black text-white border-b border-slate-800 pb-2 flex items-center gap-2">
                            <span>🔄</span>
                            <span>{{ $t('returns.return_type') }} & {{ $t('returns.party_name') }}</span>
                        </h2>

                        <!-- Type Selector Pill -->
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                @click="form.return_type = 'sales_return'"
                                type="button"
                                class="py-3 px-4 rounded-2xl border text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
                                :class="form.return_type === 'sales_return' ? 'bg-rose-500 text-white font-black border-rose-400 shadow-md shadow-rose-500/20' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-white'"
                            >
                                <span>↩️</span>
                                <span>{{ $t('returns.sales_return') }}</span>
                            </button>

                            <button
                                @click="form.return_type = 'purchase_return'"
                                type="button"
                                class="py-3 px-4 rounded-2xl border text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer"
                                :class="form.return_type === 'purchase_return' ? 'bg-emerald-500 text-slate-950 font-black border-emerald-400 shadow-md shadow-emerald-500/20' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-white'"
                            >
                                <span>↪️</span>
                                <span>{{ $t('returns.purchase_return') }}</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div v-if="form.return_type === 'sales_return'" class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">{{ $t('returns.customer_from') }}</label>
                                <SearchableSelect
                                    v-model="form.customer_id"
                                    :options="customerOptions"
                                    :placeholder="$t('invoices.select_customer')"
                                />
                            </div>

                            <div v-else class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">{{ $t('returns.supplier_to') }}</label>
                                <SearchableSelect
                                    v-model="form.supplier_id"
                                    :options="supplierOptions"
                                    :placeholder="$t('suppliers.select_supplier') || 'اختر المورد...'"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-300">{{ $t('returns.return_date') }} *</label>
                                <DatePicker v-model="form.return_date" :placeholder="$t('returns.return_date')" />
                            </div>

                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="text-xs font-bold text-slate-300">{{ $t('returns.reason') }}</label>
                                <input
                                    v-model="form.reason"
                                    type="text"
                                    :placeholder="$t('returns.reason_placeholder')"
                                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Items Selection -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-sm space-y-4">
                        <h2 class="text-sm font-black text-white border-b border-slate-800 pb-2 flex items-center gap-2">
                            <span>📦</span>
                            <span>{{ $t('returns.return_items') }}</span>
                        </h2>

                        <!-- Add Item Row Selector -->
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <SearchableSelect
                                    v-model="selectedItemToAdd"
                                    :options="availableItemOptions"
                                    :placeholder="$t('returns.select_item_to_add')"
                                />
                            </div>
                            <button
                                @click="addItemRow"
                                type="button"
                                class="h-10 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition cursor-pointer"
                            >
                                + {{ $t('common.add') }}
                            </button>
                        </div>

                        <!-- Items Table -->
                        <div class="overflow-x-auto pt-2">
                            <table class="w-full text-right text-xs">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-400 font-bold">
                                        <th class="pb-2">{{ $t('inventory.item_name') }}</th>
                                        <th class="pb-2 w-28">{{ $t('common.quantity') }}</th>
                                        <th class="pb-2 w-28">{{ $t('invoices.unit_price') }}</th>
                                        <th class="pb-2 font-mono">{{ $t('common.total') }}</th>
                                        <th class="pb-2 text-center w-10">✕</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60 font-sans">
                                    <tr v-for="(it, itIdx) in form.items" :key="it.item_id">
                                        <td class="py-2.5 font-bold text-white font-tajawal">
                                            {{ it.name }}
                                            <span class="text-[10px] text-slate-500 mr-1">({{ it.unit }})</span>
                                        </td>

                                        <td class="py-2.5">
                                            <input
                                                v-model.number="it.quantity"
                                                type="number"
                                                step="0.001"
                                                min="0.001"
                                                required
                                                class="w-24 px-2 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-mono font-bold text-amber-400 focus:outline-none"
                                            >
                                        </td>

                                        <td class="py-2.5">
                                            <input
                                                v-model.number="it.unit_price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                class="w-24 px-2 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-mono font-bold text-white focus:outline-none"
                                            >
                                        </td>

                                        <td class="py-2.5 font-mono font-black text-emerald-400">
                                            {{ formatMoney((it.quantity || 0) * (it.unit_price || 0)) }} {{ $t('common.currency') }}
                                        </td>

                                        <td class="py-2.5 text-center">
                                            <button
                                                @click="removeItemRow(itIdx)"
                                                type="button"
                                                class="w-7 h-7 rounded-xl bg-rose-500/15 hover:bg-rose-500/30 text-rose-400 flex items-center justify-center transition cursor-pointer"
                                            >
                                                ✕
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div v-if="form.items.length === 0" class="py-8 text-center text-slate-500 text-xs font-bold font-tajawal">
                                {{ $t('returns.empty_return_items') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Col: Financial Summary & Confirmation -->
                <div class="space-y-5">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl space-y-4 sticky top-20">
                        <h2 class="text-base font-black text-white border-b border-slate-800 pb-3">{{ $t('returns.summary_title') }}</h2>

                        <div class="space-y-3 font-mono">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-200 font-bold font-tajawal">{{ $t('returns.total_returns_val') }}:</span>
                                <span class="text-xl font-black text-amber-400">{{ formatMoney(netTotal) }} {{ $t('common.currency') }}</span>
                            </div>

                            <div class="space-y-1 pt-2 border-t border-slate-800">
                                <label class="text-xs font-bold text-slate-300 font-tajawal">{{ $t('returns.refund_amount_cash') }}</label>
                                <input
                                    v-model.number="form.refund_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-emerald-400 font-mono font-black focus:outline-none"
                                >
                                <p class="text-[10px] text-slate-500 font-tajawal mt-0.5">
                                    {{ $t('returns.refund_hint') }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing || form.items.length === 0"
                            class="w-full h-12 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            <span>🔄</span>
                            <span>{{ form.processing ? '...' : $t('returns.confirm_return_save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>