<script setup>
import { ref } from 'vue';
import { useMoney } from '@/Composables/useMoney';

const props = defineProps({
    show: { type: Boolean, default: false },
    item: { type: Object, default: null },
    customerPriceTier: { type: String, default: 'retail' },
    presets: {
        type: Array,
        default: () => [
            { label: 'ثمن كيلو (125 جم)', val: 0.125 },
            { label: 'ربع كيلو (250 جم)', val: 0.250 },
            { label: 'نصف كيلو (500 جم)', val: 0.500 },
            { label: 'كيلو كامل (1000 جم)', val: 1.000 },
        ]
    }
});

const emit = defineEmits(['close', 'confirm']);

const { formatMoney } = useMoney();
const selectedWeight = ref(0.250);
const customWeightInput = ref('');

const effectiveKiloPrice = (item) => {
    if (!item) return 0;
    return props.customerPriceTier === 'wholesale' ? item.price_wholesale : item.price_retail;
};

const handleConfirm = () => {
    const finalQty = customWeightInput.value ? Number(customWeightInput.value) : selectedWeight.value;
    if (finalQty > 0) {
        emit('confirm', { item: props.item, quantity: finalQty });
    }
    customWeightInput.value = '';
};
</script>

<template>
    <div
        v-if="show && item"
        @click="emit('close')"
        class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div @click.stop class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="font-black text-sm text-white">{{ item.name }}</h3>
                    <p class="text-[10px] text-emerald-400 font-mono">
                        سعر الكيلو: {{ formatMoney(effectiveKiloPrice(item)) }} {{ $t('common.currency') }}
                    </p>
                </div>
                <button @click="emit('close')" class="w-7 h-7 rounded-xl bg-slate-800 text-slate-400 text-xs">✕</button>
            </div>

            <!-- Weight Chips (OCP - extensible via presets prop) -->
            <div class="grid grid-cols-2 gap-2">
                <button
                    v-for="w in presets"
                    :key="w.val"
                    @click="selectedWeight = w.val; customWeightInput = ''"
                    type="button"
                    class="p-3 rounded-2xl border text-center transition"
                    :class="selectedWeight === w.val && !customWeightInput ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400 font-black' : 'bg-slate-800/60 border-slate-800 text-slate-300 hover:bg-slate-800'"
                >
                    <div class="text-xs font-bold">{{ w.label }}</div>
                    <div class="text-[10px] font-mono text-slate-400 mt-1">
                        {{ formatMoney(effectiveKiloPrice(item) * w.val) }} {{ $t('common.currency') }}
                    </div>
                </button>
            </div>

            <!-- Custom Weight Input -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 mb-1">وزن مخصص (كجم):</label>
                <input
                    v-model="customWeightInput"
                    type="number"
                    step="0.001"
                    placeholder="مثال: 0.750"
                    class="w-full h-10 bg-slate-800 border border-slate-700 rounded-xl px-3 text-center text-xs font-mono font-bold text-white focus:outline-none focus:border-emerald-500"
                />
            </div>

            <button
                @click="handleConfirm"
                type="button"
                class="w-full h-11 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs transition"
            >
                تأكيد وإضافة للسلة
            </button>
        </div>
    </div>
</template>
