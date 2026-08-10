<!DOCTYPE html>
<html lang="ar" dir="rtl" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'نظام إدارة الفواتير والمخزون' }} | سرور POS</title>
    
    <!-- Google Fonts: Cairo & Tajawal -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- PWA Settings -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="سرور POS">

    <!-- Tailwind CSS CDN for instant full styling -->
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
        }
    </script>
    
    <style>
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
        }
        [x-cloak] { display: none !important; }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
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
<body class="h-full bg-slate-950 text-slate-100 flex overflow-hidden selection:bg-amber-500 selection:text-white" x-data="{ sidebarOpen: false }">

    <!-- 🌟 Top Page Loading Progress Bar -->
    <div id="top-loading-bar"></div>

    <!-- ⚡ Floating Livewire Action Loading Badge -->
    <div wire:loading class="fixed bottom-5 left-5 z-[99999] bg-slate-900/95 border border-amber-500/50 text-amber-300 px-4 py-2.5 rounded-2xl shadow-2xl shadow-black/90 flex items-center gap-3 text-xs font-bold font-tajawal backdrop-blur-md">
        <svg class="animate-spin h-4 w-4 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>جاري التحميل والمعالجة...</span>
    </div>

    @auth
        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'" class="fixed lg:static inset-y-0 right-0 z-50 w-72 bg-slate-900 border-l border-slate-800 flex flex-col transition-transform duration-300 ease-in-out">
            <!-- Brand Header -->
            <div class="h-16 px-6 flex items-center justify-between border-b border-slate-800/80 bg-slate-900/50 backdrop-blur-md">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-600 to-amber-400 flex items-center justify-center shadow-lg shadow-amber-500/20 font-black text-xl text-white">
                        ☕
                    </div>
                    <div>
                        <h1 class="font-extrabold text-base tracking-tight text-white flex items-center gap-1.5 font-tajawal">
                            سرور POS
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">V1.0</span>
                        </h1>
                        <p class="text-xs text-slate-400">إدارة الفواتير والمخزون</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
                    ✕
                </button>
            </div>

            <!-- Quick POS Button (Admin & Cashier) -->
            @hasanyrole('admin|cashier')
            <div class="p-4 border-b border-slate-800/60">
                <a href="{{ route('invoices.create') }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-amber-600 via-amber-500 to-amber-600 hover:from-amber-500 hover:to-amber-500 text-white font-bold rounded-xl shadow-lg shadow-amber-600/30 transition-all duration-200 active:scale-95 group font-tajawal">
                    <svg class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>فاتورة بيع جديدة (F2)</span>
                </a>
            </div>
            @endhasanyrole

            <!-- Nav Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>لوحة التحكم (Dashboard)</span>
                </a>

                <!-- المبيعات والفواتير (Admin, Cashier, Accountant) -->
                @hasanyrole('admin|cashier|accountant')
                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">المبيعات والفواتير</div>

                <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('invoices.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>فواتير المبيعات</span>
                </a>

                <a href="{{ route('daily.journal') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('daily.journal') || request()->routeIs('shifts.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>📅 اليومية وحركة الدرج</span>
                </a>

                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('customers.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>العملاء والحسابات</span>
                </a>
                @endhasanyrole

                <!-- المشتريات والمخزون (Admin, Storekeeper, Accountant, Cashier) -->
                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">المشتريات والمخزون</div>

                <a href="{{ route('items.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('items.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>الأصناف والمخزون</span>
                </a>

                @hasanyrole('admin|storekeeper|accountant')
                <a href="{{ route('purchases.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('purchases.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>فواتير المشتريات</span>
                </a>

                <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('suppliers.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>الموردون</span>
                </a>
                @endhasanyrole

                <!-- المرتجعات والمصروفات والتقارير -->
                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">المرتجعات والمصروفات والتقارير</div>

                <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('expenses.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>المصروفات والنثريات</span>
                </a>

                <a href="{{ route('returns.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('returns.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>سجل المرتجعات</span>
                </a>

                <!-- تقارير الأرباح (Admin & Accountant Only) -->
                @hasanyrole('admin|accountant')
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('reports.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>التقارير المالية والأرباح</span>
                </a>
                @endhasanyrole

                <!-- إدارة النظام والمستخدمين (Admin Only) -->
                <div class="pt-3 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">إدارة النظام والمستخدمين</div>

                @hasrole('admin')
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('users.*') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>المستخدمون والكاشير</span>
                </a>
                @endhasrole

                <a href="{{ route('profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('profile') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>الملف الشخصي والأمان</span>
                </a>
            </nav>

            <!-- User Info & Logout -->
            <div class="p-4 border-t border-slate-800/80 bg-slate-900/60 flex items-center justify-between">
                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 min-w-0 hover:opacity-80 transition-opacity">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 border border-amber-500/40 text-amber-300 flex items-center justify-center font-bold text-xs shrink-0">
                        {{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-200 truncate">{{ auth()->user()->name ?? 'مستخدم' }}</p>
                        <p class="text-[10px] text-amber-400/80 truncate">
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
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors cursor-pointer" title="تسجيل الخروج">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top App Bar -->
            <header class="h-16 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex items-center justify-between px-4 lg:px-8 shrink-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div class="text-sm font-semibold text-slate-300 hidden sm:flex items-center gap-2">
                        <span>📅 {{ now()->translatedFormat('l, d F Y') }}</span>
                        <span class="text-slate-600">|</span>
                        <span class="text-amber-400 font-mono" x-data="{ time: new Date().toLocaleTimeString('ar-EG') }" x-init="setInterval(() => time = new Date().toLocaleTimeString('ar-EG'), 1000)" x-text="time"></span>
                    </div>
                </div>

                <!-- Header Actions -->
                <div class="flex items-center gap-3" x-data="{ userMenuOpen: false }">
                    <div class="relative">
                        <button
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 border border-slate-700/60 text-xs transition-colors cursor-pointer"
                        >
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="font-bold text-slate-200">{{ auth()->user()->name ?? 'المدير العام' }}</span>
                            <span class="text-slate-400 text-[10px]">▼</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            x-show="userMenuOpen"
                            @click.away="userMenuOpen = false"
                            x-cloak
                            class="absolute left-0 mt-2 w-48 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl py-2 z-50 divide-y divide-slate-800/80 font-sans"
                        >
                            <div class="px-4 py-2 text-xs">
                                <p class="font-bold text-white">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono" dir="ltr">{{ auth()->user()->phone ?? auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                                    <span>⚙️</span>
                                    <span>الملف الشخصي والأمان</span>
                                </a>
                                @hasrole('admin')
                                <a href="{{ route('users.index') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                                    <span>👥</span>
                                    <span>إدارة المستخدمين والصلاحيات</span>
                                </a>
                                @endhasrole
                            </div>
                            <div class="pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-xs text-rose-400 hover:bg-rose-500/10 transition-colors cursor-pointer text-right">
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
            <main class="flex-1 overflow-y-auto bg-slate-950 p-4 lg:p-6">
                {{ $slot }}
            </main>
        </div>
    @else
        <!-- Guest View (Full screen Login) -->
        <main class="flex-1 min-h-screen">
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
            background: '#0f172a',
            color: '#f8fafc',
            customClass: {
                popup: 'border border-slate-700 shadow-2xl rounded-2xl font-sans'
            },
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

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
                background: '#0f172a',
                color: '#f8fafc',
                customClass: {
                    popup: 'border border-slate-700 shadow-2xl rounded-2xl font-sans'
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

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.log('SW Registration error:', err);
                });
            });
        }
    </script>
    @livewireScripts
</body>
</html>
