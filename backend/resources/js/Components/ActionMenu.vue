<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { MoreVertical, X } from 'lucide-vue-next';

const props = defineProps({
    items: {
        type: Array,
        default: () => []
        // Each item: { label: string, icon?: any, href?: string, onClick?: () => void, variant?: 'default'|'danger'|'warning'|'success', show?: boolean, description?: string }
    },
    title: {
        type: String,
        default: ''
    },
    buttonClass: {
        type: String,
        default: ''
    },
    align: {
        type: String,
        default: 'end' // 'start' | 'end'
    }
});

const isOpen = ref(false);
const menuRef = ref(null);

const toggleMenu = () => {
    isOpen.value = !isOpen.value;
};

const closeMenu = () => {
    isOpen.value = false;
};

const handleItemClick = (item) => {
    closeMenu();
    if (item.onClick) {
        item.onClick();
    }
};

const handleClickOutside = (e) => {
    if (menuRef.value && !menuRef.value.contains(e.target)) {
        closeMenu();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="menuRef" class="relative inline-block text-right font-tajawal">
        <!-- Trigger Button -->
        <button
            @click.stop="toggleMenu"
            type="button"
            class="h-9.5 min-w-[38px] px-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700/70 flex items-center justify-center gap-1 transition active:scale-90 cursor-pointer shadow-xs"
            :class="buttonClass"
            :title="title || $t('common.actions') || 'الإجراءات'"
        >
            <MoreVertical class="w-4 h-4 text-slate-600 dark:text-slate-300" />
            <slot name="trigger" />
        </button>

        <!-- Desktop Dropdown (md and up) -->
        <div
            v-if="isOpen"
            class="hidden md:block absolute z-50 mt-1.5 w-56 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-1.5 space-y-1 select-none animate-in fade-in zoom-in-95 duration-100"
            :class="align === 'start' ? 'left-0' : 'right-0'"
        >
            <div v-if="title" class="px-3 py-1.5 text-[11px] font-black text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800/80 truncate">
                {{ title }}
            </div>

            <template v-for="(item, idx) in items" :key="idx">
                <template v-if="item.show !== false">
                    <!-- Link Item -->
                    <Link
                        v-if="item.href"
                        :href="item.href"
                        @click="closeMenu"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition text-right cursor-pointer"
                        :class="[
                            item.variant === 'danger'
                                ? 'text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40'
                                : (item.variant === 'warning'
                                    ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40'
                                    : (item.variant === 'success'
                                        ? 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40'
                                        : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800'))
                        ]"
                    >
                        <component v-if="item.icon" :is="item.icon" class="w-4 h-4 shrink-0" />
                        <span v-else-if="item.emoji" class="text-sm shrink-0">{{ item.emoji }}</span>
                        <div class="flex-1 min-w-0">
                            <span class="block truncate">{{ item.label }}</span>
                            <span v-if="item.description" class="block text-[10px] text-slate-400 font-normal truncate">{{ item.description }}</span>
                        </div>
                    </Link>

                    <!-- Button Action Item -->
                    <button
                        v-else
                        @click="handleItemClick(item)"
                        type="button"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition text-right cursor-pointer"
                        :class="[
                            item.variant === 'danger'
                                ? 'text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40'
                                : (item.variant === 'warning'
                                    ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40'
                                    : (item.variant === 'success'
                                        ? 'text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40'
                                        : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800'))
                        ]"
                    >
                        <component v-if="item.icon" :is="item.icon" class="w-4 h-4 shrink-0" />
                        <span v-else-if="item.emoji" class="text-sm shrink-0">{{ item.emoji }}</span>
                        <div class="flex-1 min-w-0">
                            <span class="block truncate">{{ item.label }}</span>
                            <span v-if="item.description" class="block text-[10px] text-slate-400 font-normal truncate">{{ item.description }}</span>
                        </div>
                    </button>
                </template>
            </template>
        </div>

        <!-- Mobile Bottom Action Sheet (Screens < md) -->
        <Teleport to="body">
            <div
                v-if="isOpen"
                class="md:hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-end justify-center font-tajawal select-none"
                @click="closeMenu"
            >
                <div
                    @click.stop
                    class="w-full bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 rounded-t-3xl p-5 shadow-2xl space-y-4 max-h-[85vh] flex flex-col text-slate-900 dark:text-white animate-in slide-in-from-bottom duration-200 safe-bottom"
                >
                    <!-- Handle & Header -->
                    <div class="space-y-3">
                        <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full mx-auto"></div>
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2.5">
                            <div>
                                <h3 class="font-black text-sm text-slate-900 dark:text-white">{{ title || $t('common.actions') || 'إجراءات الفاتورة' }}</h3>
                                <p class="text-[11px] text-slate-400 font-bold">اختر الإجراء المطلوب تنفيذه</p>
                            </div>
                            <button
                                @click="closeMenu"
                                type="button"
                                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-400 flex items-center justify-center active:scale-90 cursor-pointer"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Action List -->
                    <div class="overflow-y-auto space-y-2 py-1">
                        <template v-for="(item, idx) in items" :key="idx">
                            <template v-if="item.show !== false">
                                <!-- Link Item -->
                                <Link
                                    v-if="item.href"
                                    :href="item.href"
                                    @click="closeMenu"
                                    class="w-full min-h-[48px] px-4 py-3 rounded-2xl flex items-center gap-3.5 text-xs font-black transition text-right active:scale-98 border shadow-xs"
                                    :class="[
                                        item.variant === 'danger'
                                            ? 'bg-rose-500/10 border-rose-500/25 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20'
                                            : (item.variant === 'warning'
                                                ? 'bg-amber-500/10 border-amber-500/25 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20'
                                                : (item.variant === 'success'
                                                    ? 'bg-emerald-500/10 border-emerald-500/25 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20'
                                                    : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700/60 text-slate-800 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800'))
                                    ]"
                                >
                                    <component v-if="item.icon" :is="item.icon" class="w-5 h-5 shrink-0" />
                                    <span v-else-if="item.emoji" class="text-base shrink-0">{{ item.emoji }}</span>
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-xs font-black truncate">{{ item.label }}</span>
                                        <span v-if="item.description" class="block text-[10px] text-slate-400 font-bold truncate">{{ item.description }}</span>
                                    </div>
                                    <span class="text-slate-400 text-xs">←</span>
                                </Link>

                                <!-- Button Action Item -->
                                <button
                                    v-else
                                    @click="handleItemClick(item)"
                                    type="button"
                                    class="w-full min-h-[48px] px-4 py-3 rounded-2xl flex items-center gap-3.5 text-xs font-black transition text-right active:scale-98 border shadow-xs cursor-pointer"
                                    :class="[
                                        item.variant === 'danger'
                                            ? 'bg-rose-500/10 border-rose-500/25 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20'
                                            : (item.variant === 'warning'
                                                ? 'bg-amber-500/10 border-amber-500/25 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20'
                                                : (item.variant === 'success'
                                                    ? 'bg-emerald-500/10 border-emerald-500/25 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20'
                                                    : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700/60 text-slate-800 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800'))
                                    ]"
                                >
                                    <component v-if="item.icon" :is="item.icon" class="w-5 h-5 shrink-0" />
                                    <span v-else-if="item.emoji" class="text-base shrink-0">{{ item.emoji }}</span>
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-xs font-black truncate">{{ item.label }}</span>
                                        <span v-if="item.description" class="block text-[10px] text-slate-400 font-bold truncate">{{ item.description }}</span>
                                    </div>
                                    <span class="text-slate-400 text-xs">←</span>
                                </button>
                            </template>
                        </template>
                    </div>

                    <!-- Cancel / Close Button -->
                    <button
                        @click="closeMenu"
                        type="button"
                        class="w-full h-12 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center transition active:scale-95 cursor-pointer shadow-xs"
                    >
                        {{ $t('common.cancel') || 'إغلاق القائمة' }}
                    </button>
                </div>
            </div>
        </Teleport>
    </div>
</template>
