<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    item: Object,
    initialQty: {
        type: Number,
        default: 0.250,
    },
});

const emit = defineEmits(['close', 'confirm']);

const selectedWeight = ref(0.250);
const customGrams = ref('');

// Sync when item opens
watch(() => props.item, (newItem) => {
    if (newItem) {
        // Default to 0.250 kg for kg items, or 1 for pieces
        if (isPieceItem.value) {
            selectedWeight.value = 1;
        } else {
            selectedWeight.value = props.initialQty > 0 ? props.initialQty : 0.250;
        }
        customGrams.value = '';
    }
}, { immediate: true });

const isPieceItem = computed(() => {
    const unit = props.item?.unit?.toLowerCase() || '';
    return unit.includes('قطعة') || unit.includes('قطعه') || unit.includes('عبوة') || unit.includes('كيس') || unit.includes('علبة');
});

// Weight Presets for Coffee (Kg)
const kgPresets = [
    { label: 'ثمن كيلو (125 جم)', weight: 0.125, badge: '1/8' },
    { label: 'ربع كيلو (250 جم)', weight: 0.250, badge: '1/4' },
    { label: 'نصف كيلو (500 جم)', weight: 0.500, badge: '1/2' },
    { label: 'كيلو كامل (1 كجم)', weight: 1.000, badge: '1.0' },
    { label: 'كيلو ونصف (1.5 كجم)', weight: 1.500, badge: '1.5' },
    { label: '2 كيلو (2 كجم)', weight: 2.000, badge: '2.0' },
];

// Presets for Pieces
const piecePresets = [
    { label: '1 قطعة', weight: 1, badge: '1' },
    { label: '2 قطعة', weight: 2, badge: '2' },
    { label: '3 قطعة', weight: 3, badge: '3' },
    { label: '5 قطع', weight: 5, badge: '5' },
    { label: '10 قطع', weight: 10, badge: '10' },
    { label: '12 دزينة', weight: 12, badge: '12' },
];

const setPreset = (w) => {
    selectedWeight.value = w;
    customGrams.value = '';
};

const onCustomGramsInput = () => {
    const grams = parseFloat(customGrams.value || 0);
    if (grams > 0) {
        selectedWeight.value = Number((grams / 1000).toFixed(3));
    }
};

const adjustQuantity = (delta) => {
    if (isPieceItem.value) {
        selectedWeight.value = Math.max(1, selectedWeight.value + delta);
    } else {
        selectedWeight.value = Math.max(0.050, Number((selectedWeight.value + (delta * 0.125)).toFixed(3)));
    }
};

const totalPrice = computed(() => {
    const pricePerUnit = parseFloat(props.item?.selling_price || 0);
    return (pricePerUnit * selectedWeight.value).toFixed(2);
});

const confirm = () => {
    emit('confirm', {
        item: props.item,
        quantity: selectedWeight.value,
        unit_price: parseFloat(props.item?.selling_price || 0),
        unit: props.item?.unit || (isPieceItem.value ? 'قطعة' : 'كجم'),
    });
    emit('close');
};
</script>

