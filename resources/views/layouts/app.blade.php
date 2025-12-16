<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
        darkMode: localStorage.getItem('darkMode') !== 'false',
        sidebarOpen: window.innerWidth >= 1024,
        sidebarMinimized: localStorage.getItem('sidebarMinimized') === 'true'
      }" 
      :class="darkMode ? 'dark' : ''" 
      x-init="
        $watch('darkMode', val => localStorage.setItem('darkMode', val));
        $watch('sidebarMinimized', val => localStorage.setItem('sidebarMinimized', val));
        window.addEventListener('resize', () => {
          if (window.innerWidth >= 1024) sidebarOpen = true;
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

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
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
        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen && window.innerWidth < 1024" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden" x-cloak></div>
        
        <!-- Unified Sidebar (Push Layout) -->
        <aside :class="sidebarOpen ? 'w-72' : 'w-0 lg:w-72'"
               class="h-screen border-r border-border-dark bg-background-dark flex-shrink-0 transition-all duration-300 overflow-hidden sticky top-0">
            <div class="w-72 h-full flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-border-dark flex items-center gap-3">
                <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-background-dark">account_balance</span>
                </div>
                <div class="sidebar-text flex-1">
                    <h1 class="text-lg font-bold text-white">Simple Akunting</h1>
                    <p class="text-xs text-text-muted">{{ auth()->user()->company->entity_type ?? 'UMKM' }}</p>
                </div>
                <!-- Close button for mobile -->
                <button @click="sidebarOpen = false" 
                        class="lg:hidden text-white hover:text-primary transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto" x-data="{ 
                transaksi: true, 
                masterData: true, 
                laporan: true,
                pengaturan: true 
            }">
                <!-- Dashboard -->
                <x-sidebar-item href="{{ route('dashboard') }}" icon="dashboard" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-sidebar-item>

                <!-- Transaksi Group -->
                <div class="pt-4">
                    <button @click="transaksi = !transaksi" 
                            class="w-full px-4 py-2 flex items-center justify-between text-xs font-bold text-text-muted uppercase tracking-wider hover:text-white transition">
                        <span class="sidebar-group-label">Transaksi</span>
                        <span class="material-symbols-outlined text-sm transition-transform sidebar-group-label" 
                              :class="transaksi ? 'rotate-0' : '-rotate-90'">expand_more</span>
                    </button>
                </div>
                <div x-show="transaksi" x-collapse>
                    <x-sidebar-item href="{{ route('sales.index') }}" icon="point_of_sale" :active="request()->routeIs('sales.*')">
                        Penjualan
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('purchases.index') }}" icon="shopping_cart" :active="request()->routeIs('purchases.*')">
                        Pembelian
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('journals.index') }}" icon="receipt_long" :active="request()->routeIs('journals.*')">
                        Jurnal Umum
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('cash.receive') }}" icon="payments" :active="request()->routeIs('cash.receive')">
                        Penerimaan Kas
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('cash.spend') }}" icon="wallet" :active="request()->routeIs('cash.spend')">
                        Pengeluaran Kas
                    </x-sidebar-item>
                </div>

                <!-- Master Data Group -->
                <div class="pt-4">
                    <button @click="masterData = !masterData" 
                            class="w-full px-4 py-2 flex items-center justify-between text-xs font-bold text-text-muted uppercase tracking-wider hover:text-white transition">
                        <span class="sidebar-group-label">Master Data</span>
                        <span class="material-symbols-outlined text-sm transition-transform sidebar-group-label" 
                              :class="masterData ? 'rotate-0' : '-rotate-90'">expand_more</span>
                    </button>
                </div>
                <div x-show="masterData" x-collapse>
                    <x-sidebar-item href="{{ route('accounts.index') }}" icon="account_tree" :active="request()->routeIs('accounts.*')">
                        Chart of Accounts
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('contacts.index') }}" icon="contacts" :active="request()->routeIs('contacts.*')">
                        Pelanggan & Supplier
                    </x-sidebar-item>
                    @if(auth()->user()->company?->isBumdesa())
                    <x-sidebar-item href="{{ route('units.index') }}" icon="store" :active="request()->routeIs('units.*')">
                        Unit Usaha
                    </x-sidebar-item>
                    @endif
                    <x-sidebar-item href="{{ route('inventory.index') }}" icon="inventory_2" :active="request()->routeIs('inventory.*')">
                        Persediaan
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('assets.index') }}" icon="precision_manufacturing" :active="request()->routeIs('assets.*')">
                        Aset Tetap
                    </x-sidebar-item>
                </div>

                <!-- Laporan Group -->
                <div class="pt-4">
                    <button @click="laporan = !laporan" 
                            class="w-full px-4 py-2 flex items-center justify-between text-xs font-bold text-text-muted uppercase tracking-wider hover:text-white transition">
                        <span class="sidebar-group-label">Laporan</span>
                        <span class="material-symbols-outlined text-sm transition-transform sidebar-group-label" 
                              :class="laporan ? 'rotate-0' : '-rotate-90'">expand_more</span>
                    </button>
                </div>
                <div x-show="laporan" x-collapse>
                    <x-sidebar-item href="{{ route('reports.balance-sheet') }}" icon="balance" :active="request()->routeIs('reports.balance-sheet')">
                        Neraca
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('reports.profit-loss') }}" icon="trending_up" :active="request()->routeIs('reports.profit-loss')">
                        Laba Rugi
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('reports.trial-balance') }}" icon="fact_check" :active="request()->routeIs('reports.trial-balance')">
                        Neraca Saldo
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('reports.ledger') }}" icon="account_balance_wallet" :active="request()->routeIs('reports.ledger')">
                        Buku Besar
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('reports.cash-flow') }}" icon="trending_flat" :active="request()->routeIs('reports.cash-flow')">
                        Arus Kas
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('reports.financial-analysis') }}" icon="analytics" :active="request()->routeIs('reports.financial-analysis')">
                        Analisa Keuangan
                    </x-sidebar-item>
                </div>

                <!-- Pengaturan Group (Admin access) -->
                @if(auth()->user()->isAdmin() || !auth()->user()->role || auth()->user()->role == 'User')
                <div class="pt-4">
                    <button @click="pengaturan = !pengaturan" 
                            class="w-full px-4 py-2 flex items-center justify-between text-xs font-bold text-text-muted uppercase tracking-wider hover:text-white transition">
                        <span class="sidebar-group-label">Pengaturan</span>
                        <span class="material-symbols-outlined text-sm transition-transform sidebar-group-label" 
                              :class="pengaturan ? 'rotate-0' : '-rotate-90'">expand_more</span>
                    </button>
                </div>
                <div x-show="pengaturan" x-collapse>
                    <x-sidebar-item href="{{ route('company.settings') }}" icon="business" :active="request()->routeIs('company.settings')">
                        Perusahaan
                    </x-sidebar-item>
                    <x-sidebar-item href="{{ route('users.index') }}" icon="group" :active="request()->routeIs('users.*')">
                        Kelola Pengguna
                    </x-sidebar-item>
                </div>
                @endif
            </nav>

            <!-- Theme Toggle & User Menu -->
            <div class="p-4 border-t border-border-dark space-y-3">
                <!-- Theme Toggle -->
                <button @click="darkMode = !darkMode" 
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-surface-dark hover:bg-surface-highlight transition">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-text-muted" x-text="darkMode ? 'dark_mode' : 'light_mode'"></span>
                        <span class="text-sm text-text-muted" x-text="darkMode ? 'Mode Gelap' : 'Mode Terang'"></span>
                    </div>
                    <div class="w-10 h-6 rounded-full transition-colors" 
                         :class="darkMode ? 'bg-primary' : 'bg-surface-highlight'">
                        <div class="w-5 h-5 rounded-full bg-white transform transition-transform mt-0.5"
                             :class="darkMode ? 'translate-x-4.5 ml-0.5' : 'translate-x-0.5'"></div>
                    </div>
                </button>

                <!-- User Menu -->
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-surface-dark">
                    <div class="w-10 h-10 bg-primary-dark rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-primary font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0 sidebar-text">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-text-muted truncate">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-text-muted hover:text-primary" title="Logout">
                            <span class="material-symbols-outlined">logout</span>
                        </button>
                    </form>
                </div>
            </div>
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
                                class="lg:hidden text-white hover:text-primary transition flex-shrink-0">
                            <span class="material-symbols-outlined">menu</span>
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
