<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { trans } from '@/helpers/trans';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const flash = computed(() => page.props.flash || {});

const navItems = computed(() => [
    { name: trans('super.dashboard'), href: '/admin/super', icon: '📊', active: page.url === '/admin/super' },
    { name: trans('super.tenants'), href: '/admin/super/tenants', icon: '🏪', active: page.url.startsWith('/admin/super/tenants') },
    { name: trans('super.plans'), href: '/admin/super/plans', icon: '💼', active: page.url.startsWith('/admin/super/plans') },
]);
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col antialiased selection:bg-indigo-500 selection:text-white">
        <!-- Top Navbar -->
        <header class="h-16 border-b border-indigo-900/40 bg-slate-900/90 backdrop-blur-md sticky top-0 z-40 px-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link href="/admin/super" class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white font-black text-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        🛡️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-black text-sm tracking-tight text-white">
                                {{ $t('super.platform_title') }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                                {{ $t('super.central_platform') }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-bold hidden sm:block">
                            {{ $t('super.platform_subtitle') }}
                        </p>
                    </div>
                </Link>
            </div>

            <!-- Top Actions -->
            <div class="flex items-center gap-3">
                <Link
                    href="/"
                    class="h-9 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 flex items-center gap-1.5 transition"
                >
                    <span>☕</span>
                    <span class="hidden sm:inline">{{ $t('super.back_to_pos') }}</span>
                </Link>

                <div class="text-left hidden md:block pl-2 border-r border-slate-800 pr-3">
                    <div class="text-xs font-black text-white">{{ user?.name || 'مدير المنصة' }}</div>
                    <div class="text-[10px] text-indigo-400 font-mono font-bold">SUPER ADMIN</div>
                </div>

                <button
                    @click="$inertia.post('/admin/logout')"
                    type="button"
                    class="h-9 px-3 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-xs font-bold text-rose-400 flex items-center gap-1.5 transition cursor-pointer"
                    title="تسجيل الخروج من لوحة السوبر أدمن"
                >
                    <span>🚪</span>
                    <span class="hidden sm:inline">خروج</span>
                </button>
            </div>
        </header>

        <!-- Body Shell -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar -->
            <aside class="w-64 bg-slate-900/60 border-l border-indigo-900/30 flex flex-col p-4 space-y-6">
                <div class="space-y-1">
                    <div class="px-3 text-[11px] font-black tracking-wider text-indigo-400 uppercase mb-2">
                        {{ $t('super.platform_title') }}
                    </div>

                    <Link
                        v-for="(item, idx) in navItems"
                        :key="idx"
                        :href="item.href"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition group"
                        :class="item.active ? 'bg-indigo-600 text-white font-black shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
                    >
                        <span class="text-base">{{ item.icon }}</span>
                        <span>{{ item.name }}</span>
                    </Link>
                </div>

                <div class="mt-auto p-4 rounded-2xl bg-gradient-to-br from-indigo-950/60 to-slate-900 border border-indigo-800/40 text-xs space-y-2">
                    <div class="font-black text-white flex items-center gap-1.5">
                        <span>⚡</span>
                        <span>Multi-Database Architecture</span>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Isolated tenant databases provisioned instantly with zero cross-tenant contamination.
                    </p>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 space-y-6">
                <!-- Flash Notification Banner -->
                <div v-if="flash?.success" class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 font-bold text-xs flex items-center justify-between">
                    <span>✓ {{ flash.success }}</span>
                </div>
                <div v-if="flash?.error" class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 font-bold text-xs flex items-center justify-between">
                    <span>✕ {{ flash.error }}</span>
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
