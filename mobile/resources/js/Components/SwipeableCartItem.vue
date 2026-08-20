<script setup>
import { ref } from 'vue';
import { haptic } from '@/Utils/haptics';

const props = defineProps({
    item: Object,
    index: Number,
});

const emit = defineEmits(['update-qty', 'remove']);

const touchStartX = ref(0);
const touchStartY = ref(0);
const swipeOffset = ref(0);
const isSwiping = ref(false);
const isRevealed = ref(false);

const handleTouchStart = (e) => {
    touchStartX.value = e.touches[0].clientX;
    touchStartY.value = e.touches[0].clientY;
    isSwiping.value = true;
};

const handleTouchMove = (e) => {
    if (!isSwiping.value) return;
    const diffX = e.touches[0].clientX - touchStartX.value;
    const diffY = e.touches[0].clientY - touchStartY.value;

    // If vertical scroll is stronger, don't intercept
    if (Math.abs(diffY) > Math.abs(diffX) && Math.abs(swipeOffset.value) < 10) {
        return;
    }

    // Dragging to the left (negative diffX)
    if (diffX < 0) {
        swipeOffset.value = Math.max(diffX, -100);
    } else if (isRevealed.value && diffX > 0) {
        swipeOffset.value = Math.min(0, -90 + diffX);
    }
};

const handleTouchEnd = () => {
    isSwiping.value = false;
    if (swipeOffset.value < -40) {
        swipeOffset.value = -90;
        if (!isRevealed.value) {
            haptic.heavy();
            isRevealed.value = true;
        }
    } else {
        swipeOffset.value = 0;
        isRevealed.value = false;
    }
};

const closeSwipe = () => {
    swipeOffset.value = 0;
    isRevealed.value = false;
};

const triggerRemove = () => {
    haptic.heavy();
    emit('remove', props.index);
};
</script>

<template>
    <div class="relative overflow-hidden rounded-2xl bg-rose-600 select-none shadow-xs">
        <!-- Background Action Revealed on Swipe (Positioned on the right for RTL drag-left) -->
        <div
            @click="triggerRemove"
            class="absolute inset-y-0 right-0 w-24 flex flex-col items-center justify-center text-white font-bold cursor-pointer touch-active px-2"
        >
            <span class="text-lg animate-bounce">🗑️</span>
            <span class="text-[11px] font-black mt-0.5 leading-tight">حذف الصنف</span>
        </div>

        <!-- Foreground Card Container -->
        <div
            @touchstart="handleTouchStart"
            @touchmove="handleTouchMove"
            @touchend="handleTouchEnd"
            @click="isRevealed ? closeSwipe() : null"
            class="relative bg-white dark:bg-slate-900 p-3 rounded-2xl border transition-transform duration-150 ease-out"
            :class="isRevealed ? 'border-rose-500 shadow-md' : 'border-slate-200 dark:border-slate-800'"
            :style="{ transform: `translateX(${swipeOffset}px)` }"
        >
            <div class="flex items-center justify-between gap-2">
                <!-- Item Info -->
                <div class="flex items-center gap-2.5 truncate">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-500 flex items-center justify-center text-xs font-black shrink-0">
                        ☕
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-extrabold text-slate-900 dark:text-white truncate">
                            {{ item.name }}
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono">
                            {{ Number(item.unit_price).toFixed(2) }} ج.م / {{ item.unit || 'كجم' }}
                        </div>
                    </div>
                </div>

                <!-- Quantity Controls & Price -->
                <div class="flex items-center gap-3 shrink-0">
                    <div class="flex items-center bg-slate-100 dark:bg-slate-800 rounded-xl p-0.5 border border-slate-200 dark:border-slate-700">
                        <button
                            @click.stop="haptic.light(); emit('update-qty', { index, delta: -0.250 })"
                            type="button"
                            class="w-6 h-6 rounded-lg bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center justify-center touch-active shadow-xs"
                        >
                            -
                        </button>
                        <span class="px-2 text-xs font-black font-mono text-slate-900 dark:text-white min-w-[32px] text-center">
                            {{ item.quantity }}
                        </span>
                        <button
                            @click.stop="haptic.light(); emit('update-qty', { index, delta: 0.250 })"
                            type="button"
                            class="w-6 h-6 rounded-lg bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs flex items-center justify-center touch-active shadow-xs"
                        >
                            +
                        </button>
                    </div>

                    <div class="text-end min-w-[60px]">
                        <div class="text-xs font-black font-mono text-emerald-600 dark:text-emerald-400">
                            {{ (item.quantity * item.unit_price).toFixed(2) }}
                        </div>
                        <div class="text-[9px] text-slate-400 font-bold">ج.م</div>
                    </div>
                </div>
            </div>

            <!-- Hint indicator on first item -->
            <div v-if="index === 0 && !isRevealed" class="text-[9px] text-slate-400 dark:text-slate-500 text-end mt-1 font-mono">
                👈 اسحب لليسار لحذف الصنف
            </div>
            <div v-else-if="isRevealed" class="text-[9px] text-rose-500 text-end mt-1 font-bold">
                ⚠️ اضغط على الأيقونة الحمراء لحذف الصنف نهائياً
            </div>
        </div>
    </div>
</template>
