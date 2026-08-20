<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { trans } from '@/helpers/trans';

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
        alert(trans('inventory.cannot_disable_main_store') || 'لا يمكن تعطيل الفرع والمخزن الرئيسي للمنشأة');
        return;
    }
    router.post(`/stores/${s.id}/toggle-active`, {}, {
        preserveScroll: true,
    });
};

const deleteStore = (s) => {
    if (!s.can_be_deleted) {
        alert((trans('inventory.cannot_delete_store') || 'لا يمكن حذف الفرع/المخزن') + ':\n- ' + s.deletion_blockers.join('\n- '));
        return;
    }
    if (confirm(trans('common.confirm_delete') || `هل أنت متأكد من حذف (${s.name})؟`)) {
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
    <Head :title="$t('inventory.stores_title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🏬</span>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            {{ $t('inventory.stores_title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                        {{ $t('inventory.stores_subtitle') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                    <Link
                        href="/store-stocks"
                        class="flex-1 sm:flex-none h-11 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center justify-center gap-1.5 transition active:scale-95 shadow-xs"
                    >
                        <span>📊</span>
                        <span>{{ $t('inventory.store_stocks') }}</span>
                    </Link>

                    <button
                        @click="openCreateModal"
                        type="button"
                        class="flex-1 sm:flex-none h-11 px-5 rounded-2xl btn-primary-theme font-bold text-xs flex items-center justify-center gap-2 transition transform active:scale-95 cursor-pointer shadow-theme-primary"
                    >
                        <span class="text-base font-black">+</span>
                        <span>{{ $t('inventory.add_new_store') }}</span>
                    </button>
                </div>
            </div>

            <!-- Stores Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                <div
                    v-for="s in stores"
                    :key="s.id"
                    class="bg-white dark:bg-slate-900 border rounded-3xl p-4 sm:p-5 shadow-xs space-y-4 transition hover:border-theme-primary flex flex-col justify-between"
                    :class="s.is_main ? 'border-theme-primary dark:bg-gradient-to-br dark:from-slate-900 dark:to-slate-950' : 'border-slate-200 dark:border-slate-800'"
                >
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xl shrink-0">
                                    <span v-if="s.type === 'wholesale_van' || s.type === 'van'">🚚</span>
                                    <span v-else-if="s.type === 'main_warehouse' || s.type === 'warehouse'">🏭</span>
                                    <span v-else>🏬</span>
                                </div>
                                <div>
                                    <h3 class="font-black text-slate-900 dark:text-white text-sm flex items-center gap-2">
                                        <span>{{ s.name }}</span>
                                        <span v-if="s.is_main" class="px-2 py-0.5 rounded-md tab-theme-active text-[10px] font-black">{{ $t('inventory.store_type_main') }} 👑</span>
                                    </h3>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-slate-400 dark:text-slate-500 font-mono font-bold">{{ s.code }}</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-tajawal">
                                            ({{ s.type === 'wholesale_van' || s.type === 'van' ? 'عربية توزيع جملة' : (s.type === 'main_warehouse' || s.type === 'warehouse' ? $t('inventory.store_type_main') : $t('inventory.store_type_branch')) }})
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="toggleActive(s)"
                                type="button"
                                class="px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer active:scale-95"
                                :class="s.is_active ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/30' : 'bg-rose-500/20 text-rose-600 dark:text-rose-400 hover:bg-rose-500/30'"
                                :title="s.is_active ? $t('common.active') : $t('common.inactive')"
                            >
                                {{ s.is_active ? '🟢 ' + $t('common.active') : '⚪ ' + $t('common.inactive') }}
                            </button>
                        </div>

                        <div class="text-xs text-slate-500 dark:text-slate-400 space-y-1.5 font-tajawal pt-2 border-t border-slate-100 dark:border-slate-800/80">
                            <div v-if="s.address" class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                                <span>📍</span>
                                <span>{{ s.address }}</span>
                            </div>
                            <div v-if="s.phone" class="flex items-center gap-1.5 font-mono text-slate-700 dark:text-slate-300">
                                <span>📱</span>
                                <span>{{ s.phone }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-1">
                                <span>📦 {{ $t('inventory.total_items_count') }}:</span>
                                <strong class="text-slate-900 dark:text-white font-mono text-xs">{{ s.stocks_count }} {{ $t('inventory.item_unit') }}</strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>🧾 {{ $t('dashboard.invoices_today') || 'الفواتير' }}:</span>
                                <span class="text-slate-700 dark:text-slate-300 font-mono">{{ s.invoices_count || 0 }}</span>
                            </div>
                        </div>

                        <!-- Assigned Staff -->
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800/80 space-y-1.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-500 dark:text-slate-400 font-bold">{{ $t('users.title') || 'الموظفون المعينون' }}:</span>
                                <button
                                    @click="openUserAssignmentModal(s)"
                                    type="button"
                                    class="text-theme-primary hover:underline text-[10px] font-black cursor-pointer"
                                >
                                    + {{ $t('common.edit') || 'إدارة الموظفين' }}
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="u in s.assigned_users"
                                    :key="u.id"
                                    class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold border border-slate-200 dark:border-transparent"
                                >
                                    👤 {{ u.name }}
                                </span>
                                <span v-if="!s.assigned_users || s.assigned_users.length === 0" class="text-[10px] text-slate-400 dark:text-slate-500 italic">
                                    {{ $t('common.no_records') || 'لا يوجد موظفون مخصصون' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-3 flex items-center justify-between gap-2 border-t border-slate-100 dark:border-slate-800">
                        <Link
                            :href="`/store-stocks?store_id=${s.id}`"
                            class="h-10 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center gap-1.5 border border-slate-200 dark:border-transparent active:scale-95 shadow-xs"
                        >
                            <span>📦</span>
                            <span>{{ $t('inventory.store_stocks') }}</span>
                        </Link>

                        <div class="flex items-center gap-2">
                            <button
                                @click="openEditModal(s)"
                                type="button"
                                class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 text-xs font-bold transition active:scale-90 cursor-pointer border border-slate-200 dark:border-transparent flex items-center justify-center"
                                :title="$t('common.edit')"
                            >
                                ✏️
                            </button>

                            <button
                                @click="deleteStore(s)"
                                type="button"
                                class="w-10 h-10 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 dark:text-rose-400 border border-rose-500/30 text-xs transition active:scale-90 cursor-pointer flex items-center justify-center"
                                :class="!s.can_be_deleted ? 'opacity-40 cursor-not-allowed' : ''"
                                :title="s.can_be_deleted ? $t('common.delete') : s.deletion_blockers.join(', ')"
                            >
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add / Edit Store Modal (Smooth Native Pop) -->
        <Teleport to="body">
            <Transition name="modal-zoom">
                <div
                    v-if="showStoreModal"
                    @click="showStoreModal = false"
                    class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 font-tajawal select-none"
                    dir="rtl"
                >
                    <div @click.stop class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                            <h3 class="font-black text-base text-slate-900 dark:text-white">
                                {{ editingStore ? $t('inventory.store_updated') : $t('inventory.add_new_store') }}
                            </h3>
                            <button
                                @click="showStoreModal = false"
                                class="w-9 h-9 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-400 text-xs hover:text-slate-900 dark:hover:text-white cursor-pointer flex items-center justify-center transition active:scale-90 shadow-xs"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <form @submit.prevent="saveStore" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.store_name') }} *</label>
                                <input
                                    v-model="storeForm.name"
                                    type="text"
                                    required
                                    :placeholder="$t('inventory.store_name')"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:border-theme-primary focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.store_type') }} *</label>
                                    <select
                                        v-model="storeForm.type"
                                        class="w-full h-11 px-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:border-theme-primary focus:outline-none shadow-inner font-bold"
                                    >
                                        <option value="retail_shop">{{ $t('inventory.store_type_branch') }} 🏬</option>
                                        <option value="wholesale_van">عربية توزيع جملة 🚚</option>
                                        <option value="main_warehouse">{{ $t('inventory.store_type_main') }} 🏭</option>
                                    </select>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('inventory.store_code') }}</label>
                                    <input
                                        v-model="storeForm.code"
                                        type="text"
                                        placeholder="STR-001"
                                        class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white font-mono focus:border-theme-primary focus:outline-none shadow-inner"
                                    >
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('common.address') }}</label>
                                <input
                                    v-model="storeForm.address"
                                    type="text"
                                    :placeholder="$t('common.address')"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:border-theme-primary focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $t('common.phone') }}</label>
                                <input
                                    v-model="storeForm.phone"
                                    type="tel"
                                    inputmode="tel"
                                    placeholder="01xxxxxxxxx"
                                    class="w-full h-11 px-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white font-mono focus:border-theme-primary focus:outline-none shadow-inner"
                                >
                            </div>

                            <div class="flex items-center gap-2 pt-1">
                                <input
                                    id="is_main_check"
                                    v-model="storeForm.is_main"
                                    type="checkbox"
                                    class="rounded accent-theme-primary w-4 h-4 cursor-pointer"
                                >
                                <label for="is_main_check" class="text-xs font-bold text-theme-primary cursor-pointer">
                                    {{ $t('inventory.store_type_main') }} 👑
                                </label>
                            </div>

                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                                <button
                                    @click="showStoreModal = false"
                                    type="button"
                                    class="h-11 px-5 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition active:scale-95 cursor-pointer shadow-xs"
                                >
                                    {{ $t('common.cancel') }}
                                </button>
                                <button
                                    type="submit"
                                    :disabled="storeForm.processing"
                                    class="h-11 px-6 rounded-2xl btn-primary-theme text-xs font-black transition transform active:scale-95 cursor-pointer disabled:opacity-50 shadow-theme-primary"
                                >
                                    {{ storeForm.processing ? '...' : $t('common.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Staff Assignment Modal (Smooth Native Pop) -->
        <Teleport to="body">
            <Transition name="modal-zoom">
                <div
                    v-if="showUserModal"
                    @click="showUserModal = false"
                    class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 font-tajawal select-none"
                    dir="rtl"
                >
                    <div @click.stop class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                            <h3 class="font-black text-base text-slate-900 dark:text-white flex items-center gap-2">
                                <span>👥 {{ $t('users.title') || 'تعيين موظفي الفرع' }}:</span>
                                <span class="text-theme-primary">{{ targetStore?.name }}</span>
                            </h3>
                            <button
                                @click="showUserModal = false"
                                class="w-9 h-9 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-400 text-xs hover:text-slate-900 dark:hover:text-white cursor-pointer flex items-center justify-center transition active:scale-90 shadow-xs"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <form @submit.prevent="saveUserAssignment" class="space-y-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400">حدد الموظفين والكاشيرات وسائقي التوزيع المسموح لهم بالعمل على هذا الفرع:</p>

                            <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                                <label
                                    v-for="u in all_users"
                                    :key="u.id"
                                    class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-theme-primary cursor-pointer transition active:scale-98"
                                >
                                    <div class="flex items-center gap-2.5">
                                        <input
                                            type="checkbox"
                                            :value="u.id"
                                            v-model="userAssignmentForm.user_ids"
                                            class="rounded accent-theme-primary w-4 h-4 cursor-pointer"
                                        >
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">{{ u.name }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ u.email }}</span>
                                </label>
                            </div>

                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                                <button
                                    @click="showUserModal = false"
                                    type="button"
                                    class="h-11 px-5 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition active:scale-95 cursor-pointer shadow-xs"
                                >
                                    {{ $t('common.cancel') }}
                                </button>
                                <button
                                    type="submit"
                                    :disabled="userAssignmentForm.processing"
                                    class="h-11 px-6 rounded-2xl btn-primary-theme text-xs font-black transition transform active:scale-95 cursor-pointer disabled:opacity-50 shadow-theme-primary"
                                >
                                    {{ userAssignmentForm.processing ? '...' : $t('common.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>