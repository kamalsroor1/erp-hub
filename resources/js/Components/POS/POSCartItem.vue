<script setup>
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    line: { type: Object, required: true },
    index: { type: Number, required: true },
});

const emit = defineEmits(['remove', 'apply-last-price', 'change']);

const { formatMoney, formatQty } = useMoney();

const step = props.line.unit === 'كجم' ? 0.250 : 1;

const decreaseQty = () => {
    if (props.line.quantity > (props.line.unit === 'كجم' ? 0.125 : 1)) {
        props.line.quantity = Number((props.line.quantity - step).toFixed(3));
        emit('change');
    } else {
        emit('remove', props.index);
    }
};

const increaseQty = () => {
    props.line.quantity = Number((props.line.quantity + step).toFixed(3));
    emit('change');
};
</script>

<template>
    <div class="p-2.5 rounded-2xl bg-slate-800/50 border border-slate-800 space-y-1.5">
        <div class="flex items-center justify-between gap-2">
            <div class="flex-1 truncate">
                <div class="font-black text-xs text-white truncate">{{ line.name }}</div>
                <div class="text-[10px] text-slate-400 font-mono flex items-center gap-2 mt-0.5">
                    <span>{{ formatMoney(line.unit_price) }} {{ $t('common.currency') }}</span>
                    <span>×</span>
                    <span class="text-white font-bold">{{ formatQty(line.quantity) }} {{ line.unit }}</span>
                </div>
            </div>

            <!-- Qty Controls -->
            <div class="flex items-center gap-1.5 shrink-0">
                <button
                    @click="decreaseQty"
                    type="button"
                    class="w-6 h-6 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs flex items-center justify-center"
                >
                    -
                </button>

                <input
                    v-model.number="line.quantity"
                    @input="emit('change')"
                    type="number"
                    step="0.001"
                    class="w-14 h-6 bg-slate-900 border border-slate-700 rounded-lg text-center text-xs font-mono font-bold text-white focus:outline-none focus:border-emerald-500"
                />

                <button
                    @click="increaseQty"
                    type="button"
                    class="w-6 h-6 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs flex items-center justify-center"
                >
                    +
                </button>
            </div>

            <!-- Line Total & Delete -->
            <div class="text-left shrink-0 pl-1">
                <div class="font-mono font-black text-xs text-emerald-400">
                    {{ formatMoney(line.unit_price * line.quantity) }}
                </div>
                <button
                    @click="emit('remove', index)"
                    type="button"
                    class="text-slate-500 hover:text-rose-400 text-[11px] mt-0.5"
                >
                    ✕
                </button>
            </div>
        </div>

        <!-- Last Customer Price Tag (if available) -->
        <div v-if="line.last_sold_price" class="flex items-center justify-between pt-1 border-t border-slate-700/40 text-[10px]">
            <span class="text-amber-400 font-mono">
                🏷️ آخر سعر للعميل: {{ formatMoney(line.last_sold_price.unit_price) }} {{ $t('common.currency') }}
            </span>
            <button
                @click="emit('apply-last-price', line)"
                type="button"
                class="px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 font-black text-[9px] transition"
            >
                تطبيق
            </button>
        </div>
    </div>
</template>
