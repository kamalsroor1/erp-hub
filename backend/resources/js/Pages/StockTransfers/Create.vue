<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { trans } from '@/helpers/trans';

const props = defineProps({
    stores: { type: Array, default: () => [] },
    items: { type: Array, default: () => [] },
});

const form = useForm({
    from_store_id: props.stores[0]?.id || null,
    to_store_id: props.stores[1]?.id || null,
    transfer_date: new Date().toISOString().split('T')[0],
    notes: '',
    items: [],
});

const selectedItemIdToAdd = ref(null);

const availableItemOptions = computed(() => {
    return props.items.map(item => ({
        id: item.id,
        name: `${item.name} (${item.code}) - ${trans('inventory.current_stock') || 'الرصيد'}: ${item.current_stock} ${item.unit || 'كجم'}`,
    }));
});

const addItemRow = () => {
    if (!selectedItemIdToAdd.value) return;
    const item = props.items.find(i => i.id === selectedItemIdToAdd.value);
    if (!item) return;

    if (form.items.some(it => it.item_id === item.id)) {
        alert(trans('inventory.item_already_added') || 'هذا الصنف مضاف بالفعل في إذن التحويل');
        return;
    }

    form.items.push({
        item_id: item.id,
        name: item.name,
        code: item.code,
        unit: item.unit || 'كجم',
        quantity: 10,
    });

    selectedItemIdToAdd.value = null;
};

const removeItemRow = (index) => {
    form.items.splice(index, 1);
};

const submitTransfer = () => {
    if (form.from_store_id === form.to_store_id) {
        alert(trans('inventory.same_store_transfer_error') || 'لا يمكن إجراء تحويل مخزني لنفس الفرع أو المخزن!');
        return;
    }
    if (form.items.length === 0) {
        alert(trans('inventory.add_at_least_one_item') || 'يرجى إضافة صنف واحد على الأقل لإجراء التحويل.');
        return;
    }

    form.post('/stock-transfers', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('inventory.new_transfer')" />

    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/stock-transfers" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 transition">
                            →
                        </Link>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>🚚 {{ $t('inventory.new_transfer') }}</span>
                            </h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-bold mt-0.5">
                                {{ $t('inventory.transfers_subtitle') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <form @submit.prevent="submitTransfer" class="space-y-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs space-y-5">
                    <h2 class="text-sm font-black text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-3 flex items-center gap-2">
                        <span>🏢</span>
                        <span>{{ $t('inventory.stores_title') }}</span>
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.from_store') }} *</label>
                            <select
                                v-model="form.from_store_id"
                                required
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-theme-primary focus:outline-none"
                            >
                                <option v-for="st in stores" :key="st.id" :value="st.id">
                                    {{ st.name }} ({{ st.type }})
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.to_store') }} *</label>
                            <select
                                v-model="form.to_store_id"
                                required
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-theme-primary focus:outline-none"
                            >
                                <option v-for="st in stores" :key="st.id" :value="st.id" :disabled="st.id === form.from_store_id">
                                    {{ st.name }} ({{ st.type }})
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('common.date') }} *</label>
                            <DatePicker v-model="form.transfer_date" :placeholder="$t('common.date')" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('common.notes') }}</label>
                        <input
                            v-model="form.notes"
                            type="text"
                            :placeholder="$t('common.notes')"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-theme-primary focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Items Picker Card -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-xs space-y-5">
                    <h2 class="text-sm font-black text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-3 flex items-center gap-2">
                        <span>📦</span>
                        <span>{{ $t('inventory.transfer_items') }}</span>
                    </h2>

                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <SearchableSelect
                                v-model="selectedItemIdToAdd"
                                :options="availableItemOptions"
                                :placeholder="$t('inventory.search_item_placeholder')"
                            />
                        </div>
                        <button
                            @click="addItemRow"
                            type="button"
                            class="h-11 px-5 rounded-2xl btn-primary-theme font-black text-xs transition cursor-pointer"
                        >
                            + {{ $t('inventory.add_new_item') }}
                        </button>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-right text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                    <th class="pb-3">{{ $t('inventory.item_name') }}</th>
                                    <th class="pb-3 font-mono">{{ $t('inventory.transferred_quantity') }}</th>
                                    <th class="pb-3">{{ $t('inventory.unit') }}</th>
                                    <th class="pb-3 text-center w-16">{{ $t('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                                <tr v-for="(it, idx) in form.items" :key="idx" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="py-3 font-bold text-slate-900 dark:text-white font-tajawal">
                                        {{ it.name }}
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono block">{{ it.code }}</span>
                                    </td>

                                    <td class="py-3 font-mono font-black">
                                        <input
                                            v-model.number="it.quantity"
                                            type="number"
                                            step="0.001"
                                            min="0.001"
                                            required
                                            class="w-28 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-black text-emerald-600 dark:text-emerald-400 focus:border-theme-primary focus:outline-none"
                                        >
                                    </td>

                                    <td class="py-3 text-slate-700 dark:text-slate-300 font-tajawal">
                                        {{ it.unit || 'كجم' }}
                                    </td>

                                    <td class="py-3 text-center">
                                        <button
                                            @click="removeItemRow(idx)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 dark:text-rose-400 transition cursor-pointer"
                                        >
                                            ✕
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="form.items.length === 0" class="py-12 text-center space-y-2 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                            <span class="text-2xl">📦</span>
                            <p class="text-xs font-bold text-slate-400 font-tajawal">{{ $t('inventory.no_items_found') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3">
                    <Link
                        href="/stock-transfers"
                        class="px-5 py-3 rounded-2xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        {{ $t('common.cancel') }}
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing || form.items.length === 0"
                        class="h-12 px-8 rounded-2xl btn-primary-theme font-bold text-xs flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <span>🚚</span>
                        <span>{{ form.processing ? '...' : $t('inventory.new_transfer') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
