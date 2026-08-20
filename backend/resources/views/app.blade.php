<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'مخزني ERP') }}</title>

    <!-- Early Anti-Flicker Theme Script -->
    <script>
        (function() {
            try {
                const storedTheme = localStorage.getItem('theme_preference') || 'dark';
                if (storedTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                } else {
                    document.documentElement.classList.add('light');
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Google Arabic Fonts (Cairo & Tajawal) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    <!-- Vite Scripts & Inertia -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead

    <style>
        * {
            font-family: 'Cairo', 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        body {
            font-family: 'Cairo', 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100 antialiased selection:bg-amber-500 selection:text-white min-h-screen overflow-x-hidden font-sans transition-colors duration-200">
    @inertia
</body>
</html>
