<script setup>
import { ref, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import SplashScreen from '@/Components/SplashScreen.vue';
import SideMenu from '@/Components/SideMenu.vue';
import BranchSwitcherModal from '@/Components/BranchSwitcherModal.vue';
import UpdateRequiredModal from '@/Components/UpdateRequiredModal.vue';
import { can } from '@/Utils/permissions';
import { haptic } from '@/Utils/haptics';

const page = usePage();
const isDark = ref(true);
const isNavigating = ref(false);
const isSideMenuOpen = ref(false);
const isBranchModalOpen = ref(false);

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark');
});

router.on('start', () => {
    isNavigating.value = true;
    try { haptic.selection(); } catch (e) {}
});

router.on('finish', () => {
    isNavigating.value = false;
});

const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

const logout = () => {
    router.post('/logout');
};

const user = page.props.auth?.user;
const store = page.props.auth?.store;
const isAuth = page.props.auth?.check;
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col justify-between select-none">
        
        <!-- Top Glowing Dynamic Progress Bar on Page Navigation -->
        <div
            v-if="isNavigating"
            class="fixed top-0 inset-x-0 h-1 z-[9999] bg-gradient-to-r from-emerald-400 via-amber-400 to-emerald-500 shadow-md shadow-emerald-500/50 animate-pulse"
        ></div>

        <!-- Centered Modern Screen Loader (Doesn't overlap header) -->
        <div
            v-if="isNavigating"
            class="fixed inset-0 z-50 pointer-events-none flex items-center justify-center p-4 backdrop-blur-xs bg-slate-950/15 transition duration-150"
        >
            <div class="bg-slate-900/95 text-white border border-emerald-500/30 px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3">
                <div class="w-4 h-4 border-2 border-emerald-400 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs font-black text-emerald-400">{{ $t('common.loading') }}...</span>
            </div>
        </div>

        <!-- Animated Mobile Splash Screen (Only on first cold launch) -->
        <SplashScreen />

        <!-- Side Navigation Drawer -->
        <SideMenu
            :isOpen="isSideMenuOpen"
            :isDark="isDark"
            @close="isSideMenuOpen = false"
            @toggleTheme="toggleTheme"
            @openBranchSwitcher="isBranchModalOpen = true"
        />

        <!-- Branch Switcher Modal -->
        <BranchSwitcherModal
            :isOpen="isBranchModalOpen"
            @close="isBranchModalOpen = false"
        />

        <!-- Sticky Header with Active Branch Bar -->
        <header v-if="isAuth" class="sticky top-0 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-b border-slate-200 dark:border-slate-800 px-4 py-2.5 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <!-- Side Menu Hamburger Button -->
                <button
                    @click="isSideMenuOpen = true"
                    type="button"
                    class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition touch-active"
                    :title="$t('nav.main_menu')"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <!-- Active Branch Selector Chip -->
                <button
                    @click="isBranchModalOpen = true"
                    type="button"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-500/20 transition touch-active text-xs font-bold"
                >
                    <span class="text-xs">🏬</span>
                    <span class="truncate max-w-[140px]">{{ store?.name || $t('common.main_store_default') }}</span>
                    <span class="text-[10px] text-emerald-500">▼</span>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <!-- Theme Toggle Button -->
                <button @click="toggleTheme" type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition touch-active">
                    <span v-if="isDark" class="text-amber-400 text-sm">☀️</span>
                    <span v-else class="text-slate-700 text-sm">🌙</span>
                </button>
            </div>
        </header>

        <!-- Main Content Area -->
        <main
            class="flex-1 w-full max-w-md mx-auto px-4 py-4 pb-24 overflow-y-auto transition-opacity duration-150"
            :class="isNavigating ? 'opacity-50 pointer-events-none' : 'opacity-100'"
        >
            <!-- In-App Auto-Updater Banner & Modal Lock -->
            <UpdateRequiredModal />

            <!-- Flash Message Toast Banner -->
            <div v-if="page.props.flash?.success" class="mb-3 p-3 rounded-2xl bg-emerald-600 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-emerald-600/20">
                <span>✅</span>
                <span>{{ page.props.flash.success }}</span>
            </div>
            <div v-if="page.props.flash?.error" class="mb-3 p-3 rounded-2xl bg-rose-600 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-rose-600/20">
                <span>❌</span>
                <span>{{ page.props.flash.error }}</span>
            </div>

            <slot />
        </main>

        <!-- Bottom Navigation Bar (Dynamic 5 Items based on Permissions) -->
        <nav v-if="isAuth" class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-t border-slate-200 dark:border-slate-800 safe-pb shadow-lg max-w-md mx-auto">
            <div class="grid grid-cols-5 h-15 items-center">
                <!-- 1. Dashboard -->
                <Link href="/" prefetch class="flex flex-col items-center justify-center py-1 transition touch-active" :class="page.url === '/' ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                    <span class="text-base mb-0.5">📊</span>
                    <span class="text-[10px]">{{ $t('nav.dashboard') }}</span>
                </Link>

                <!-- 2. POS Cashier or Items -->
                <Link v-if="can('pos.access')" href="/pos" prefetch class="flex flex-col items-center justify-center py-1 transition touch-active" :class="page.url.startsWith('/pos') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                    <span class="text-base mb-0.5">⚡</span>
                    <span class="text-[10px]">{{ $t('nav.pos') }}</span>
                </Link>
                <Link v-else-if="can('items.view')" href="/items" prefetch class="flex flex-col items-center justify-center py-1 transition touch-active" :class="page.url.startsWith('/items') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                    <span class="text-base mb-0.5">☕</span>
                    <span class="text-[10px]">{{ $t('nav.inventory') }}</span>
                </Link>
                <Link v-else href="/purchases" prefetch class="flex flex-col items-center justify-center py-1 transition touch-active" :class="page.url.startsWith('/purchases') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                    <span class="text-base mb-0.5">📦</span>
                    <span class="text-[10px]">{{ $t('nav.purchases') }}</span>
                </Link>

                <!-- 3. Invoices or Shifts -->
                <Link v-if="can('invoices.view')" href="/invoices" prefetch class="flex flex-col items-center justify-center py-1 transition touch-active" :class="page.url.startsWith('/invoices') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                    <span class="text-base mb-0.5">🧾</span>
                    <span class="text-[10px]">{{ $t('nav.invoices') }}</span>
                </Link>
                <Link v-else-if="can('daily_journal.view')" href="/shifts" prefetch class="flex flex-col items-center justify-center py-1 transition touch-active" :class="page.url.startsWith('/shifts') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                    <span class="text-base mb-0.5">🔐</span>
                    <span class="text-[10px]">{{ $t('nav.shifts') }}</span>
                </Link>
                <Link v-else href="/customers" prefetch class="flex flex-col items-center justify-center py-1 transition touch-active" :class="page.url.startsWith('/customers') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                    <span class="text-base mb-0.5">👥</span>
                    <span class="text-[10px]">{{ $t('nav.customers') }}</span>
                </Link>

                <!-- 4. Treasury or Reports -->
                <Link v-if="can('reports.view')" href="/reports" prefetch class="flex flex-col items-center justify-center py-1 transition touch-active" :class="page.url.startsWith('/reports') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                    <span class="text-base mb-0.5">📈</span>
                    <span class="text-[10px]">{{ $t('nav.reports') }}</span>
                </Link>
                <Link v-else-if="can('daily_journal.view')" href="/treasury" prefetch class="flex flex-col items-center justify-center py-1 transition touch-active" :class="page.url.startsWith('/treasury') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                    <span class="text-base mb-0.5">🏦</span>
                    <span class="text-[10px]">{{ $t('nav.treasury') }}</span>
                </Link>
                <Link v-else href="/suppliers" prefetch class="flex flex-col items-center justify-center py-1 transition touch-active" :class="page.url.startsWith('/suppliers') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'">
                    <span class="text-base mb-0.5">🏭</span>
                    <span class="text-[10px]">{{ $t('nav.suppliers') }}</span>
                </Link>

                <!-- 5. Menu Drawer Trigger -->
                <button @click="isSideMenuOpen = true" type="button" class="flex flex-col items-center justify-center py-1 transition text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 touch-active">
                    <span class="text-base mb-0.5">☰</span>
                    <span class="text-[10px]">{{ $t('nav.more') }}</span>
                </button>
            </div>
        </nav>
    </div>
</template>
