<script setup>
import { ref, computed } from 'vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    show: { type: Boolean, default: false },
    customers: { type: Array, default: () => [] },
    selectedCustomerId: { type: [Number, String], default: null },
});

const emit = defineEmits(['close', 'select', 'create-new', 'openQuickAdd']);

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
    <Teleport to="body">
        <Transition name="modal-zoom">
            <div
                v-if="show"
                @click="emit('close')"
                class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 font-tajawal select-none"
            >
                <div @click.stop class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-3.5 max-h-[85vh] flex flex-col">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 shrink-0">
                        <h3 class="font-black text-sm sm:text-base text-slate-900 dark:text-white">{{ $t('pos.choose_invoice_customer') }}</h3>
                        <button
                            @click="emit('close')"
                            type="button"
                            class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center justify-center text-sm font-bold transition active:scale-90 cursor-pointer shadow-xs shrink-0"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="relative shrink-0">
                        <input
                            v-model="customerSearch"
                            type="text"
                            :placeholder="$t('pos.search_customer_placeholder')"
                            class="w-full h-11 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-amber-500 shadow-inner"
                        />
                    </div>

                    <div class="flex-1 overflow-y-auto space-y-2 pr-0.5 min-h-[120px]">
                        <div
                            v-for="c in filteredCustomers"
                            :key="c.id"
                            @click="emit('select', c)"
                            class="p-3 rounded-2xl border flex items-center justify-between cursor-pointer transition active:scale-98 shadow-xs min-h-[50px]"
                            :class="selectedCustomerId === c.id ? 'bg-amber-500/15 border-amber-500 text-slate-900 dark:text-white font-black ring-2 ring-amber-500/30' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        >
                            <div>
                                <p class="text-xs sm:text-sm font-bold">{{ c.name }}</p>
                                <p v-if="c.phone" class="text-[11px] text-slate-400 font-mono mt-0.5">{{ c.phone }}</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg" :class="c.price_tier === 'wholesale' ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                {{ c.price_tier === 'wholesale' ? $t('customers.wholesale') : $t('customers.retail') }}
                            </span>
                        </div>

                        <div v-if="filteredCustomers.length === 0" class="py-8 text-center text-xs text-slate-400 font-bold">
                            {{ $t('pos.no_matching_customers') }}
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200 dark:border-slate-800 shrink-0">
                        <button
                            @click="emit('openQuickAdd')"
                            type="button"
                            class="w-full h-11 rounded-2xl border-2 border-dashed border-amber-500/40 hover:border-amber-500 hover:bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center gap-2 transition active:scale-95 cursor-pointer"
                        >
                            <span>➕</span>
                            <span>{{ $t('pos.add_new_customer_quick') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
