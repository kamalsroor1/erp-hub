<script setup>
defineProps({
    lines: {
        type: Number,
        default: 3,
    },
    count: {
        type: Number,
        default: 3,
    }
});
</script>

<template>
    <div class="space-y-2.5">
        <div
            v-for="i in count"
            :key="i"
            class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3 select-none"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl skeleton-shimmer shrink-0"></div>
                    <div class="space-y-1.5">
                        <div class="w-28 h-3.5 rounded-md skeleton-shimmer"></div>
                        <div class="w-16 h-2.5 rounded-md skeleton-shimmer"></div>
                    </div>
                </div>
                <div class="w-16 h-5 rounded-lg skeleton-shimmer"></div>
            </div>

            <div v-if="lines > 1" class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="w-20 h-3 rounded-md skeleton-shimmer"></div>
                <div class="w-14 h-3.5 rounded-md skeleton-shimmer"></div>
            </div>
        </div>
    </div>
</template>
