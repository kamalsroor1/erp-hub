<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    isOpen: Boolean,
});

const emit = defineEmits(['close']);

const page = usePage();
const stores = page.props.auth?.stores || [];
const activeStore = page.props.auth?.store;
const isSwitching = ref(false);

const selectStore = (storeId) => {
    if (storeId === activeStore?.id) {
        emit('close');
        return;
    }

    isSwitching.value = true;
    router.post('/stores/switch', { store_id: storeId }, {
        preserveScroll: true,
        onFinish: () => {
            isSwitching.value = false;
            emit('close');
        },
    });
};
</script>

<template>
    <!-- Modal Backdrop -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isOpen"
            @click="$emit('close')"
            class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 select-none"
        >
            <!-- Modal Box -->
            <div
                @click.stop
                class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4"
            >
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center text-sm font-black">
                            🏢
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white">تبديل الفرع النشط</h3>
                            <p class="text-[10px] text-slate-400 font-bold">الفروع المصرح لك بالعمل عليها</p>
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

                <!-- Stores List -->
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <button
                        v-for="st in stores"
                        :key="st.id"
                        @click="selectStore(st.id)"
                        type="button"
                        :disabled="isSwitching"
                        class="w-full text-start p-3 rounded-2xl border transition flex items-center justify-between gap-3 touch-active"
                        :class="st.id === activeStore?.id ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-500/50 text-emerald-600 dark:text-emerald-400' : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700/60 text-slate-800 dark:text-slate-200 hover:border-emerald-400'"
                    >
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">
                                {{ st.type === 'main_warehouse' ? '🏬' : (st.type === 'wholesale_van' ? '🚚' : '🏪') }}
                            </span>
                            <div>
                                <div class="text-xs font-black">{{ st.name }}</div>
                                <div class="text-[10px] text-slate-400 font-semibold">{{ st.address || 'فرع معتمد' }}</div>
                            </div>
                        </div>

                        <span v-if="st.id === activeStore?.id" class="text-xs font-black px-2 py-0.5 rounded-full bg-emerald-500 text-white shadow-xs">
                            النشط حالياً ✓
                        </span>
                        <span v-else class="text-[11px] text-slate-400 font-bold">
                            اختيار
                        </span>
                    </button>

                    <div v-if="!stores || stores.length === 0" class="text-center py-6 text-xs text-slate-400">
                        لا توجد فروع إضافية مخصصة لحسابك
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
