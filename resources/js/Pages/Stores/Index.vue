<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stores: { type: Array, default: () => [] },
    all_users: { type: Array, default: () => [] },
});

// Create / Edit Store Modal
const showStoreModal = ref(false);
const editingStore = ref(null);

const storeForm = useForm({
    name: '',
    code: '',
    type: 'retail_shop',
    address: '',
    phone: '',
    is_active: true,
    is_main: false,
});

const openCreateModal = () => {
    editingStore.value = null;
    storeForm.reset();
    storeForm.clearErrors();
    storeForm.type = 'retail_shop';
    storeForm.is_active = true;
    storeForm.is_main = false;
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
    storeForm.is_main = s.is_main;
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

const toggleActive = (s) => {
    if (s.is_main && s.is_active) {
        alert('لا يمكن تعطيل الفرع والمخزن الرئيسي للمنشأة');
        return;
    }
    router.post(`/stores/${s.id}/toggle-active`, {}, {
        preserveScroll: true,
    });
};

const deleteStore = (s) => {
    if (!s.can_be_deleted) {
        alert('لا يمكن حذف الفرع/المخزن:\n- ' + s.deletion_blockers.join('\n- '));
        return;
    }
    if (confirm(`هل أنت متأكد من نقل الفرع (${s.name}) إلى سلة المحذوفات؟`)) {
        router.delete(`/stores/${s.id}`, {
            preserveScroll: true,
        });
    }
};

// Staff Assignment Modal
const showUserModal = ref(false);
const targetStore = ref(null);
const userAssignmentForm = useForm({
    user_ids: [],
});

const openUserAssignmentModal = (s) => {
    targetStore.value = s;
    userAssignmentForm.user_ids = [...(s.assigned_user_ids || [])];
    showUserModal.value = true;
};

const saveUserAssignment = () => {
    if (!targetStore.value) return;
    userAssignmentForm.post(`/stores/${targetStore.value.id}/assign-users`, {
        preserveScroll: true,
        onSuccess: () => {
            showUserModal.value = false;
        }
    });
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
                        إدارة منافذ البيع المتعددة، خطوط سير عربيات التوزيع الجملة، والتحويلات المخزنية
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
                        <span>إضافة فرع / عربية توزيع</span>
                    </button>
                </div>
            </div>

            <!-- Stores Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div
                    v-for="s in stores"
                    :key="s.id"
                    class="bg-slate-900 border rounded-3xl p-5 shadow-sm space-y-4 transition hover:border-amber-500/50 flex flex-col justify-between"
                    :class="s.is_main ? 'border-amber-500/40 bg-gradient-to-br from-slate-900 to-amber-950/15' : 'border-slate-800'"
                >
                    <div class="space-y-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-slate-800 flex items-center justify-center text-xl">
                                    <span v-if="s.type === 'wholesale_van' || s.type === 'van'">🚚</span>
                                    <span v-else-if="s.type === 'main_warehouse' || s.type === 'warehouse'">🏭</span>
                                    <span v-else>🏬</span>
                                </div>
                                <div>
                                    <h3 class="font-black text-white text-sm flex items-center gap-2">
                                        <span>{{ s.name }}</span>
                                        <span v-if="s.is_main" class="px-2 py-0.5 rounded-md bg-amber-500 text-slate-950 text-[10px] font-black">الرئيسي 👑</span>
                                    </h3>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-slate-500 font-mono font-bold">{{ s.code }}</span>
                                        <span class="text-[10px] text-slate-400 font-tajawal">
                                            ({{ s.type === 'wholesale_van' || s.type === 'van' ? 'عربية توزيع جملة' : (s.type === 'main_warehouse' || s.type === 'warehouse' ? 'مخزن رئيسي' : 'محل قطاعي') }})
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="toggleActive(s)"
                                type="button"
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold transition cursor-pointer"
                                :class="s.is_active ? 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30' : 'bg-rose-500/20 text-rose-400 hover:bg-rose-500/30'"
                                :title="s.is_active ? 'الفرع نشط (اضغط للتعطيل)' : 'الفرع معطل (اضغط للتفعيل)'"
                            >
                                {{ s.is_active ? '🟢 نشط' : '⚪ معطل' }}
                            </button>
                        </div>

                        <div class="text-xs text-slate-400 space-y-1.5 font-tajawal pt-2 border-t border-slate-800/80">
                            <div v-if="s.address" class="flex items-center gap-1.5 text-slate-300">
                                <span>📍</span>
                                <span>{{ s.address }}</span>
                            </div>
                            <div v-if="s.phone" class="flex items-center gap-1.5 font-mono text-slate-300">
                                <span>📱</span>
                                <span>{{ s.phone }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-1">
                                <span>📦 عدد الأصناف بالمخزن:</span>
                                <strong class="text-white font-mono text-xs">{{ s.stocks_count }} صنف</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>🧾 فواتير المبيعات:</span>
                                <span class="text-slate-300 font-mono">{{ s.invoices_count || 0 }} فاتورة</span>
                            </div>
                        </div>

                        <!-- Assigned Staff -->
                        <div class="pt-2 border-t border-slate-800/80 space-y-1.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-400 font-bold">الموظفون المعينون:</span>
                                <button
                                    @click="openUserAssignmentModal(s)"
                                    type="button"
                                    class="text-amber-400 hover:text-amber-300 text-[10px] font-black cursor-pointer"
                                >
                                    + إدارة الموظفين
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="u in s.assigned_users"
                                    :key="u.id"
                                    class="px-2 py-0.5 rounded-lg bg-slate-800 text-slate-300 text-[10px] font-bold"
                                >
                                    👤 {{ u.name }}
                                </span>
                                <span v-if="!s.assigned_users || s.assigned_users.length === 0" class="text-[10px] text-slate-500 italic">
                                    لا يوجد موظفون مخصصون
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-3 flex items-center justify-between gap-2 border-t border-slate-800">
                        <Link
                            :href="`/store-stocks?store_id=${s.id}`"
                            class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition flex items-center gap-1"
                        >
                            <span>📦</span>
                            <span>الأرصدة</span>
                        </Link>

                        <div class="flex items-center gap-1.5">
                            <button
                                @click="openEditModal(s)"
                                type="button"
                                class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs font-bold transition cursor-pointer"
                                title="تعديل بيانات الفرع"
                            >
                                ✏️
                            </button>

                            <button
                                @click="deleteStore(s)"
                                type="button"
                                class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs transition cursor-pointer"
                                :class="!s.can_be_deleted ? 'opacity-40 cursor-not-allowed' : ''"
                                :title="s.can_be_deleted ? 'حذف الفرع' : s.deletion_blockers.join(', ')"
                            >
                                🗑️
                            </button>
                        </div>
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
                                <option value="retail_shop">محل بيع قطاعي 🏬</option>
                                <option value="wholesale_van">عربية توزيع جملة 🚚</option>
                                <option value="main_warehouse">مخزن رئيسي 🏭</option>
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

                    <div class="flex items-center gap-2 pt-1">
                        <input
                            id="is_main_check"
                            v-model="storeForm.is_main"
                            type="checkbox"
                            class="rounded accent-amber-500 w-4 h-4 cursor-pointer"
                        >
                        <label for="is_main_check" class="text-xs font-bold text-amber-400 cursor-pointer">
                            تعيين كمقر رئيسي للمنشأة 👑
                        </label>
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
                            {{ storeForm.processing ? 'جاري الحفظ...' : 'حفظ البيانات' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Staff Assignment Modal -->
        <div
            v-if="showUserModal"
            @click="showUserModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-black text-base text-white flex items-center gap-2">
                        <span>👥 تعيين موظفي الفرع:</span>
                        <span class="text-amber-400">{{ targetStore?.name }}</span>
                    </h3>
                    <button @click="showUserModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <form @submit.prevent="saveUserAssignment" class="space-y-4">
                    <p class="text-xs text-slate-400">حدد الموظفين والكاشيرات وسائقي التوزيع المسموح لهم بالعمل على هذا الفرع:</p>

                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                        <label
                            v-for="u in all_users"
                            :key="u.id"
                            class="flex items-center justify-between p-3 rounded-2xl bg-slate-950 border border-slate-800 hover:border-amber-500/40 cursor-pointer transition"
                        >
                            <div class="flex items-center gap-2.5">
                                <input
                                    type="checkbox"
                                    :value="u.id"
                                    v-model="userAssignmentForm.user_ids"
                                    class="rounded accent-amber-500 w-4 h-4 cursor-pointer"
                                >
                                <span class="text-xs font-bold text-white">{{ u.name }}</span>
                            </div>
                            <span class="text-[10px] text-slate-500 font-mono">{{ u.email }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showUserModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            إلغاء
                        </button>
                        <button
                            type="submit"
                            :disabled="userAssignmentForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ userAssignmentForm.processing ? 'جاري الحفظ...' : 'حفظ التعيينات' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>