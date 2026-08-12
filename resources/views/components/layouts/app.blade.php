<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $siteCompanyName = \App\Models\Setting::get('company_name', 'سرور كوفي');
        $siteSubtitle = \App\Models\Setting::get('company_subtitle', 'لتوريدات خامات مطاحن البن');
    @endphp
    <title>{{ $title ?? $siteCompanyName }} | {{ $siteCompanyName }} - {{ $siteSubtitle }}</title>
    
    <!-- Theme Early Initialization (Zero-lag / Anti-flicker) -->
    <script>
        (function() {
            try {
                const localPref = localStorage.getItem('theme');
                const userPref = "{{ auth()->user()->theme_preference ?? '' }}";
                const theme = localPref || userPref || 'dark';
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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Favicon & PWA Icons -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="سرور كوفي">

    <!-- Tailwind CSS CDN & Config -->
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
    
    <style>
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        html.dark ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        html:not(.dark) ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        html.dark ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        html:not(.dark) ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        /* 🚀 Top Page Loading Progress Bar Animation */
        #top-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3.5px;
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
    </style>
    @livewireStyles
</head>
<body class="h-full bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 flex overflow-hidden selection:bg-amber-500 selection:text-white" x-data="{ sidebarOpen: false }">

    <!-- 🌟 Top Page Loading Progress Bar -->
    <div id="top-loading-bar"></div>

    <!-- ⚡ Floating Livewire Action Loading Badge -->
    <div wire:loading class="fixed bottom-5 left-5 z-[99999] bg-white/95 dark:bg-slate-900/95 border border-amber-500/50 text-amber-600 dark:text-amber-300 px-4 py-2.5 rounded-2xl shadow-2xl shadow-slate-400/30 dark:shadow-black/90 flex items-center gap-3 text-xs font-bold font-tajawal backdrop-blur-md">
        <svg class="animate-spin h-4 w-4 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>جاري التحميل والمعالجة...</span>
    </div>

    @auth
        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'" class="fixed lg:static inset-y-0 right-0 z-50 w-72 bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 ease-in-out shadow-xl lg:shadow-none">
            <!-- Brand Header -->
            <div class="h-16 px-6 flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-900/50 backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 p-1 flex items-center justify-center shadow-md shadow-amber-500/10 border border-slate-200 dark:border-slate-700 shrink-0">
                        <img src="{{ asset('logo.png') }}" alt="سرور POS" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="font-extrabold text-base tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5 font-tajawal">
                            {{ $siteCompanyName }}
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">V1.0</span>
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[150px]">{{ $siteSubtitle }}</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-700 dark:hover:text-white">
                    ✕
                </button>
            </div>

            <!-- Quick POS Button (pos.access) -->
            @can('pos.access')
            <div class="p-4 border-b border-slate-200 dark:border-slate-800/60">
                <a href="{{ route('invoices.create') }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-500 text-white font-bold rounded-xl shadow-lg shadow-amber-600/30 transition-all duration-200 active:scale-95 group font-tajawal">
                    <svg class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>فاتورة بيع جديدة (F2)</span>
                </a>
            </div>
            @endcan

            <!-- Nav Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>لوحة التحكم (Dashboard)</span>
                </a>

                <!-- المبيعات والفواتير -->
                @if(auth()->user()?->can('invoices.view') || auth()->user()?->can('daily_journal.view') || auth()->user()?->can('customers.manage'))
                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">المبيعات والفواتير</div>

                @can('invoices.view')
                <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('invoices.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>فواتير المبيعات</span>
                </a>
                @endcan

                @can('daily_journal.view')
                <a href="{{ route('daily.journal') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('daily.journal') || request()->routeIs('shifts.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>📅 اليومية وحركة الدرج</span>
                </a>
                @endcan

                @can('customers.manage')
                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('customers.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>العملاء والحسابات</span>
                </a>
                @endcan
                @endif

                <!-- المخزون والفروع والتوزيع -->
                @if(auth()->user()?->can('items.view') || auth()->user()?->can('transfers.view') || auth()->user()?->can('stores.manage') || auth()->user()?->can('purchases.view') || auth()->user()?->can('suppliers.manage'))
                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">المخزون والفروع والتوزيع</div>

                @can('items.view')
                <a href="{{ route('items.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('items.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>الأصناف والأسعار العامة</span>
                </a>

                <a href="{{ route('store-stocks') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('store-stocks') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span>📦 جرد وأسعار الفروع</span>
                </a>
                @endcan

                @can('transfers.view')
                <a href="{{ route('stock-transfers') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('stock-transfers*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    <span>🚚 أذونات التحويل والشحن</span>
                </a>
                @endcan

                @can('stores.manage')
                <a href="{{ route('stores') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('stores') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>🏬 الفروع وعربات التوزيع</span>
                </a>
                @endcan

                @can('purchases.view')
                <a href="{{ route('purchases.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('purchases.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>فواتير المشتريات</span>
                </a>
                @endcan

                @can('suppliers.manage')
                <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('suppliers.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>الموردون</span>
                </a>
                @endcan
                @endif

                <!-- المرتجعات والمصروفات والتقارير -->
                @if(auth()->user()?->can('expenses.manage') || auth()->user()?->can('returns.manage') || auth()->user()?->can('reports.view'))
                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">المرتجعات والمصروفات والتقارير</div>

                @can('expenses.manage')
                <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('expenses.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>المصروفات والنثريات</span>
                </a>
                @endcan

                @can('returns.manage')
                <a href="{{ route('returns.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('returns.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>سجل المرتجعات</span>
                </a>
                @endcan

                @can('reports.view')
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('reports.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>التقارير المالية والأرباح</span>
                </a>
                @endcan
                @endif

                <!-- إدارة النظام والمستخدمين -->
                @if(auth()->user()?->can('roles.manage') || auth()->user()?->can('trash.access') || auth()->user()?->can('logs.view'))
                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">إدارة النظام والمستخدمين</div>

                @can('roles.manage')
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('users.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>المستخدمون والكاشير</span>
                </a>

                <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('roles.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>🛡️ الأدوار ومجموعات الصلاحيات</span>
                </a>
                @endcan

                @can('logs.view')
                <a href="{{ route('activity-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('activity-logs.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <span>📜 سجل العمليات والرقابة</span>
                </a>
                @endcan

                @can('trash.access')
                <a href="{{ route('trash.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('trash.index') ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/40' : 'text-slate-600 dark:text-slate-300 hover:bg-rose-500/10 hover:text-rose-600 dark:hover:text-rose-400' }}">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span class="flex items-center justify-between w-full">
                        <span>🗑️ سلة المحذوفات المركزية</span>
                    </span>
                </a>
                @endcan
                @endif

                <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('profile') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>الملف الشخصي والأمان</span>
                </a>

                <!-- 📲 PWA Mobile Install Button (Permanent) -->
                <div class="pt-2 px-1">
                    <button onclick="triggerPwaInstall()" type="button" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-gradient-to-r from-amber-600/15 via-amber-500/20 to-amber-600/15 hover:from-amber-600/30 hover:to-amber-500/30 text-amber-600 dark:text-amber-400 font-bold rounded-xl text-xs border border-amber-500/30 shadow-sm transition-all cursor-pointer">
                        <span>📲 تثبيت التطبيق (PWA)</span>
                    </button>
                </div>
            </nav>

            <!-- User Info & Logout -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-900/60 flex items-center justify-between">
                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 min-w-0 hover:opacity-80 transition-opacity">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 border border-amber-500/40 text-amber-600 dark:text-amber-300 flex items-center justify-center font-bold text-xs shrink-0">
                        {{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ auth()->user()->name ?? 'مستخدم' }}</p>
                        <p class="text-[10px] text-amber-600 dark:text-amber-400/80 truncate">
                            @if(auth()->user()->hasRole('admin'))
                                👑 المدير العام (Admin)
                            @elseif(auth()->user()->hasRole('cashier'))
                                💼 كاشير (Cashier)
                            @elseif(auth()->user()->hasRole('storekeeper'))
                                📦 أمين مخزن (Store)
                            @elseif(auth()->user()->hasRole('accountant'))
                                📊 محاسب (Accountant)
                            @else
                                مستخدم
                            @endif
                        </p>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 rounded-lg transition-colors cursor-pointer" title="تسجيل الخروج">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top App Bar -->
            <header class="h-16 bg-white/90 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 lg:px-8 shrink-0 z-30 shadow-sm">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div class="text-sm font-semibold text-slate-600 dark:text-slate-300 hidden sm:flex items-center gap-2">
                        <span>📅 {{ now()->translatedFormat('l, d F Y') }}</span>
                        <span class="text-slate-300 dark:text-slate-600">|</span>
                        <span class="text-amber-600 dark:text-amber-400 font-mono" x-data="{ time: new Date().toLocaleTimeString('ar-EG') }" x-init="setInterval(() => time = new Date().toLocaleTimeString('ar-EG'), 1000)" x-text="time"></span>
                    </div>
                </div>

                <!-- Header Actions -->
                <div class="flex items-center gap-2 sm:gap-3" x-data="{ userMenuOpen: false }">
                    
                    @php
                        $currentStore = auth()->user()?->getCurrentStore();
                        $availableStores = auth()->user()?->hasRole('admin') 
                            ? \App\Models\Store::where('is_active', true)->orderBy('is_main', 'desc')->get()
                            : auth()->user()?->stores()->where('is_active', true)->get();
                        if ($availableStores && $availableStores->isEmpty() && $currentStore) {
                            $availableStores = collect([$currentStore]);
                        }
                    @endphp

                    <!-- 🏬 Store / Van Switcher in Header -->
                    @if($currentStore)
                    <div class="relative" x-data="{ storeMenuOpen: false }">
                        <button
                            @click="storeMenuOpen = !storeMenuOpen"
                            type="button"
                            class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-bold text-xs transition-all cursor-pointer shrink-0 max-w-[130px] sm:max-w-none"
                            title="تبديل الفرع أو عربية التوزيع النشطة"
                        >
                            <span>
                                @if($currentStore->type === 'wholesale_van') 🚚 @elseif($currentStore->type === 'main_warehouse') 🏢 @else 🏬 @endif
                            </span>
                            <span class="truncate max-w-[100px] sm:max-w-[140px]">{{ $currentStore->name }}</span>
                            @if($availableStores && $availableStores->count() > 1)
                            <span class="text-[10px] text-emerald-500">▼</span>
                            @endif
                        </button>

                        @if($availableStores && $availableStores->count() > 1)
                        <div
                            x-show="storeMenuOpen"
                            @click.away="storeMenuOpen = false"
                            x-cloak
                            class="absolute left-0 mt-2 w-56 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl py-2 z-50 divide-y divide-slate-100 dark:divide-slate-800/80 font-sans"
                        >
                            <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                الفرع / عربية التوزيع النشطة:
                            </div>
                            <div class="py-1 max-h-60 overflow-y-auto">
                                @foreach($availableStores as $stOption)
                                <button
                                    type="button"
                                    onclick="switchGlobalStore({{ $stOption->id }})"
                                    class="w-full text-right flex items-center justify-between px-3 py-2 text-xs transition-colors cursor-pointer {{ (int)$currentStore->id === (int)$stOption->id ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-black' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                                >
                                    <div class="flex items-center gap-2 truncate">
                                        <span>@if($stOption->type === 'wholesale_van') 🚚 @elseif($stOption->type === 'main_warehouse') 🏢 @else 🏬 @endif</span>
                                        <span class="truncate">{{ $stOption->name }}</span>
                                    </div>
                                    @if((int)$currentStore->id === (int)$stOption->id)
                                    <span class="text-xs text-emerald-600 font-black">✓</span>
                                    @endif
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- 📲 PWA Install Button (Permanent in Top Bar) -->
                    <button
                        type="button"
                        onclick="triggerPwaInstall()"
                        class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all cursor-pointer active:scale-95 shrink-0"
                        title="تثبيت النظام كتطبيق على الهاتف أو الكمبيوتر"
                    >
                        <span class="text-sm">📲</span>
                        <span class="hidden sm:inline font-tajawal">تثبيت التطبيق</span>
                    </button>

                    <!-- ☀️ / 🌙 Theme Toggle Button -->
                    <button
                        type="button"
                        onclick="toggleAppTheme()"
                        class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/90 dark:hover:bg-slate-700/80 border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 transition-all cursor-pointer shadow-sm active:scale-95 shrink-0"
                        title="تبديل الوضع النهاري / الليلي"
                    >
                        <span class="dark:hidden flex items-center gap-1 text-slate-800 font-bold">
                            <span class="text-sm">🌙</span>
                            <span class="hidden sm:inline">الوضع الليلي</span>
                        </span>
                        <span class="hidden dark:flex items-center gap-1 text-amber-400 font-bold">
                            <span class="text-sm">☀️</span>
                            <span class="hidden sm:inline">الوضع النهاري</span>
                        </span>
                    </button>

                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <button
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-1.5 sm:gap-2.5 px-2.5 sm:px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-800 border border-slate-300 dark:border-slate-700/60 text-xs transition-colors cursor-pointer text-slate-800 dark:text-slate-200 max-w-[140px] sm:max-w-none"
                        >
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                            <span class="font-bold truncate">{{ auth()->user()->name ?? 'المدير العام' }}</span>
                            <span class="text-slate-400 text-[10px] shrink-0">▼</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            x-show="userMenuOpen"
                            @click.away="userMenuOpen = false"
                            x-cloak
                            class="absolute left-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl py-2 z-50 divide-y divide-slate-100 dark:divide-slate-800/80 font-sans"
                        >
                            <div class="px-4 py-2 text-xs">
                                <p class="font-bold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono" dir="ltr">{{ auth()->user()->phone ?? auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-colors">
                                    <span>⚙️</span>
                                    <span>الملف الشخصي والأمان</span>
                                </a>
                                @can('roles.manage')
                                <a href="{{ route('users.index') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-colors">
                                    <span>👥</span>
                                    <span>إدارة المستخدمين والصلاحيات</span>
                                </a>
                                <a href="{{ route('roles.index') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-colors">
                                    <span>🛡️</span>
                                    <span>مصفوفة الصلاحيات والأدوار</span>
                                </a>
                                @endcan
                                @can('logs.view')
                                <a href="{{ route('activity-logs.index') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition-colors">
                                    <span>📜</span>
                                    <span>سجل العمليات والرقابة</span>
                                </a>
                                @endcan
                            </div>
                            <div class="pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-rose-500 hover:bg-rose-500/10 transition-colors cursor-pointer text-right font-bold">
                                        <span>🚪</span>
                                        <span>تسجيل الخروج</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dynamic Body Page Content -->
            <main class="flex-1 overflow-y-auto bg-slate-100 dark:bg-slate-950 p-4 lg:p-6 text-slate-800 dark:text-slate-100">
                {{ $slot }}
            </main>
        </div>
    @else
        <!-- Guest View (Full screen Login) -->
        <main class="flex-1 min-h-screen bg-slate-100 dark:bg-slate-950">
            {{ $slot }}
        </main>
    @endauth

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-start',
            showConfirmButton: false,
            timer: 2800,
            timerProgressBar: true,
            background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
            customClass: {
                popup: 'border border-slate-300 dark:border-slate-700 shadow-2xl rounded-2xl font-sans'
            },
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        // 🏬 Global Fast Store Switcher
        window.switchGlobalStore = function(storeId) {
            fetch('{{ route('store.switch') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ store_id: storeId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                }
            })
            .catch(err => console.error('Store switch error:', err));
        };

        // Flash session messages on load
        @if (session()->has('success'))
            document.addEventListener('DOMContentLoaded', () => {
                Toast.fire({
                    icon: 'success',
                    title: '{{ session('success') }}'
                });
            });
        @endif

        @if (session()->has('error'))
            document.addEventListener('DOMContentLoaded', () => {
                Toast.fire({
                    icon: 'error',
                    title: '{{ session('error') }}'
                });
            });
        @endif

        // Dynamic Livewire event listeners
        window.addEventListener('swal:toast', event => {
            const detail = Array.isArray(event.detail) ? event.detail[0] : (event.detail || {});
            Toast.fire({
                icon: detail.icon || detail.type || 'success',
                title: detail.title || detail.message || 'تمت العملية بنجاح'
            });
        });

        window.addEventListener('swal:alert', event => {
            const detail = Array.isArray(event.detail) ? event.detail[0] : (event.detail || {});
            Swal.fire({
                icon: detail.icon || detail.type || 'info',
                title: detail.title || 'إشعار',
                text: detail.text || detail.message || '',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#d97706',
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                customClass: {
                    popup: 'border border-slate-300 dark:border-slate-700 shadow-2xl rounded-2xl font-sans'
                }
            });
        });

        // ⌨️ Global Keyboard Shortcuts
        document.addEventListener('keydown', (e) => {
            // F2: Open New Invoice (POS) from any screen
            if (e.key === 'F2') {
                e.preventDefault();
                if (window.location.pathname !== '/invoices/create') {
                    window.location.href = "{{ route('invoices.create') }}";
                } else {
                    const searchInput = document.querySelector('input[placeholder*="ابحث"]');
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }
                }
            }
        });

        // ☀️ / 🌙 Theme Toggle Controller (Instant + Backend Persistence)
        window.toggleAppTheme = function() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            const newTheme = isDark ? 'light' : 'dark';

            if (newTheme === 'dark') {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }

            try {
                localStorage.setItem('theme', newTheme);
            } catch(e) {}

            // Save to logged-in user in database
            fetch("{{ route('theme.toggle') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ theme: newTheme })
            }).catch(e => console.log('Theme sync error', e));
        };

        function applyStoredTheme() {
            try {
                const stored = localStorage.getItem('theme');
                if (stored === 'dark') {
                    document.documentElement.classList.add('dark');
                } else if (stored === 'light') {
                    document.documentElement.classList.remove('dark');
                }
            } catch(e) {}
        }

        window.addEventListener('livewire:navigated', applyStoredTheme);

        window.addEventListener('theme-changed', event => {
            const theme = typeof event.detail === 'string' ? event.detail : (event.detail?.[0] || 'dark');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            try {
                localStorage.setItem('theme', theme);
            } catch(e) {}
        });

        // 🚀 Smooth Top Progress Bar Controller
        const topLoader = document.getElementById('top-loading-bar');
        let loaderTimer;

        function startTopLoader() {
            if (!topLoader) return;
            topLoader.classList.add('active');
            topLoader.style.width = '30%';
            topLoader.style.opacity = '1';
            clearTimeout(loaderTimer);
            loaderTimer = setTimeout(() => {
                topLoader.style.width = '75%';
            }, 180);
        }

        function finishTopLoader() {
            if (!topLoader) return;
            topLoader.style.width = '100%';
            setTimeout(() => {
                topLoader.style.opacity = '0';
                setTimeout(() => {
                    topLoader.classList.remove('active');
                    topLoader.style.width = '0%';
                }, 300);
            }, 150);
        }

        // Hook to page navigation and Livewire commits
        window.addEventListener('beforeunload', () => startTopLoader());

        document.addEventListener('livewire:navigating', () => startTopLoader());
        document.addEventListener('livewire:navigated', () => finishTopLoader());

        document.addEventListener('livewire:init', () => {
            if (window.Livewire) {
                Livewire.hook('commit', ({ succeed, fail }) => {
                    startTopLoader();
                    succeed(() => finishTopLoader());
                    fail(() => finishTopLoader());
                });
            }
        });
    </script>

    <!-- 📲 PWA Install Guide Modal -->
    <div id="pwa-guide-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-md p-6 space-y-4 shadow-2xl relative">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" class="w-10 h-10 object-contain rounded-xl p-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700" alt="لوجو">
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white text-base font-tajawal">تثبيت تطبيق سرور كوفي</h3>
                        <p class="text-xs text-slate-500">على الشاشة الرئيسية للهاتف أو الكمبيوتر</p>
                    </div>
                </div>
                <button onclick="closePwaModal()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-xl font-bold p-1">✕</button>
            </div>

            <div class="space-y-3 text-xs text-slate-700 dark:text-slate-300">
                <div class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 space-y-1">
                    <p class="font-bold text-amber-800 dark:text-amber-300 text-sm">📱 لهواتف الأندرويد (Chrome):</p>
                    <p>انقر على زر <strong>"تثبيت التطبيق الآن"</strong> بالأسفل، أو اضغط على قائمة المتصفح (⋮) ثم اختر <strong>"تثبيت التطبيق"</strong> أو <strong>"إضافة إلى الشاشة الرئيسية"</strong>.</p>
                </div>

                <div class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-1">
                    <p class="font-bold text-slate-900 dark:text-white text-sm">🍏 لهواتف الآيفون (Safari):</p>
                    <p>1. اضغط على زر المشاركة بالأسفل <strong>(⎋ Share)</strong> في شريط متصفح سفاري.</p>
                    <p>2. مرر للأسفل واختر <strong>"إضافة إلى الشاشة الرئيسية ➕ (Add to Home Screen)"</strong>.</p>
                </div>

                <div class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-1">
                    <p class="font-bold text-slate-900 dark:text-white text-sm">💻 لأجهزة الكمبيوتر (Chrome / Edge):</p>
                    <p>اضغط على أيقونة التثبيت <strong>(⊕ أو 🖥️)</strong> الموجودة في نهاية شريط العنوان بالمتصفح لتثبيته كتطبيق مستقل.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button onclick="closePwaModal()" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">إغلاق</button>
                <button id="pwa-prompt-btn" onclick="executeNativeInstall()" class="px-5 py-2.5 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-bold rounded-xl text-xs shadow-lg shadow-amber-500/20 cursor-pointer">📲 تثبيت التطبيق الآن</button>
            </div>
        </div>
    </div>

    <script>
        // 📲 PWA Service Worker & Install Prompt Controller
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            console.log('PWA beforeinstallprompt ready');
        });

        window.triggerPwaInstall = function() {
            if (deferredPrompt) {
                window.executeNativeInstall();
            } else {
                const modal = document.getElementById('pwa-guide-modal');
                if (modal) {
                    modal.style.display = 'flex';
                    modal.classList.remove('hidden');
                }
            }
        };

        window.executeNativeInstall = async function() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                window.closePwaModal();
            } else {
                const modal = document.getElementById('pwa-guide-modal');
                if (modal) {
                    modal.style.display = 'flex';
                    modal.classList.remove('hidden');
                }
            }
        };

        window.closePwaModal = function() {
            const modal = document.getElementById('pwa-guide-modal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
        };

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset("sw.js") }}').then(reg => {
                    console.log('SW Registered successfully:', reg.scope);
                }).catch(err => {
                    console.log('SW Registration error:', err);
                });
            });
        }
    </script>
    @livewireScripts
</body>
</html>
