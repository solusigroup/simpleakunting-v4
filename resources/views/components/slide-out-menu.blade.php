@props(['todayRevenue' => 0, 'todayExpense' => 0])

<!-- Slide-Out Menu Panel -->
<div x-data="{ open: false }" 
     x-on:open-slide-menu.window="open = true"
     x-on:keydown.escape.window="open = false">
    
    <!-- Menu Toggle Button (for header) -->
    <button @click="open = true" 
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-surface-dark border border-border-dark hover:bg-surface-highlight hover:border-primary/50 text-white transition-all duration-200 group"
            title="Menu Cepat">
        <span class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform">rocket_launch</span>
        <span class="hidden sm:inline text-sm font-medium">Menu Cepat</span>
    </button>

    <!-- Backdrop Overlay -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50" 
         x-cloak></div>

    <!-- Slide-Out Panel -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed top-0 right-0 h-full w-full sm:w-96 bg-background-dark border-l border-border-dark shadow-2xl z-50 flex flex-col overflow-hidden"
         x-cloak>
        
        <!-- Header -->
        <div class="p-6 border-b border-border-dark bg-gradient-to-r from-primary/10 to-transparent">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary/20 rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">rocket_launch</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Menu Cepat</h2>
                        <p class="text-xs text-text-muted">Akses cepat ke fitur favorit</p>
                    </div>
                </div>
                <button @click="open = false" 
                        class="w-9 h-9 flex items-center justify-center rounded-lg bg-surface-dark hover:bg-surface-highlight text-text-muted hover:text-white transition">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Quick Actions -->
            <div>
                <h3 class="text-xs font-bold text-text-muted uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">bolt</span>
                    Quick Actions
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    <a href="{{ route('sales.create') }}" 
                       class="group flex flex-col items-center gap-2 p-4 rounded-2xl bg-surface-dark border border-border-dark hover:border-primary hover:bg-primary/10 transition-all duration-200">
                        <div class="w-12 h-12 bg-primary/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-primary text-2xl">point_of_sale</span>
                        </div>
                        <span class="text-xs font-medium text-white text-center">Penjualan</span>
                    </a>
                    <a href="{{ route('purchases.create') }}" 
                       class="group flex flex-col items-center gap-2 p-4 rounded-2xl bg-surface-dark border border-border-dark hover:border-blue-400 hover:bg-blue-500/10 transition-all duration-200">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-blue-400 text-2xl">shopping_cart</span>
                        </div>
                        <span class="text-xs font-medium text-white text-center">Pembelian</span>
                    </a>
                    <a href="{{ route('journals.index') }}" 
                       class="group flex flex-col items-center gap-2 p-4 rounded-2xl bg-surface-dark border border-border-dark hover:border-orange-400 hover:bg-orange-500/10 transition-all duration-200">
                        <div class="w-12 h-12 bg-orange-500/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-orange-400 text-2xl">edit_note</span>
                        </div>
                        <span class="text-xs font-medium text-white text-center">Jurnal</span>
                    </a>
                </div>
            </div>

            <!-- Cash Transactions -->
            <div>
                <h3 class="text-xs font-bold text-text-muted uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">payments</span>
                    Kas & Bank
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('cash.receive') }}" 
                       class="group flex items-center gap-3 p-4 rounded-xl bg-surface-dark border border-border-dark hover:border-green-400 hover:bg-green-500/10 transition-all duration-200">
                        <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-green-400">arrow_downward</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">Penerimaan</p>
                            <p class="text-xs text-text-muted">Kas Masuk</p>
                        </div>
                    </a>
                    <a href="{{ route('cash.spend') }}" 
                       class="group flex items-center gap-3 p-4 rounded-xl bg-surface-dark border border-border-dark hover:border-red-400 hover:bg-red-500/10 transition-all duration-200">
                        <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-red-400">arrow_upward</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">Pengeluaran</p>
                            <p class="text-xs text-text-muted">Kas Keluar</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Report Shortcuts -->
            <div>
                <h3 class="text-xs font-bold text-text-muted uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">analytics</span>
                    Laporan
                </h3>
                <div class="space-y-2">
                    <a href="{{ route('reports.balance-sheet') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-dark border border-transparent hover:border-border-dark transition-all duration-200 group">
                        <span class="material-symbols-outlined text-primary group-hover:scale-110 transition-transform">balance</span>
                        <span class="text-sm text-white">Neraca</span>
                        <span class="material-symbols-outlined text-text-muted ml-auto text-sm">chevron_right</span>
                    </a>
                    <a href="{{ route('reports.profit-loss') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-dark border border-transparent hover:border-border-dark transition-all duration-200 group">
                        <span class="material-symbols-outlined text-green-400 group-hover:scale-110 transition-transform">trending_up</span>
                        <span class="text-sm text-white">Laba Rugi</span>
                        <span class="material-symbols-outlined text-text-muted ml-auto text-sm">chevron_right</span>
                    </a>
                    <a href="{{ route('reports.ledger') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-dark border border-transparent hover:border-border-dark transition-all duration-200 group">
                        <span class="material-symbols-outlined text-blue-400 group-hover:scale-110 transition-transform">account_balance_wallet</span>
                        <span class="text-sm text-white">Buku Besar</span>
                        <span class="material-symbols-outlined text-text-muted ml-auto text-sm">chevron_right</span>
                    </a>
                    <a href="{{ route('reports.cash-flow') }}" 
                       class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-dark border border-transparent hover:border-border-dark transition-all duration-200 group">
                        <span class="material-symbols-outlined text-purple-400 group-hover:scale-110 transition-transform">trending_flat</span>
                        <span class="text-sm text-white">Arus Kas</span>
                        <span class="material-symbols-outlined text-text-muted ml-auto text-sm">chevron_right</span>
                    </a>
                </div>
            </div>

            <!-- Today's Summary -->
            <div>
                <h3 class="text-xs font-bold text-text-muted uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">calendar_today</span>
                    Ringkasan Hari Ini
                </h3>
                <div class="bg-gradient-to-br from-surface-dark to-surface-highlight rounded-2xl border border-border-dark p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-green-500/20 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-400 text-lg">arrow_downward</span>
                            </div>
                            <span class="text-sm text-text-muted">Pendapatan</span>
                        </div>
                        <span class="text-lg font-bold text-green-400">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-px bg-border-dark"></div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-red-500/20 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-red-400 text-lg">arrow_upward</span>
                            </div>
                            <span class="text-sm text-text-muted">Pengeluaran</span>
                        </div>
                        <span class="text-lg font-bold text-red-400">Rp {{ number_format($todayExpense, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-px bg-border-dark"></div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-sm font-medium text-white">Laba Bersih</span>
                        @php $netToday = $todayRevenue - $todayExpense; @endphp
                        <span class="text-lg font-bold {{ $netToday >= 0 ? 'text-primary' : 'text-red-400' }}">
                            Rp {{ number_format(abs($netToday), 0, ',', '.') }}
                            @if($netToday < 0)
                            <span class="text-xs">(Rugi)</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-border-dark bg-surface-dark/50">
            <a href="{{ route('dashboard') }}" 
               @click="open = false"
               class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-primary hover:bg-primary-dark text-white font-medium transition">
                <span class="material-symbols-outlined">dashboard</span>
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
