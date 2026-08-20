<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#10b981">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <title>{{ $title ?? 'سرور كوفي ERP Mobile' }}</title>

    <!-- Theme Initialization (Dark Mode by Default / Local Storage) -->
    <script>
        (function() {
            try {
                const theme = localStorage.getItem('theme') || 'dark';
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Google Fonts: Cairo & Tajawal -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN & Color Configuration (Matching Blade UI Exactly) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'Tajawal', 'sans-serif'],
                        tajawal: ['Tajawal', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        },
                        amber: {
                            50: '#fffbeb',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                        },
                        dark: {
                            800: '#1e293b',
                            850: '#172033',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        };
    </script>

    <!-- Alpine.js & Livewire Styles -->
    @livewireStyles

    <style>
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
            touch-action: manipulation;
        }
        [x-cloak] { display: none !important; }

        /* Custom Mobile Scrollbars */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        html.dark ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        html.dark ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        html:not(.dark) ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        html:not(.dark) ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        /* 🚀 Glow Top Loading Bar */
        #top-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            z-index: 999999;
            pointer-events: none;
            width: 0%;
            background: linear-gradient(90deg, #d97706, #f59e0b, #10b981, #d97706);
            background-size: 200% 100%;
            transition: width 0.25s ease, opacity 0.3s ease;
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.7);
        }
        #top-loading-bar.active {
            opacity: 1;
            animation: progressGlow 1.2s ease infinite;
        }
        @keyframes progressGlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Safe Area Padding for Modern Mobile Devices (iOS Notch & Android Nav Bar) */
        .safe-pb {
            padding-bottom: max(1rem, env(safe-area-inset-bottom));
        }
        .safe-pt {
            padding-top: max(0.5rem, env(safe-area-inset-top));
        }
    </style>
</head>
<body class="h-full bg-slate-50 dark:bg-dark-950 text-slate-900 dark:text-slate-100 antialiased flex flex-col justify-between select-none">

    <!-- Top Loading Bar -->
    <div id="top-loading-bar"></div>

    <!-- Header (Sticky Mobile Top Bar) -->
    @php
        $user = \App\Services\ApiService::getUser();
        $store = \App\Services\ApiService::getStore();
    @endphp
    @if(\App\Services\ApiService::isAuthenticated())
    <header class="sticky top-0 z-40 bg-white/90 dark:bg-dark-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-4 py-2.5 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center text-white font-extrabold shadow-sm text-base">
                ☕
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">سرور كوفي ERP</h1>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ $user['name'] ?? 'المستخدم' }}</span>
                    @if($store)
                        <span class="text-slate-400 dark:text-slate-600">|</span>
                        <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ $store['name'] }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Theme Toggle Button -->
            <button onclick="toggleTheme()" type="button" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-dark-800 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                <span class="hidden dark:inline text-amber-400 text-base">☀️</span>
                <span class="inline dark:hidden text-slate-700 text-base">🌙</span>
            </button>

            <!-- Logout Link -->
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center hover:bg-rose-100 transition" title="تسجيل الخروج">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </header>
    @endif

    <!-- Main Content Body -->
    <main class="flex-1 w-full max-w-md mx-auto px-4 py-4 pb-24 overflow-y-auto">
        {{ $slot }}
    </main>

    <!-- Bottom Navigation Bar (Fixed for Mobile Screens) -->
    @if(\App\Services\ApiService::isAuthenticated())
    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-dark-900/95 backdrop-blur-lg border-t border-slate-200 dark:border-slate-800 safe-pb shadow-lg max-w-md mx-auto">
        <div class="grid grid-cols-4 h-15 items-center">
            <!-- 1. Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('dashboard') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[11px]">الرئيسية</span>
            </a>

            <!-- 2. Customers -->
            <a href="{{ route('customers.index') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('customers.*') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span class="text-[11px]">العملاء</span>
            </a>

            <!-- 3. Suppliers -->
            <a href="{{ route('suppliers.index') }}" class="flex flex-col items-center justify-center py-1 {{ request()->routeIs('suppliers.*') ? 'text-emerald-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span class="text-[11px]">الموردين</span>
            </a>

            <!-- 4. Settings / Connection -->
            <a href="{{ route('login') }}" class="flex flex-col items-center justify-center py-1 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="text-[11px]">الاتصال</span>
            </a>
        </div>
    </nav>
    @endif

    <!-- Toast Notification Engine (Alpine.js) -->
    <div x-data="{
            toasts: [],
            add(msg, type = 'success') {
                const id = Date.now();
                this.toasts.push({ id, msg, type });
                setTimeout(() => this.remove(id), 3500);
            },
            remove(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
        }"
        x-on:toast.window="add($event.detail.msg, $event.detail.type)"
        class="fixed top-16 left-4 right-4 z-50 pointer-events-none flex flex-col gap-2 max-w-md mx-auto">
        <template x-for="t in toasts" :key="t.id">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-3 scale-95"
                 :class="{
                     'bg-emerald-600 text-white shadow-emerald-500/20': t.type === 'success',
                     'bg-rose-600 text-white shadow-rose-500/20': t.type === 'error',
                     'bg-amber-600 text-white shadow-amber-500/20': t.type === 'warning',
                     'bg-sky-600 text-white shadow-sky-500/20': t.type === 'info'
                 }"
                 class="px-4 py-3 rounded-2xl shadow-xl flex items-center gap-2.5 text-sm font-semibold pointer-events-auto border border-white/10">
                <span x-text="t.type === 'success' ? '✅' : (t.type === 'error' ? '❌' : '⚠️')"></span>
                <span class="flex-1" x-text="t.msg"></span>
                <button @click="remove(t.id)" class="opacity-70 hover:opacity-100 text-xs">✕</button>
            </div>
        </template>
    </div>

    <!-- Theme Switcher Script -->
    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }

        // Livewire Loading Bar Hook
        document.addEventListener('livewire:navigating', () => {
            const bar = document.getElementById('top-loading-bar');
            if (bar) {
                bar.style.width = '35%';
                bar.classList.add('active');
            }
        });
        document.addEventListener('livewire:navigated', () => {
            const bar = document.getElementById('top-loading-bar');
            if (bar) {
                bar.style.width = '100%';
                setTimeout(() => {
                    bar.style.width = '0%';
                    bar.classList.remove('active');
                }, 300);
            }
        });
    </script>

    @livewireScripts
</body>
</html>
