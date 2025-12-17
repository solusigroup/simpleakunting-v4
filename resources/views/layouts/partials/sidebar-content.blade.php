<div class="p-6 border-b border-border-dark flex items-center gap-3">
    <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center flex-shrink-0">
        <span class="material-symbols-outlined text-background-dark">account_balance</span>
    </div>
    <div class="sidebar-text flex-1">
        <h1 class="text-lg font-bold text-white">Simple Akunting</h1>
        <p class="text-xs text-text-muted">{{ auth()->user()->company->entity_type ?? 'UMKM' }}</p>
    </div>
    <!-- Close button -->
    <button @click="sidebarOpen = false" 
            class="text-white hover:text-primary transition"
            title="Tutup Sidebar">
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
        <x-sidebar-item href="{{ route('reports.equity-changes') }}" icon="account_balance" :active="request()->routeIs('reports.equity-changes')">
            Perubahan Ekuitas
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

<!-- Help - Pinned at bottom -->
<div class="px-4 pb-2">
    <x-sidebar-item href="{{ route('help') }}" icon="menu_book" :active="request()->routeIs('help')">
        Panduan Aplikasi
    </x-sidebar-item>
</div>

<!-- Theme Toggle & User Menu -->
<div class="p-4 border-t border-border-dark space-y-3">
    <!-- Theme Toggle -->
    <button @click="darkMode = !darkMode" 
            class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-surface-dark hover:bg-surface-highlight transition">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-text-muted" x-text="darkMode ? 'dark_mode' : 'light_mode'"></span>
            <span class="text-sm text-text-muted sidebar-text" x-text="darkMode ? 'Mode Gelap' : 'Mode Terang'"></span>
        </div>
        <div class="w-10 h-6 rounded-full transition-colors sidebar-text" 
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
