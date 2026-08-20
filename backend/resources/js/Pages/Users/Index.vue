<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { trans } from '@/helpers/trans';

const props = defineProps({
    users: { type: Object, required: true },
    roles: { type: Array, default: () => [] },
    stores: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || 'all');

const applyFilters = () => {
    router.get('/users', {
        search: search.value || undefined,
        role: roleFilter.value !== 'all' ? roleFilter.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

let searchTimer = null;
watch([search, roleFilter], () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        applyFilters();
    }, 400);
});

// Add / Edit Modal
const showModal = ref(false);
const editingUser = ref(null);

const userForm = useForm({
    name: '',
    phone: '',
    email: '',
    password: '',
    role: 'cashier',
    default_store_id: props.stores[0]?.id || null,
    is_active: true,
});

const storeOptions = computed(() => [
    { id: null, name: trans('users.all_stores_option') || 'كافة الفروع / بدون تقييد' },
    ...props.stores
]);

const openCreateModal = () => {
    editingUser.value = null;
    userForm.reset();
    userForm.clearErrors();
    showModal.value = true;
};

const openEditModal = (u) => {
    editingUser.value = u;
    userForm.clearErrors();
    userForm.name = u.name;
    userForm.phone = u.phone;
    userForm.email = u.email || '';
    userForm.password = '';
    userForm.role = u.primary_role;
    userForm.default_store_id = u.default_store_id;
    userForm.is_active = u.is_active;
    showModal.value = true;
};

const saveUser = () => {
    if (editingUser.value) {
        userForm.put(`/users/${editingUser.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
            }
        });
    } else {
        userForm.post('/users', {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
            }
        });
    }
};

const toggleUser = (u) => {
    router.post(`/users/${u.id}/toggle-active`, {}, {
        preserveScroll: true,
    });
};

const deleteUser = (u) => {
    const confirmMsg = trans('users.delete_confirm', { name: u.name }) || `هل أنت متأكد من حذف حساب (${u.name})؟`;
    if (confirm(confirmMsg)) {
        router.delete(`/users/${u.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="$t('users.title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">👥</span>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            {{ $t('users.title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                        {{ $t('users.subtitle') }}
                    </p>
                </div>

                <div class="flex items-center gap-2.5">
                    <Link
                        href="/roles"
                        class="h-11 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center gap-1.5 transition"
                    >
                        <span>🛡️</span>
                        <span>{{ $t('users.matrix_btn') }}</span>
                    </Link>

                    <button
                        @click="openCreateModal"
                        type="button"
                        class="h-11 px-5 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-600/30 transition transform active:scale-95 cursor-pointer"
                    >
                        <span class="text-base font-black">+</span>
                        <span>{{ $t('users.create_btn') }}</span>
                    </button>
                </div>
            </div>

            <!-- Quick Filter Bar -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 shadow-xs space-y-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="w-full md:w-96 relative">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="$t('users.search_placeholder')"
                            class="w-full pr-10 pl-4 py-2.5 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none transition shadow-inner"
                        >
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 text-xs pointer-events-none">
                            🔍
                        </span>
                    </div>

                    <div class="w-full md:w-auto flex items-center gap-2">
                        <select
                            v-model="roleFilter"
                            class="px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none"
                        >
                            <option value="all">{{ $t('users.all_roles') }}</option>
                            <option value="admin">{{ $t('users.role_admin') }}</option>
                            <option value="cashier">{{ $t('users.role_cashier') }}</option>
                            <option value="storekeeper">{{ $t('users.role_storekeeper') }}</option>
                            <option value="accountant">{{ $t('users.role_accountant') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-xs space-y-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-bold">
                                <th class="pb-3">{{ $t('auth.name') }}</th>
                                <th class="pb-3">{{ $t('auth.phone') }}</th>
                                <th class="pb-3">{{ $t('common.user') }}</th>
                                <th class="pb-3">{{ $t('common.store') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.status') }}</th>
                                <th class="pb-3 text-center">{{ $t('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 font-sans">
                            <tr v-for="u in users.data" :key="u.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="py-3.5">
                                    <div class="font-black text-slate-900 dark:text-white font-tajawal text-sm">{{ u.name }}</div>
                                    <div v-if="u.email" class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ u.email }}</div>
                                </td>

                                <td class="py-3.5 font-mono font-bold text-amber-600 dark:text-amber-400 text-xs">
                                    {{ u.phone }}
                                </td>

                                <td class="py-3.5 font-tajawal">
                                    <span
                                        class="px-2.5 py-1 rounded-xl text-xs font-bold"
                                        :class="[
                                            u.primary_role === 'admin' ? 'bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/30' :
                                            (u.primary_role === 'cashier' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-transparent')
                                        ]"
                                    >
                                        {{ u.primary_role === 'admin' ? $t('users.role_admin') : (u.primary_role === 'cashier' ? $t('users.role_cashier') : (u.primary_role === 'storekeeper' ? $t('users.role_storekeeper') : $t('users.role_accountant'))) }}
                                    </span>
                                </td>

                                <td class="py-3.5 font-tajawal text-slate-700 dark:text-slate-300">
                                    {{ u.default_store_name || $t('users.all_stores_option') }}
                                </td>

                                <td class="py-3.5 text-center">
                                    <button
                                        @click="toggleUser(u)"
                                        type="button"
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-tajawal transition cursor-pointer"
                                        :class="u.is_active ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30'"
                                    >
                                        {{ u.is_active ? $t('users.status_active') : $t('users.status_inactive') }}
                                    </button>
                                </td>

                                <td class="py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 font-tajawal">
                                        <button
                                            @click="openEditModal(u)"
                                            type="button"
                                            class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 text-xs font-bold transition cursor-pointer border border-slate-200 dark:border-transparent"
                                        >
                                            {{ $t('common.edit') }} ✏️
                                        </button>

                                        <button
                                            @click="deleteUser(u)"
                                            type="button"
                                            class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 transition cursor-pointer"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="!users.data || users.data.length === 0" class="py-16 text-center space-y-2">
                        <span class="text-3xl">👥</span>
                        <p class="text-xs font-bold text-slate-400 font-tajawal">{{ $t('users.empty_users') }}</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="users.links && users.links.length > 3" class="pt-4 border-t border-slate-800/80 flex items-center justify-between font-sans">
                    <span class="text-xs text-slate-400 font-tajawal">
                        {{ $t('common.showing') }} {{ users.from || 0 }} {{ $t('common.to') }} {{ users.to || 0 }} {{ $t('common.of') }} {{ users.total || 0 }}
                    </span>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, lIdx) in users.links" :key="lIdx">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                :class="link.active ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-3 py-1.5 rounded-xl text-xs text-slate-600 font-bold"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add / Edit User Modal -->
        <div
            v-if="showModal"
            @click="showModal = false"
            class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4 font-tajawal"
            dir="rtl"
        >
            <div @click.stop class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-black text-base text-white">
                        {{ editingUser ? $t('users.edit_title') : $t('users.create_title') }}
                    </h3>
                    <button @click="showModal = false" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 text-xs hover:text-white">✕</button>
                </div>

                <form @submit.prevent="saveUser" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('users.full_name') }}</label>
                            <input
                                v-model="userForm.name"
                                type="text"
                                required
                                placeholder="مثال: أحمد محمد"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('users.phone_for_login') }}</label>
                            <input
                                v-model="userForm.phone"
                                type="text"
                                required
                                placeholder="01xxxxxxxxx"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('users.email_optional') }}</label>
                            <input
                                v-model="userForm.email"
                                type="email"
                                placeholder="user@example.com"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">
                                {{ editingUser ? $t('users.password_leave_blank') : $t('users.password_required') }}
                            </label>
                            <input
                                v-model="userForm.password"
                                type="password"
                                :required="!editingUser"
                                placeholder="••••••••"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white font-mono focus:border-amber-500 focus:outline-none"
                            >
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('users.role_field') }}</label>
                            <select
                                v-model="userForm.role"
                                class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white focus:border-amber-500 focus:outline-none"
                            >
                                <option value="cashier">{{ $t('users.role_cashier') }}</option>
                                <option value="storekeeper">{{ $t('users.role_storekeeper') }}</option>
                                <option value="accountant">{{ $t('users.role_accountant') }}</option>
                                <option value="admin">{{ $t('users.role_admin') }}</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-300">{{ $t('users.assigned_store') }}</label>
                            <SearchableSelect
                                v-model="userForm.default_store_id"
                                :options="storeOptions"
                                :placeholder="$t('inventory.select_store')"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                        <button
                            @click="showModal = false"
                            type="button"
                            class="px-4 py-2.5 rounded-2xl border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-800 transition cursor-pointer"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="userForm.processing"
                            class="px-5 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg shadow-amber-500/20 transition transform active:scale-95 cursor-pointer disabled:opacity-50"
                        >
                            {{ userForm.processing ? $t('users.saving_user') : $t('users.save_user') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>