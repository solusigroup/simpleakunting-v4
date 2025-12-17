<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
        darkMode: localStorage.getItem('darkMode') !== 'false',
        sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
        sidebarMinimized: localStorage.getItem('sidebarMinimized') === 'true',
        isMobile: window.innerWidth < 1024
      }" 
      :class="darkMode ? 'dark' : ''" 
      x-init="
        $watch('darkMode', val => localStorage.setItem('darkMode', val));
        $watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val));
        $watch('sidebarMinimized', val => localStorage.setItem('sidebarMinimized', val));
        window.addEventListener('resize', () => {
          isMobile = window.innerWidth < 1024;
        });
      ">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'Simple Akunting') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

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
        
        /* Dropdown option text fix - Light mode */
        html:not(.dark) select option {
            background-color: #ffffff;
            color: #1a2e22;
        }
        
        /* Dropdown option text fix - Dark mode */
        html.dark select option {
            background-color: #1a2e22;
            color: #ffffff;
        }
        
        /* Sidebar transitions */
        aside {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Hide text in minimized sidebar on desktop */
        @media (min-width: 1024px) {
            aside[data-minimized="true"] .sidebar-text,
            aside[data-minimized="true"] .sidebar-group-label {
                display: none;
            }
            
            /* Center icons when minimized */
            aside[data-minimized="true"] a,
            aside[data-minimized="true"] button {
                justify-content: center;
            }
        }
    </style>
</head>
<body class="font-body antialiased bg-background-dark dark:bg-background-dark text-white min-h-screen">
    <div class="flex min-h-screen">
        <!-- Mobile Overlay (behind sidebar) -->
        <div x-show="sidebarOpen && isMobile" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-cloak></div>
        
        <!-- Sidebar: Fixed on mobile, Push on desktop -->
        <aside :class="[
                   sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                   sidebarOpen ? 'w-72' : 'lg:w-0'
               ]"
               class="fixed lg:relative h-screen border-r border-border-dark bg-background-dark flex-shrink-0 transition-all duration-300 overflow-hidden z-50 lg:z-auto lg:sticky top-0 w-72 lg:w-auto">
            <div class="w-72 h-full flex flex-col">
            <!-- Logo -->
            @include('layouts.partials.sidebar-content')
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-h-screen overflow-x-hidden">
            <!-- Header -->
            @if(isset($header))
            <header class="sticky top-0 z-10 bg-background-dark/95 backdrop-blur border-b border-border-dark">
                <div class="px-4 sm:px-8 py-6">
                    <div class="flex items-center gap-4">
                        <!-- Mobile Hamburger in Header -->
                        <button @click="sidebarOpen = !sidebarOpen" 
                                class="text-white hover:text-primary transition flex-shrink-0"
                                :title="sidebarOpen ? 'Tutup Sidebar' : 'Buka Sidebar'">
                            <span class="material-symbols-outlined" x-text="sidebarOpen ? 'menu_open' : 'menu'"></span>
                        </button>
                        
                        <div class="flex-1">
                            {{ $header }}
                        </div>
                    </div>
                </div>
            </header>
            @endif

            <!-- Page Content -->
            <div class="p-4 sm:p-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
