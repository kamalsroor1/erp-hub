<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'تصفية وفلاتر متقدمة' },
    subtitle: { type: String, default: 'خصص معايير البحث للحصول على نتائج دقيقة' },
    activeCount: { type: Number, default: 0 },
});

const emit = defineEmits(['close', 'apply', 'reset']);

const handleKeydown = (e) => {
    if (e.key === 'Escape' && props.show) {
        emit('close');
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <teleport to="body">
        <Transition name="drawer">
            <div v-if="show" class="fixed inset-0 z-50 overflow-hidden font-tajawal" dir="rtl">
                <!-- Backdrop -->
                <div
                    @click="emit('close')"
                    class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity"
                />

                <!-- Slide-Over Drawer Container -->
                <div class="fixed inset-y-0 left-0 max-w-full flex pl-0 sm:pl-10 pointer-events-none">
                    <div
                        class="drawer-panel w-screen max-w-md bg-slate-900 border-r border-slate-800 shadow-2xl flex flex-col justify-between pointer-events-auto"
                    >
                        <!-- Drawer Header -->
                        <div class="p-5 border-b border-slate-800/80 bg-slate-900/90 flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center font-bold text-base">
                                    🔍
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-black text-sm text-white">{{ title }}</h3>
                                        <span v-if="activeCount > 0" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-slate-950">
                                            {{ activeCount }} نشط
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ subtitle }}</p>
                                </div>
                            </div>

                            <button
                                @click="emit('close')"
                                type="button"
                                class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center text-xs transition cursor-pointer"
                            >
                                ✕
                            </button>
                        </div>

                        <!-- Drawer Scrollable Content -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-6">
                            <slot />
                        </div>

                        <!-- Drawer Footer (Sticky Actions) -->
                        <div class="p-4 border-t border-slate-800/80 bg-slate-950/60 flex items-center justify-between gap-3 shrink-0">
                            <button
                                @click="emit('reset')"
                                type="button"
                                class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold transition cursor-pointer flex items-center gap-1.5"
                            >
                                <span>🔄</span>
                                <span>إعادة تعيين</span>
                            </button>

                            <div class="flex items-center gap-2">
                                <button
                                    @click="emit('close')"
                                    type="button"
                                    class="px-4 py-2.5 rounded-2xl border border-slate-700 hover:bg-slate-800 text-slate-300 text-xs font-bold transition cursor-pointer"
                                >
                                    إلغاء
                                </button>

                                <button
                                    @click="emit('apply')"
                                    type="button"
                                    class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white text-xs font-black shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer flex items-center gap-1.5"
                                >
                                    <span>🚀</span>
                                    <span>تطبيق الفلاتر</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </teleport>
</template>

<style scoped>
/* Backdrop Fade Transition */
.drawer-enter-active,
.drawer-leave-active {
    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.drawer-enter-from,
.drawer-leave-to {
    opacity: 0;
}

/* Panel Slide Transition */
.drawer-enter-active .drawer-panel {
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;
}

.drawer-leave-active .drawer-panel {
    transition: transform 0.25s cubic-bezier(0.4, 0, 1, 1), opacity 0.25s ease;
}

.drawer-enter-from .drawer-panel,
.drawer-leave-to .drawer-panel {
    transform: translateX(-100%);
    opacity: 0.7;
}
</style>