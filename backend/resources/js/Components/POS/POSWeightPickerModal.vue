<script setup>
import { ref, computed } from 'vue';
import { useMoney } from '@/Composables/useMoney';
import { trans } from '@/helpers/trans';

const props = defineProps({
    show: { type: Boolean, default: false },
    item: { type: Object, default: null },
    customerPriceTier: { type: String, default: 'retail' },
});

const emit = defineEmits(['close', 'confirm']);

const presets = computed(() => [
    { label: trans('inventory.weight_eighth') || 'ثمن كيلو (125 جم)', val: 0.125 },
    { label: trans('inventory.weight_quarter') || 'ربع كيلو (250 جم)', val: 0.250 },
    { label: trans('inventory.weight_half') || 'نصف كيلو (500 جم)', val: 0.500 },
    { label: trans('inventory.weight_kilo') || 'كيلو كامل (1000 جم)', val: 1.000 },
]);

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
        class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal select-none"
    >
        <div @click.stop class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="space-y-0.5">
                    <h3 class="font-black text-sm sm:text-base text-slate-900 dark:text-white leading-tight">{{ item.name }}</h3>
                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono font-bold">
                        {{ $t('pos.kilo_price') }}: {{ formatMoney(effectiveKiloPrice(item)) }} {{ $t('common.currency') }}
                    </p>
                </div>
                <button
                    @click="emit('close')"
                    type="button"
                    class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center justify-center text-sm font-bold transition active:scale-90 cursor-pointer shadow-xs shrink-0"
                >
                    ✕
                </button>
            </div>

            <!-- Weight Chips (OCP - extensible via presets prop) -->
            <div class="grid grid-cols-2 gap-2.5">
                <button
                    v-for="w in presets"
                    :key="w.val"
                    @click="selectedWeight = w.val; customWeightInput = ''"
                    type="button"
                    class="p-3.5 rounded-2xl border text-center transition active:scale-95 cursor-pointer shadow-xs"
                    :class="selectedWeight === w.val && !customWeightInput ? 'bg-emerald-500/15 border-emerald-500 text-emerald-600 dark:text-emerald-400 font-black ring-2 ring-emerald-500/30' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                >
                    <div class="text-xs sm:text-sm font-bold">{{ w.label }}</div>
                    <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-1 font-bold">
                        {{ formatMoney(effectiveKiloPrice(item) * w.val) }} {{ $t('common.currency') }}
                    </div>
                </button>
            </div>

            <!-- Custom Weight Input -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('pos.custom_weight') }}:</label>
                <input
                    v-model="customWeightInput"
                    type="number"
                    step="0.001"
                    placeholder="0.750"
                    class="w-full h-11 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 text-center text-sm font-mono font-black text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 shadow-inner"
                />
            </div>

            <button
                @click="handleConfirm"
                type="button"
                class="w-full h-12 rounded-2xl btn-primary-theme font-black text-sm transition transform active:scale-95 cursor-pointer shadow-theme-primary flex items-center justify-center gap-2"
            >
                <span>⚡</span>
                <span>{{ $t('pos.confirm_add_cart') }}</span>
            </button>
        </div>
    </div>
</template>
