<script setup>
import { ref, computed } from 'vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    show: { type: Boolean, default: false },
    customers: { type: Array, default: () => [] },
    selectedCustomerId: { type: [Number, String], default: null },
});

const emit = defineEmits(['close', 'select', 'create-new']);

const { formatMoney } = useMoney();
const customerSearch = ref('');

const filteredCustomers = computed(() => {
    const q = customerSearch.value.trim().toLowerCase();
    if (!q) return props.customers;
    return props.customers.filter(c =>
        c.name.toLowerCase().includes(q) ||
        c.phone?.includes(q)
    );
});
</script>

<template>
    <div
        v-if="show"
        @click="emit('close')"
        class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal select-none"
    >
        <div @click.stop class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-3.5">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="font-black text-sm sm:text-base text-slate-900 dark:text-white">{{ $t('pos.choose_invoice_customer') }}</h3>
                <button
                    @click="emit('close')"
                    type="button"
                    class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center justify-center text-sm font-bold transition active:scale-90 cursor-pointer shadow-xs shrink-0"
                >
                    ✕
                </button>
            </div>

            <div class="relative">
                <input
                    v-model="customerSearch"
                    type="text"
                    :placeholder="$t('pos.search_customer_placeholder')"
                    class="w-full h-11 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-amber-500 shadow-inner"
                />
            </div>

            <div class="max-h-72 overflow-y-auto space-y-2 pr-0.5">
                <div
                    v-for="c in filteredCustomers"
                    :key="c.id"
                    @click="emit('select', c)"
                    class="p-3 rounded-2xl border flex items-center justify-between cursor-pointer transition active:scale-98 shadow-xs min-h-[50px]"
                    :class="selectedCustomerId === c.id ? 'bg-amber-500/15 border-amber-500 text-slate-900 dark:text-white font-black ring-2 ring-amber-500/30' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                >
                    <div class="space-y-0.5">
                        <div class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">{{ c.name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono" dir="ltr">{{ c.phone || '—' }}</div>
                    </div>
                    <span
                        class="text-xs font-mono font-black px-2 py-0.5 rounded-lg"
                        :class="c.current_balance > 0 ? 'text-rose-600 dark:text-rose-400 bg-rose-500/10' : 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10'"
                    >
                        {{ formatMoney(c.current_balance) }} {{ $t('common.currency') }}
                    </span>
                </div>

                <div v-if="filteredCustomers.length === 0" class="py-8 text-center text-xs text-slate-400 font-bold">
                    {{ $t('contacts.no_customers_found') }}
                </div>
            </div>

            <!-- Quick Add Customer Option -->
            <button
                @click="emit('create-new'); emit('close')"
                type="button"
                class="w-full h-11 rounded-2xl bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-600 dark:text-amber-400 font-black text-xs flex items-center justify-center gap-2 transition active:scale-95 cursor-pointer shadow-xs"
            >
                <span>➕</span>
                <span>{{ $t('pos.add_new_customer') }}</span>
            </button>
        </div>
    </div>
</template>
