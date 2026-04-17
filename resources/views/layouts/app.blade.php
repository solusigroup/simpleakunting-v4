<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
        darkMode: localStorage.getItem('darkMode') !== 'false',
        sidebarOpen: true
      }" 
      :class="darkMode ? 'dark' : ''" 
      x-init="
        $watch('darkMode', val => localStorage.setItem('darkMode', val));
      ">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'Simple Akunting') }}</title>

    <!-- PWA -->
    <meta name="theme-color" content="#e86c25">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/pwa-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Simple Akunting">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Dark mode: prevent flash of light theme -->
    <script>
      if (localStorage.getItem('darkMode') !== 'false') {
        document.documentElement.classList.add('dark');
      }
    </script>

    <!-- Scripts (includes Alpine.js with Collapse plugin) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Light mode overrides */
        html:not(.dark) body { background-color: #f6f8f7; color: #1a2e22; }
        html:not(.dark) .bg-background-dark { background-color: #ffffff; }
        html:not(.dark) .bg-surface-dark { background-color: #f0f4f2; }
        html:not(.dark) .bg-surface-dark\/30 { background-color: rgba(240, 244, 242, 0.8); }
        html:not(.dark) .bg-surface-highlight { background-color: #e5ede8; }
        html:not(.dark) .border-border-dark { border-color: #d1e0d7; }
        html:not(.dark) .text-white { color: #1a2e22; }
        html:not(.dark) .text-text-muted { color: #5a7d68; }
        
        /* Dropdown option text fix */
        html:not(.dark) select option { background-color: #ffffff; color: #1a2e22; }
        html.dark select option { background-color: #1a2e22; color: #ffffff; }
    </style>
</head>
<body class="font-body antialiased bg-background-dark dark:bg-background-dark text-white min-h-screen">
    <div class="flex min-h-screen">

        {{-- ============================================ --}}
        {{-- DESKTOP SIDEBAR (lg+): in flex flow, sticky  --}}
        {{-- ============================================ --}}
        <aside class="hidden lg:block sticky top-0 h-screen flex-shrink-0 overflow-hidden border-r border-border-dark bg-background-dark"
               style="transition: width 0.3s ease;"
               :style="{ width: sidebarOpen ? '18rem' : '0px' }">
            <div style="width: 18rem;" class="h-full flex flex-col">
                @include('layouts.partials.sidebar-content')
            </div>
        </aside>

        {{-- ============================================ --}}
        {{-- MOBILE SIDEBAR (<lg): fixed overlay          --}}
        {{-- ============================================ --}}
        {{-- Backdrop --}}
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition.opacity
             class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-cloak></div>
        {{-- Sidebar drawer --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-72 h-screen bg-background-dark border-r border-border-dark transition-transform duration-300 lg:hidden"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="w-72 h-full flex flex-col">
                @include('layouts.partials.sidebar-content')
            </div>
        </aside>

        {{-- ============================================ --}}
        {{-- MAIN CONTENT                                 --}}
        {{-- ============================================ --}}
        <main class="flex-1 min-w-0 min-h-screen overflow-x-hidden">
            <!-- Header with toggle -->
            <header class="sticky top-0 z-10 bg-background-dark/95 backdrop-blur border-b border-border-dark">
                <div class="px-4 sm:px-8 py-4">
                    <div class="flex items-center gap-4">
                        <!-- Sidebar Toggle (always visible) -->
                        <button @click="sidebarOpen = !sidebarOpen" 
                                class="text-white hover:text-primary transition flex-shrink-0"
                                :title="sidebarOpen ? 'Tutup Sidebar' : 'Buka Sidebar'">
                            <span class="material-symbols-outlined text-xl" x-text="sidebarOpen ? 'menu_open' : 'menu'"></span>
                        </button>
                        
                        @if(isset($header))
                        <div class="flex-1">
                            {{ $header }}
                        </div>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 sm:p-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Searchable Select Utility -->
    <script src="{{ asset('js/searchable-select.js') }}"></script>
    
    @stack('scripts')

    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('PWA Service Worker registered', reg))
                    .catch(err => console.log('PWA Service Worker failed', err));
            });
        }
    </script>
</body>
</html>

