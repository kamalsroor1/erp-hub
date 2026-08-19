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
        class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div @click.stop class="w-full max-w-sm bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-2xl space-y-3">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <h3 class="font-black text-sm text-white">{{ $t('pos.add_new_customer') }}</h3>
                <button @click="emit('close')" class="w-7 h-7 rounded-xl bg-slate-800 text-slate-400 text-xs">✕</button>
            </div>

            <div class="space-y-2.5 text-xs">
                <div>
                    <label class="block text-slate-400 mb-1">اسم العميل:</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="مثال: كافيه الأهرام"
                        class="w-full h-9 bg-slate-800 border border-slate-700 rounded-xl px-3 text-white focus:outline-none focus:border-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">رقم الهاتف:</label>
                    <input
                        v-model="form.phone"
                        type="tel"
                        placeholder="01012345678"
                        class="w-full h-9 bg-slate-800 border border-slate-700 rounded-xl px-3 text-white font-mono focus:outline-none focus:border-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">فئة السعر:</label>
                    <select
                        v-model="form.price_tier"
                        class="w-full h-9 bg-slate-800 border border-slate-700 rounded-xl px-3 text-white focus:outline-none focus:border-indigo-500"
                    >
                        <option value="retail">تجزئة (قطاعي)</option>
                        <option value="wholesale">جملة</option>
                    </select>
                </div>

                <div v-if="errorMessage" class="p-2 rounded-lg bg-rose-500/20 text-rose-300 text-[10px]">
                    {{ errorMessage }}
                </div>

                <button
                    :disabled="isSaving || !form.name"
                    @click="saveCustomer"
                    type="button"
                    class="w-full h-10 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-black text-xs transition mt-2"
                >
                    {{ isSaving ? 'جاري الحفظ...' : 'حفظ واختيار العميل' }}
                </button>
            </div>
        </div>
    </div>
</template>
