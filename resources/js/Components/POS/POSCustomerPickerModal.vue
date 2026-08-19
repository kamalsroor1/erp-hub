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
        class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div @click.stop class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-2xl space-y-3">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <h3 class="font-black text-sm text-white">اختيار عميل الفاتورة</h3>
                <button @click="emit('close')" class="w-7 h-7 rounded-xl bg-slate-800 text-slate-400 text-xs">✕</button>
            </div>

            <input
                v-model="customerSearch"
                type="text"
                placeholder="ابحث بالاسم أو الهاتف..."
                class="w-full h-10 bg-slate-800 border border-slate-700 rounded-xl px-3 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500"
            />

            <div class="max-h-64 overflow-y-auto space-y-1.5">
                <div
                    v-for="c in filteredCustomers"
                    :key="c.id"
                    @click="emit('select', c)"
                    class="p-2.5 rounded-xl border flex items-center justify-between cursor-pointer transition"
                    :class="selectedCustomerId === c.id ? 'bg-indigo-600/20 border-indigo-500 text-white font-black' : 'bg-slate-800/40 border-slate-800 text-slate-300 hover:bg-slate-800'"
                >
                    <div>
                        <div class="text-xs font-bold">{{ c.name }}</div>
                        <div class="text-[10px] text-slate-400 font-mono">{{ c.phone || 'بدون هاتف' }}</div>
                    </div>
                    <span class="text-[10px] font-mono" :class="c.current_balance > 0 ? 'text-rose-400' : 'text-emerald-400'">
                        {{ formatMoney(c.current_balance) }} {{ $t('common.currency') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
