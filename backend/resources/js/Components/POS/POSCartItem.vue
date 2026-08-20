<script setup>
import { computed } from 'vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    line: { type: Object, required: true },
    index: { type: Number, required: true },
});

const emit = defineEmits(['remove', 'apply-last-price', 'change']);

const { formatMoney, formatQty } = useMoney();

const isWeightBased = computed(() => {
    return props.line.unit === 'كجم' || props.line.unit === 'جم' || props.line.unit?.includes('كيلو');
});

const setExactWeight = (w) => {
    props.line.quantity = w;
    emit('change');
};

const decreaseQty = () => {
    const step = isWeightBased.value ? 0.250 : 1;
    const min = isWeightBased.value ? 0.125 : 1;
    if (props.line.quantity > min) {
        props.line.quantity = Number((props.line.quantity - step).toFixed(3));
        emit('change');
    } else {
        emit('remove', props.index);
    }
};

const increaseQty = () => {
    const step = isWeightBased.value ? 0.250 : 1;
    props.line.quantity = Number((props.line.quantity + step).toFixed(3));
    emit('change');
};
</script>

<template>
    <div class="p-2.5 rounded-2xl bg-white dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-2 group hover:border-slate-300 dark:hover:border-slate-700 transition shadow-xs">
        <div class="flex items-center justify-between gap-2">
            <div class="flex-1 truncate">
                <div class="font-black text-xs text-slate-900 dark:text-white truncate">{{ line.name }}</div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono flex items-center gap-1.5 mt-0.5">
                    <span>{{ $t('invoices.unit_price') }}:</span>
                    <input
                        v-model.number="line.unit_price"
                        @input="emit('change')"
                        type="number"
                        min="0"
                        class="w-16 h-5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-1 text-center font-mono font-bold text-slate-900 dark:text-white text-[11px] focus:outline-none focus:border-amber-500"
                    />
                    <span>{{ $t('common.currency') }}</span>
                </div>
            </div>

            <!-- Qty Steppers -->
            <div class="flex items-center gap-1 shrink-0">
                <button
                    @click="decreaseQty"
                    type="button"
                    class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-white font-bold text-xs flex items-center justify-center transition cursor-pointer border border-slate-200 dark:border-transparent"
                >
                    -
                </button>

                <input
                    v-model.number="line.quantity"
                    @input="emit('change')"
                    type="number"
                    step="0.001"
                    class="w-14 h-6 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-center text-xs font-mono font-black text-amber-600 dark:text-amber-400 focus:outline-none focus:border-amber-500"
                />

                <button
                    @click="increaseQty"
                    type="button"
                    class="w-6 h-6 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-white font-bold text-xs flex items-center justify-center transition cursor-pointer border border-slate-200 dark:border-transparent"
                >
                    +
                </button>
            </div>

            <!-- Line Total & Delete -->
            <div class="text-left shrink-0 pl-1">
                <div class="font-mono font-black text-xs text-emerald-600 dark:text-emerald-400">
                    {{ formatMoney(line.unit_price * line.quantity) }}
                </div>
                <button
                    @click="emit('remove', index)"
                    type="button"
                    class="text-slate-400 hover:text-rose-600 dark:text-slate-500 dark:hover:text-rose-400 text-xs mt-0.5 cursor-pointer"
                    :title="$t('common.delete')"
                >
                    ✕
                </button>
            </div>
        </div>

        <!-- Quick Weight Chips for Bulk / Coffee -->
        <div v-if="isWeightBased" class="flex items-center gap-1 pt-1 border-t border-slate-100 dark:border-slate-900 text-[10px]">
            <span class="text-slate-500 text-[9px] px-1">{{ $t('common.quantity') }}:</span>
            <button
                @click="setExactWeight(0.125)"
                type="button"
                class="px-2 py-0.5 rounded bg-slate-100 hover:bg-amber-500 hover:text-slate-950 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-mono font-bold transition"
            >
                1/8
            </button>
            <button
                @click="setExactWeight(0.250)"
                type="button"
                class="px-2 py-0.5 rounded bg-slate-100 hover:bg-amber-500 hover:text-slate-950 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-mono font-bold transition"
            >
                1/4
            </button>
            <button
                @click="setExactWeight(0.500)"
                type="button"
                class="px-2 py-0.5 rounded bg-slate-100 hover:bg-amber-500 hover:text-slate-950 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-mono font-bold transition"
            >
                1/2
            </button>
            <button
                @click="setExactWeight(1.000)"
                type="button"
                class="px-2 py-0.5 rounded bg-emerald-600/20 hover:bg-emerald-500 hover:text-slate-950 text-emerald-700 dark:text-emerald-300 font-mono font-bold transition"
            >
                1ك
            </button>
        </div>

        <!-- Last Customer Price Tag (if available) -->
        <div v-if="line.last_sold_price" class="flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-900 text-[10px]">
            <span class="text-amber-600 dark:text-amber-400 font-mono">
                🏷️ {{ $t('pos.last_customer_price') }}: {{ formatMoney(line.last_sold_price.unit_price) }} {{ $t('common.currency') }}
            </span>
            <button
                @click="emit('apply-last-price', line)"
                type="button"
                class="px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500/30 text-amber-700 dark:text-amber-300 font-black text-[9px] transition cursor-pointer"
            >
                {{ $t('pos.apply_btn') }}
            </button>
        </div>
    </div>
</template>