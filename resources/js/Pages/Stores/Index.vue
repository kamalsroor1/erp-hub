<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stores: { type: Array, default: () => [] },
});

const showStoreModal = ref(false);
const editingStore = ref(null);

const storeForm = useForm({
    name: '',
    code: '',
    type: 'branch',
    address: '',
    phone: '',
    is_active: true,
});

const openCreateModal = () => {
    editingStore.value = null;
    storeForm.reset();
    storeForm.clearErrors();
    showStoreModal.value = true;
};

const openEditModal = (s) => {
    editingStore.value = s;
    storeForm.clearErrors();
    storeForm.name = s.name;
    storeForm.code = s.code;
    storeForm.type = s.type;
    storeForm.address = s.address || '';
    storeForm.phone = s.phone || '';
    storeForm.is_active = s.is_active;
    showStoreModal.value = true;
};

const saveStore = () => {
    if (editingStore.value) {
        storeForm.put(`/stores/${editingStore.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showStoreModal.value = false;
            }
        });
    } else {
        storeForm.post('/stores', {
            preserveScroll: true,
            onSuccess: () => {
                showStoreModal.value = false;
            }
        });
    }
};
</script>

<template>
    <Head title="إدارة الفروع والمخازن وعربيات التوزيع" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🏬</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            إدارة الفروع والمخازن المركزية وعربيات التوزيع
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        إدارة منافذ البيع المتعددة، خطوط سير العربيات، والتحويلات المخزنية
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <Link
                        href="/store-stocks"
                        class="h-11 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold flex items-center gap-1.5 transition"
                    >
                        <span>📊</span>
                        <span>أرصدة المخازن والفروع</span>
                    </Link>

                    <button
                        @click="openCreateModal"
                        type="button"
                        class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                    >
                        <span class="text-base font-black">+</span>
                        <span>إضافة فرع / مخزن جديد</span>
                    </button>
                </div>
            </div>

            <!-- Stores Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div
                    v-for="s in stores"
                    :key="s.id"
                    class="bg-slate-900 border rounded-3xl p-5 shadow-sm space-y-4 transition hover:border-amber-500/50"
                    :class="s.is_main ? 'border-amber-500/40 bg-gradient-to-br from-slate-900 to-amber-950/15' : 'border-slate-800'"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-slate-800 flex items-center justify-center text-xl">
                                {{ s.type === 'van' ? '🚚' : (s.type === 'warehouse' ? '🏭' : '🏬') }}
                            </div>
                            <div>
                                <h3 class="font-black text-white text-sm flex items-center gap-2">
                                    <span>{{ s.name }}</span>
                                    <span v-if="s.is_main" class="px-2 py-0.5 rounded-md bg-amber-500 text-slate-950 text-[10px] font-black">الرئيسي 👑</span>
                                </h3>
                                <span class="text-xs text-slate-500 font-mono font-bold">{{ s.code }}</span>
                            </div>
                        </div>

                        <span
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                            :class="s.is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'"
                        >
                            {{ s.is_active ? 'نشط' : 'معطل' }}
                        </span>
                    </div>

                    <div class="text-xs text-slate-400 space-y-1 font-tajawal pt-2 border-t border-slate-800/80">
                        <div v-if="s.address">📍 {{ s.address }}</div>
                        <div v-if="s.phone">📱 {{ s.phone }}</div>
                        <div>📦 عدد الأصناف المسجلة: <strong class="text-white font-mono">{{ s.stocks_count }}</strong></div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button
                            @click="openEditModal(s)"
                            type="button"
                            class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs font-bold transition cursor-pointer"
                        >
                            تعديل ✏️
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add / Edit Store Modal -->
        <div
            v-if="showStoreModal"
            @click="showStoreModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-black text-base text-white">
                        {{ editingStore ? 'تعديل بيانات الفرع / المخزن' : 'إضافة فرع / مخزن / عربية توزيع' }}
                    </h3>
                    <button @click="showStoreModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <form @submit.prevent="saveStore" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">اسم الفرع / العربية *</label>
                        <input
                            v-model="storeForm.name"
                            type="text"
                            required
                            placeholder="مثال: فرع مدينة نصر / عربية توزيع 1..."
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">النوع *</label>
                            <select
                                v-model="storeForm.type"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                                <option value="branch">فرع بيع وتوزيع 🏬</option>
                                <option value="warehouse">مخزن رئيسي 🏭</option>
                                <option value="van">عربية نقل وتوزيع 🚚</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">الكود التعريفي</label>
                            <input
                                v-model="storeForm.code"
                                type="text"
                                placeholder="STR-001"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">العنوان / منطقة السير</label>
                        <input
                            v-model="storeForm.address"
                            type="text"
                            placeholder="مثال: شارع عباس العقاد..."
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-300">رقم الهاتف / تليفون المسؤول</label>
                        <input
                            v-model="storeForm.phone"
                            type="text"
                            placeholder="01xxxxxxxxx"
                            class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                        >
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showStoreModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            إلغاء
                        </button>
                        <button
                            type="submit"
                            :disabled="storeForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ storeForm.processing ? 'جاري الحفظ...' : 'حفظ المخزن' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>