<script setup>
import { computed } from 'vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    item: { type: Object, required: true },
    customerPriceTier: { type: String, default: 'retail' },
});

const emit = defineEmits(['select', 'add-qty']);

const { formatMoney, formatQty } = useMoney();

const effectivePrice = computed(() => {
    return props.customerPriceTier === 'wholesale'
        ? props.item.price_wholesale
        : props.item.price_retail;
});

const isWeightBased = computed(() => {
    return props.item.unit === 'كجم' || props.item.unit === 'جم' || props.item.unit?.includes('كيلو');
});

const stockBadgeClass = computed(() => {
    if (props.item.current_stock > props.item.min_stock_level) {
        return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
    }
    if (props.item.current_stock > 0) {
        return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
    }
    return 'bg-rose-500/10 text-rose-400 border border-rose-500/20';
});
</script>

<template>
    <div
        class="bg-slate-900 hover:bg-slate-800/90 border border-slate-800 hover:border-amber-500/40 rounded-2xl flex flex-col justify-between transition shadow-sm group overflow-hidden"
    >
        <!-- Main Card Area (Tap for Custom Modal / Add 1) -->
        <div
            @click="emit('select', item)"
            class="p-3 cursor-pointer select-none flex-1 flex flex-col justify-between active:bg-slate-800/60"
        >
            <div>
                <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold mb-1">
                    <span class="truncate">{{ item.category || $t('common.all') }}</span>
                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold" :class="stockBadgeClass">
                        {{ formatQty(item.current_stock, 1) }} {{ item.unit }}
                    </span>
                </div>
                <h3 class="font-black text-xs text-white line-clamp-2 leading-tight group-hover:text-amber-300 transition">
                    {{ item.name }}
                </h3>
            </div>

            <div class="mt-2.5 pt-2 border-t border-slate-800/80 flex items-center justify-between">
                <span class="text-xs font-black font-mono text-emerald-400">
                    {{ formatMoney(effectivePrice) }} <span class="text-[9px] text-slate-400">{{ $t('common.currency') }}</span>
                </span>
                <span class="text-[10px] text-slate-400 font-mono">
                    {{ item.code }}
                </span>
            </div>
        </div>

        <!-- Direct Quick Weight Steppers Bar (For Coffee & Bulk items) -->
        <div v-if="isWeightBased" class="p-1.5 bg-slate-950/80 border-t border-slate-800/80 grid grid-cols-4 gap-1">
            <button
                @click.stop="emit('add-qty', { item, quantity: 0.125 })"
                type="button"
                class="h-7 rounded-lg bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-bold text-[10px] font-mono transition active:scale-90 flex items-center justify-center cursor-pointer"
                :title="$t('inventory.weight_eighth')"
            >
                1/8
            </button>
            <button
                @click.stop="emit('add-qty', { item, quantity: 0.250 })"
                type="button"
                class="h-7 rounded-lg bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-bold text-[10px] font-mono transition active:scale-90 flex items-center justify-center cursor-pointer"
                :title="$t('inventory.weight_quarter')"
            >
                1/4
            </button>
            <button
                @click.stop="emit('add-qty', { item, quantity: 0.500 })"
                type="button"
                class="h-7 rounded-lg bg-slate-800 hover:bg-amber-500 hover:text-slate-950 text-slate-300 font-bold text-[10px] font-mono transition active:scale-90 flex items-center justify-center cursor-pointer"
                :title="$t('inventory.weight_half')"
            >
                1/2
            </button>
            <button
                @click.stop="emit('add-qty', { item, quantity: 1.000 })"
                type="button"
                class="h-7 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-black text-[10px] font-mono transition active:scale-90 flex items-center justify-center cursor-pointer"
                :title="$t('inventory.weight_kilo')"
            >
                1ك
            </button>
        </div>
    </div>
</template>