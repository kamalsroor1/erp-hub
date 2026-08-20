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
        customWeightInput.value = '';
        emit('close');
    }
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-zoom">
            <div
                v-if="show && item"
                @click="emit('close')"
                class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 font-tajawal select-none"
            >
                <div @click.stop class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
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

                    <!-- Preset Weights Grid (Buttons) -->
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-for="w in presetWeights"
                            :key="w.qty"
                            @click="selectPreset(w.qty)"
                            type="button"
                            class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700/80 transition text-right group cursor-pointer active:scale-95 shadow-xs"
                        >
                            <span class="block text-xs sm:text-sm font-black text-slate-900 dark:text-white group-hover:text-theme-primary transition">{{ w.label }}</span>
                            <span class="block text-[11px] text-slate-400 font-mono mt-0.5">
                                {{ formatMoney(effectiveKiloPrice(item) * w.qty) }} {{ $t('common.currency') }}
                            </span>
                        </button>
                    </div>

                    <!-- Custom Weight Input -->
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-800 space-y-2">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400">
                            {{ $t('pos.custom_weight') }} ({{ $t('pos.grams_or_kilos') }})
                        </label>
                        <div class="flex gap-2">
                            <input
                                v-model="customWeightInput"
                                type="number"
                                step="any"
                                min="0"
                                inputmode="decimal"
                                :placeholder="$t('pos.enter_custom_weight')"
                                @keyup.enter="applyCustomWeight"
                                class="flex-1 h-11 px-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-mono font-bold focus:ring-2 focus:ring-amber-500 outline-hidden transition shadow-xs"
                            />
                            <button
                                @click="applyCustomWeight"
                                type="button"
                                class="h-11 px-4 rounded-2xl btn-primary-theme font-black text-xs transition active:scale-95 cursor-pointer shrink-0 shadow-theme-primary"
                            >
                                {{ $t('common.confirm') }}
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-400">
                            {{ $t('pos.weight_hint') }}
                        </p>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