<template>
    <!-- Modal Backdrop -->
    <Transition
        enter-active-class="transition duration-250 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isOpen && item"
            @click="$emit('close')"
            class="fixed inset-0 z-[70] bg-slate-950/75 backdrop-blur-xs flex items-end justify-center select-none"
        >
            <!-- Bottom Sheet Container -->
            <div
                @click.stop
                class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl border-t border-slate-200 dark:border-slate-800 shadow-2xl p-4 pb-8 space-y-4 max-h-[90vh] overflow-y-auto animate-slide-up"
            >
                <!-- Drag Handle -->
                <div class="w-12 h-1 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto -mt-1 mb-1"></div>

                <!-- Item Header -->
                <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/15 text-amber-500 flex items-center justify-center text-xl shrink-0">
                            {{ isPieceItem ? '📦' : '⚖️' }}
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white leading-tight">
                                {{ item.name }}
                            </h3>
                            <div class="text-[11px] text-slate-400 font-mono font-bold mt-0.5">
                                السعر: <span class="text-emerald-500 font-black">{{ Number(item.selling_price).toFixed(2) }} ج.م</span> / {{ item.unit || (isPieceItem ? 'قطعة' : 'كجم') }}
                            </div>
                        </div>
                    </div>

                    <button
                        @click="$emit('close')"
                        type="button"
                        class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center text-xs font-bold"
                    >
                        ✕
                    </button>
                </div>

                <!-- Weight Presets Grid (For Coffee/Kg items) -->
                <div v-if="!isPieceItem" class="space-y-2">
                    <div class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400 flex items-center justify-between">
                        <span>أوزان سريعة شائعة:</span>
                        <span class="text-[10px] text-amber-500 font-mono font-bold">بالميزان</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="p in kgPresets"
                            :key="p.weight"
                            @click="setPreset(p.weight)"
                            type="button"
                            class="p-2.5 rounded-2xl border text-center transition touch-active flex flex-col items-center justify-center gap-0.5"
                            :class="selectedWeight === p.weight ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-600/20' : 'bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200'"
                        >
                            <span class="text-xs font-black font-mono">{{ p.badge }} كجم</span>
                            <span class="text-[10px] opacity-80 font-bold leading-tight">{{ p.label.split(' ')[0] }} {{ p.label.split(' ')[1] }}</span>
                        </button>
                    </div>

                    <!-- Custom Weight Grams Input -->
                    <div class="pt-2">
                        <label class="block text-[11px] font-bold text-slate-400 mb-1">أو إدخال وزن حر بالميزان (بالجرام):</label>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <input
                                    v-model="customGrams"
                                    @input="onCustomGramsInput"
                                    type="number"
                                    placeholder="مثال: 350 (جرام)"
                                    class="w-full h-11 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 text-xs font-mono font-bold text-slate-900 dark:text-white outline-none focus:border-emerald-500"
                                >
                                <span class="absolute inset-y-0 end-3 flex items-center text-xs font-bold text-slate-400">
                                    جرام
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Piece Presets Grid (For Piece/Unit items) -->
                <div v-else class="space-y-2">
                    <div class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">
                        الكمية المطلوبة (بالقطعة):
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="p in piecePresets"
                            :key="p.weight"
                            @click="setPreset(p.weight)"
                            type="button"
                            class="p-2.5 rounded-2xl border text-center transition touch-active"
                            :class="selectedWeight === p.weight ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-600/20' : 'bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200'"
                        >
                            <span class="text-xs font-black">{{ p.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- Active Weight & Stepper Adjustment -->
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/90 rounded-2xl border border-slate-200/80 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-slate-400 font-bold">الوزن / الكمية المحددة</div>
                        <div class="text-base font-black text-slate-900 dark:text-white font-mono">
                            {{ selectedWeight }} {{ item.unit || (isPieceItem ? 'قطعة' : 'كجم') }}
                        </div>
                    </div>

                    <!-- Stepper -->
                    <div class="flex items-center gap-2">
                        <button
                            @click="adjustQuantity(-1)"
                            type="button"
                            class="w-9 h-9 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-white flex items-center justify-center text-lg font-black touch-active shadow-xs"
                        >
                            -
                        </button>
                        <span class="w-12 text-center font-mono font-black text-sm text-slate-900 dark:text-white">
                            {{ selectedWeight }}
                        </span>
                        <button
                            @click="adjustQuantity(1)"
                            type="button"
                            class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg font-black touch-active shadow-xs"
                        >
                            +
                        </button>
                    </div>
                </div>

                <!-- Calculated Total & Confirm CTA -->
                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between px-1">
                        <span class="text-xs font-bold text-slate-500">إجمالي هذا الصنف:</span>
                        <span class="text-lg font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ totalPrice }} <span class="text-xs font-bold text-slate-400">ج.م</span>
                        </span>
                    </div>

                    <button
                        @click="confirm"
                        type="button"
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-2xl shadow-lg shadow-emerald-600/30 transition touch-active flex items-center justify-center gap-1.5"
                    >
                        <span>إضافة للفاتورة ({{ selectedWeight }} {{ item.unit || (isPieceItem ? 'قطعة' : 'كجم') }}) ✓</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
