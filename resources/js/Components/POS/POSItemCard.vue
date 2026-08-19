<script setup>
import { computed } from 'vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    item: { type: Object, required: true },
    customerPriceTier: { type: String, default: 'retail' },
});

const emit = defineEmits(['select']);

const { formatMoney, formatQty } = useMoney();

const effectivePrice = computed(() => {
    return props.customerPriceTier === 'wholesale'
        ? props.item.price_wholesale
        : props.item.price_retail;
});

const stockBadgeClass = computed(() => {
    if (props.item.current_stock > props.item.min_stock_level) {
        return 'bg-emerald-500/10 text-emerald-400';
    }
    if (props.item.current_stock > 0) {
        return 'bg-amber-500/10 text-amber-400';
    }
    return 'bg-rose-500/10 text-rose-400';
});
</script>

<template>
    <div
        @click="emit('select', item)"
        class="bg-slate-900 hover:bg-slate-800/90 border border-slate-800/90 hover:border-emerald-500/50 rounded-2xl p-3 flex flex-col justify-between cursor-pointer transition transform active:scale-95 shadow-sm group"
    >
        <div>
            <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold mb-1">
                <span class="truncate">{{ item.category }}</span>
                <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold" :class="stockBadgeClass">
                    {{ formatQty(item.current_stock, 1) }} {{ item.unit }}
                </span>
            </div>
            <h3 class="font-black text-xs text-white line-clamp-2 leading-tight group-hover:text-emerald-300 transition">
                {{ item.name }}
            </h3>
        </div>

        <div class="mt-3 pt-2 border-t border-slate-800/80 flex items-center justify-between">
            <span class="text-xs font-black font-mono text-emerald-400">
                {{ formatMoney(effectivePrice) }} <span class="text-[9px]">{{ $t('common.currency') }}</span>
            </span>
            <span class="w-6 h-6 rounded-lg bg-emerald-500/10 group-hover:bg-emerald-500 text-emerald-400 group-hover:text-slate-950 font-bold text-xs flex items-center justify-center transition">
                +
            </span>
        </div>
    </div>
</template>
