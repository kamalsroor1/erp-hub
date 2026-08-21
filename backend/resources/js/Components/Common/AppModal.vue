<script setup>
import { watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: ''
    },
    subtitle: {
        type: String,
        default: ''
    },
    icon: {
        type: [String, Object, Function],
        default: null
    },
    maxWidth: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', 'full'].includes(v)
    },
    closeable: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['close']);

const maxWidthClass = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
    '3xl': 'max-w-3xl',
    '4xl': 'max-w-4xl',
    '5xl': 'max-w-5xl',
    full: 'max-w-full m-4',
}[props.maxWidth];

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const handleKeyDown = (e) => {
    if (e.key === 'Escape' && props.show && props.closeable) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
});

watch(() => props.show, (val) => {
    if (val) {
        document.body.classList.add('overflow-hidden');
    } else {
        document.body.classList.remove('overflow-hidden');
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-zoom">
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 font-tajawal select-none"
                dir="rtl"
                @click="close"
            >
                <div
                    class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4 text-slate-900 dark:text-white max-h-[90vh] flex flex-col transition-all"
                    :class="maxWidthClass"
                    @click.stop
                >
                    <!-- Header -->
                    <div v-if="title || $slots.header" class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 shrink-0">
                        <slot name="header">
                            <div class="flex items-center gap-2.5">
                                <span v-if="typeof icon === 'string'" class="text-xl">{{ icon }}</span>
                                <component :is="icon" v-else-if="icon" class="w-5 h-5 text-theme-primary" />
                                <div>
                                    <h3 class="font-black text-sm sm:text-base text-slate-900 dark:text-white">
                                        {{ title }}
                                    </h3>
                                    <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ subtitle }}
                                    </p>
                                </div>
                            </div>
                        </slot>

                        <button
                            v-if="closeable"
                            type="button"
                            class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:hover:text-white text-xs font-bold flex items-center justify-center transition cursor-pointer"
                            @click="close"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Body / Content -->
                    <div class="flex-1 overflow-y-auto pr-0.5">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div v-if="$slots.footer" class="border-t border-slate-200 dark:border-slate-800 pt-3 shrink-0">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
