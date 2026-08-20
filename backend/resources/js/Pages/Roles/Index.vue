<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    roles: { type: Array, required: true },
    selected_role: { type: Object, default: () => ({}) },
    permission_modules: { type: Object, required: true },
});

const selectedRoleId = ref(props.selected_role?.id || props.roles[0]?.id);
const selectedPermissions = ref([...(props.selected_role?.permissions || [])]);

watch(() => props.selected_role, (newVal) => {
    if (newVal) {
        selectedRoleId.value = newVal.id;
        selectedPermissions.value = [...(newVal.permissions || [])];
    }
});

const selectRole = (id) => {
    router.get('/roles', { role_id: id }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const togglePermission = (permKey) => {
    const idx = selectedPermissions.value.indexOf(permKey);
    if (idx > -1) {
        selectedPermissions.value.splice(idx, 1);
    } else {
        selectedPermissions.value.push(permKey);
    }
};

const isPermSelected = (permKey) => {
    return selectedPermissions.value.includes(permKey);
};

const savePermissions = () => {
    router.put(`/roles/${selectedRoleId.value}`, {
        permissions: selectedPermissions.value,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="$t('roles.title')" />

    <AppLayout>
        <div class="space-y-6 font-tajawal">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🛡️</span>
                        <h1 class="text-xl sm:text-2xl font-black text-white">
                            {{ $t('roles.title') }}
                        </h1>
                    </div>
                    <p class="text-xs text-slate-400 font-bold">
                        {{ $t('roles.subtitle') }}
                    </p>
                </div>

                <Link
                    href="/users"
                    class="h-11 px-4 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold flex items-center gap-1.5 transition"
                >
                    <span>👥</span>
                    <span>{{ $t('roles.users_management') }}</span>
                </Link>
            </div>

            <!-- Main Layout: Roles Column & Permissions Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Roles List (1 Col) -->
                <div class="space-y-3">
                    <h2 class="text-xs font-black text-slate-400">{{ $t('roles.select_role_prompt') }}</h2>
                    <div class="space-y-2">
                        <button
                            v-for="r in roles"
                            :key="r.id"
                            @click="selectRole(r.id)"
                            type="button"
                            class="w-full p-4 rounded-2xl border text-right transition cursor-pointer flex items-center justify-between"
                            :class="selectedRoleId === r.id ? 'bg-amber-500/15 border-amber-500/50 shadow-md' : 'bg-slate-900 border-slate-800 hover:border-slate-700'"
                        >
                            <div>
                                <h3 class="font-black text-xs" :class="selectedRoleId === r.id ? 'text-amber-400' : 'text-white'">
                                    {{ r.label }}
                                </h3>
                                <span class="text-[10px] text-slate-500 font-bold mt-0.5 block">
                                    {{ $t('roles.active_permissions_count', { count: r.permissions_count }) }}
                                </span>
                            </div>
                            <span v-if="selectedRoleId === r.id" class="text-amber-400 font-black">←</span>
                        </button>
                    </div>
                </div>

                <!-- Permissions Matrix (3 Cols) -->
                <div class="lg:col-span-3 space-y-5">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                            <div>
                                <h2 class="text-base font-black text-white flex items-center gap-2">
                                    <span>{{ $t('roles.permissions_for') }}</span>
                                    <span class="text-amber-400">{{ selected_role?.name }}</span>
                                </h2>
                                <p class="text-xs text-slate-400 font-bold mt-0.5">{{ $t('roles.permissions_sub') }}</p>
                            </div>

                            <button
                                @click="savePermissions"
                                type="button"
                                class="h-11 px-6 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 transition transform active:scale-95 cursor-pointer"
                            >
                                {{ $t('roles.save_permissions_btn') }}
                            </button>
                        </div>

                        <!-- Modules Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div
                                v-for="(module, modKey) in permission_modules"
                                :key="modKey"
                                class="bg-slate-950/70 border border-slate-800/80 rounded-2xl p-4 space-y-3"
                            >
                                <h3 class="text-xs font-black text-amber-400 flex items-center gap-2 pb-2 border-b border-slate-800">
                                    <span>{{ module.icon }}</span>
                                    <span>{{ module.title }}</span>
                                </h3>

                                <div class="space-y-2">
                                    <label
                                        v-for="(label, permKey) in module.permissions"
                                        :key="permKey"
                                        class="flex items-start gap-3 p-2 rounded-xl hover:bg-slate-900/60 transition cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="isPermSelected(permKey)"
                                            @change="togglePermission(permKey)"
                                            :disabled="selected_role?.name === 'admin'"
                                            class="rounded text-amber-500 focus:ring-0 mt-0.5"
                                        >
                                        <span class="text-xs font-bold text-slate-300 leading-snug">{{ label }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>