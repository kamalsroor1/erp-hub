<script setup>
defineProps({
    categories: {
        type: Array,
        default: () => []
    },
    selectedCategory: {
        type: String,
        default: 'all'
    },
    totalItemsCount: {
        type: Number,
        default: 0
    }
});

defineEmits(['select-category']);
</script>

<template>
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-tajawal">
        <button
            type="button"
            class="h-9 px-3.5 rounded-xl font-black transition shrink-0 cursor-pointer flex items-center justify-center"
            :class="selectedCategory === 'all' ? 'tab-theme-active' : 'bg-slate-100 text-slate-600 hover:text-slate-900 dark:bg-slate-950 dark:text-slate-400 dark:hover:text-white border border-slate-200 dark:border-slate-800'"
            @click="$emit('select-category', 'all')"
        >
            {{ $t('common.all') }} ({{ totalItemsCount }})
        </button>

        <button
            v-for="cat in categories"
            :key="cat"
            type="button"
            class="h-9 px-3.5 rounded-xl font-black transition shrink-0 cursor-pointer flex items-center justify-center"
            :class="selectedCategory === cat ? 'tab-theme-active' : 'bg-slate-100 text-slate-600 hover:text-slate-900 dark:bg-slate-950 dark:text-slate-400 dark:hover:text-white border border-slate-200 dark:border-slate-800'"
            @click="$emit('select-category', cat)"
        >
            {{ cat }}
        </button>
    </div>
</template>
