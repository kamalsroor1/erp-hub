<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import flatpickr from 'flatpickr';
import { Arabic } from 'flatpickr/dist/l10n/ar.js';
import 'flatpickr/dist/flatpickr.min.css';

const props = defineProps({
    modelValue: { type: [String, Array, Date, null], default: null },
    mode: { type: String, default: 'single' }, // 'single' | 'range'
    placeholder: { type: String, default: 'اختر التاريخ...' },
    enableTime: { type: Boolean, default: false },
    clearable: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    icon: { type: String, default: '📅' },
});

const emit = defineEmits(['update:modelValue', 'change', 'clear']);

const inputRef = ref(null);
let fpInstance = null;

onMounted(() => {
    if (inputRef.value) {
        fpInstance = flatpickr(inputRef.value, {
            locale: Arabic,
            mode: props.mode,
            enableTime: props.enableTime,
            dateFormat: props.enableTime ? 'Y-m-d H:i' : 'Y-m-d',
            defaultDate: props.modelValue || undefined,
            disableMobile: true,
            onChange: (selectedDates, dateStr) => {
                emit('update:modelValue', dateStr);
                emit('change', dateStr, selectedDates);
            },
        });
    }
});

watch(() => props.modelValue, (newVal) => {
    if (fpInstance && newVal !== fpInstance.input.value) {
        fpInstance.setDate(newVal || '', false);
    }
});

onUnmounted(() => {
    if (fpInstance) {
        fpInstance.destroy();
    }
});

const clearDate = (e) => {
    e?.stopPropagation();
    if (fpInstance) {
        fpInstance.clear();
    }
    emit('update:modelValue', null);
    emit('clear');
    emit('change', null, []);
};
</script>

<template>
    <div class="relative w-full text-xs font-tajawal" :class="{ 'opacity-60 pointer-events-none': disabled }">
        <div class="relative flex items-center">
            <span v-if="icon" class="absolute right-3.5 text-sm text-slate-400 pointer-events-none z-10">
                {{ icon }}
            </span>

            <input
                ref="inputRef"
                type="text"
                :placeholder="placeholder"
                :disabled="disabled"
                readonly
                class="w-full pr-10 pl-8 py-2.5 rounded-2xl bg-slate-950/80 border border-slate-800 hover:border-slate-700 text-white placeholder:text-slate-500 font-mono text-xs cursor-pointer focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 focus:outline-none transition"
                :class="modelValue ? 'font-bold text-amber-400' : ''"
            >

            <!-- Clear Button -->
            <button
                v-if="clearable && modelValue && !disabled"
                @click="clearDate"
                type="button"
                class="absolute left-2.5 w-5 h-5 rounded-full hover:bg-slate-800 text-slate-400 hover:text-rose-400 flex items-center justify-center text-[10px] transition z-10"
                title="مسح التاريخ"
            >
                ✕
            </button>
        </div>
    </div>
</template>

<style>
/* Custom Dark Theme Flatpickr Styling */
.flatpickr-calendar {
    font-family: 'Cairo', 'Tajawal', sans-serif !important;
    border-radius: 1.25rem !important;
    box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.7), 0 0 0 1px rgba(255, 255, 255, 0.1) !important;
    direction: rtl !important;
    padding: 12px !important;
    width: 320px !important;
    background: #0f172a !important;
    border: 1px solid #334155 !important;
    color: #f8fafc !important;
}

.flatpickr-months {
    padding: 4px 0 !important;
}
.flatpickr-months .flatpickr-month {
    height: 40px !important;
}
.flatpickr-month,
.flatpickr-weekdays,
span.flatpickr-weekday {
    background: #0f172a !important;
    color: #94a3b8 !important;
    fill: #f8fafc !important;
    font-weight: bold !important;
}
.flatpickr-current-month {
    font-size: 110% !important;
    padding-top: 4px !important;
}
.flatpickr-current-month .cur-month {
    font-weight: 800 !important;
    margin: 0 4px !important;
}
.flatpickr-current-month input.cur-year {
    color: #f8fafc !important;
    font-weight: 800 !important;
}
.flatpickr-monthDropdown-months {
    background: #1e293b !important;
    color: #f8fafc !important;
    border-radius: 0.5rem !important;
    padding: 2px 6px !important;
    font-weight: bold !important;
}
.flatpickr-monthDropdown-months option {
    background-color: #0f172a !important;
    color: #f8fafc !important;
}
.flatpickr-prev-month, .flatpickr-next-month {
    padding: 6px !important;
    border-radius: 0.5rem !important;
    color: #f8fafc !important;
    fill: #f8fafc !important;
}
.flatpickr-prev-month:hover svg, .flatpickr-next-month:hover svg {
    fill: #f59e0b !important;
}
.flatpickr-day {
    color: #e2e8f0 !important;
    border-radius: 0.6rem !important;
    font-weight: 600 !important;
    height: 36px !important;
    line-height: 36px !important;
    margin: 2px 0 !important;
}
.flatpickr-day:hover,
.flatpickr-day:focus {
    background: #1e293b !important;
    border-color: #475569 !important;
}
.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange {
    background: #d97706 !important;
    border-color: #d97706 !important;
    color: #ffffff !important;
    font-weight: 900 !important;
    border-radius: 0.6rem !important;
}
.flatpickr-day.inRange {
    background: rgba(217, 119, 6, 0.2) !important;
    border-color: rgba(217, 119, 6, 0.3) !important;
    color: #fbbf24 !important;
}
.flatpickr-day.today {
    border-color: #f59e0b !important;
    font-weight: 800 !important;
}
.flatpickr-day.flatpickr-disabled,
.flatpickr-day.prevMonthDay,
.flatpickr-day.nextMonthDay {
    color: #475569 !important;
    opacity: 0.35 !important;
}
</style>
