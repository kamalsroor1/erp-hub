<script setup>
import { ref } from 'vue';
import { customerService } from '@/Services/customerService';

const props = defineProps({
    show: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'created']);

const form = ref({ name: '', phone: '', price_tier: 'retail', address: '' });
const isSaving = ref(false);
const errorMessage = ref('');

const saveCustomer = async () => {
    if (!form.value.name) return;
    isSaving.value = true;
    errorMessage.value = '';

    try {
        const res = await customerService.quickCreate(form.value);
        if (res.status === 'success') {
            emit('created', res.customer);
            form.value = { name: '', phone: '', price_tier: 'retail', address: '' };
        }
    } catch (e) {
        errorMessage.value = e.response?.data?.message || 'حدث خطأ أثناء حفظ بيانات العميل';
    } finally {
        isSaving.value = false;
    }
};
</script>

<template>
    <div
        v-if="show"
        @click="emit('close')"
        class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal select-none"
    >
        <div @click.stop class="w-full max-w-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="font-black text-sm sm:text-base text-slate-900 dark:text-white">{{ $t('pos.add_new_customer') }}</h3>
                <button
                    @click="emit('close')"
                    type="button"
                    class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center justify-center text-sm font-bold transition active:scale-90 cursor-pointer shadow-xs shrink-0"
                >
                    ✕
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">{{ $t('pos.customer_name') }}:</label>
                    <input
                        v-model="form.name"
                        type="text"
                        :placeholder="$t('contacts.name')"
                        class="w-full h-11 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-amber-500 shadow-inner"
                    />
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">{{ $t('pos.customer_phone') }}:</label>
                    <input
                        v-model="form.phone"
                        type="tel"
                        placeholder="01012345678"
                        class="w-full h-11 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-slate-900 dark:text-white font-mono placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:border-amber-500 shadow-inner"
                    />
                </div>
                <div class="space-y-1">
                    <label class="block font-bold text-slate-700 dark:text-slate-300">{{ $t('pos.price_tier') }}:</label>
                    <select
                        v-model="form.price_tier"
                        class="w-full h-11 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 shadow-inner cursor-pointer"
                    >
                        <option value="retail">{{ $t('pos.retail_tier') }}</option>
                        <option value="wholesale">{{ $t('pos.wholesale_tier') }}</option>
                    </select>
                </div>

                <div v-if="errorMessage" class="p-3 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-600 dark:text-rose-400 text-xs font-bold">
                    {{ errorMessage }}
                </div>

                <button
                    :disabled="isSaving || !form.name"
                    @click="saveCustomer"
                    type="button"
                    class="w-full h-12 rounded-2xl btn-primary-theme disabled:opacity-50 font-black text-sm transition transform active:scale-95 cursor-pointer shadow-theme-primary flex items-center justify-center gap-2 mt-2"
                >
                    <span>💾</span>
                    <span>{{ isSaving ? '...' : $t('pos.save_select_customer') }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
