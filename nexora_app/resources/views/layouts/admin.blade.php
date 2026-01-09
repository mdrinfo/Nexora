<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nexora Admin</title>
    <meta name="theme-color" content="#111827">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/turbolinks/5.2.0/turbolinks.js"></script>
    <script>Turbolinks.start();</script>
    <script>
        (function() {
            var t = localStorage.getItem('theme');
            if (!t) {
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                t = prefersDark ? 'dark' : 'light';
                localStorage.setItem('theme', t);
            }
            if (t === 'dark') document.documentElement.classList.add('dark');
        })();
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function(){});
            });
        }
    </script>
</head>
<body class="bg-white dark:bg-gray-900">
    <header class="bg-gray-900 text-white dark:bg-gray-800 shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6">
                    @php
                        $settings = \App\Models\Setting::first();
                        $logo = ($settings && $settings->logo_path) ? asset($settings->logo_path) : null;
                        $brand = ($settings && $settings->brand_name) ? $settings->brand_name : 'Nexora';
                    @endphp

                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
                        @if($logo)
                            <img src="{{ $logo }}" alt="{{ $brand }}" class="h-10 w-auto object-contain">
                        @else
                            <div class="bg-blue-600 text-white p-2 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        @endif
                        <h1 class="text-xl font-bold tracking-tight">{{ $brand }}</h1>
                    </a>
                    
                    <div class="hidden md:block h-8 w-px bg-gray-700"></div>
                    
                    <!-- Live Clock -->
                    <div class="hidden md:flex flex-col justify-center" id="live-clock">
                        <span class="text-lg font-bold leading-none tracking-wide font-mono" id="clock-time">--:--</span>
                        <span class="text-xs text-gray-400 uppercase tracking-wider mt-0.5" id="clock-date">-- --- ----</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button id="toggleTheme" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 transition-colors border border-gray-700">
                        <!-- Sun Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Moon Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <div class="flex">
        <aside class="w-64 border-r border-gray-200 bg-white dark:bg-gray-900 dark:border-gray-700 min-h-screen p-4">
            @include('layouts.partials.sidebar')
        </aside>
        <main class="flex-1 max-w-6xl mx-auto p-6 text-gray-900 dark:text-gray-100">
            @yield('content')
        </main>
    </div>
    <script>
        document.getElementById('toggleTheme').addEventListener('click', function() {
            var isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });

        // Live Clock
        function updateClock() {
            const now = new Date();
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            
            const timeEl = document.getElementById('clock-time');
            const dateEl = document.getElementById('clock-date');
            
            if(timeEl && dateEl) {
                timeEl.textContent = now.toLocaleTimeString('fr-FR', timeOptions);
                dateEl.textContent = now.toLocaleDateString('fr-FR', dateOptions);
            }
        }
        
        if(document.getElementById('live-clock')) {
            updateClock();
            setInterval(updateClock, 1000);
        }
    </script>
</body>
</html>
