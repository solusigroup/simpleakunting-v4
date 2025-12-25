<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Dashboard</h2>
                <p class="text-text-muted text-sm mt-1">{{ $company->name }} • Periode {{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Slide-Out Menu Button -->
                <x-slide-out-menu :todayRevenue="$todayRevenue" :todayExpense="$todayExpense" />
                
                <!-- Date Filter Button -->
                <x-btn type="secondary" onclick="document.getElementById('dateFilterModal').classList.remove('hidden')">
                    <span class="material-symbols-outlined text-xl">calendar_today</span>
                    Filter Tanggal
                </x-btn>
                
                <!-- Transaction Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <x-btn type="primary" @click="open = !open">
                        <span class="material-symbols-outlined text-xl">add</span>
                        Transaksi Baru
                        <span class="material-symbols-outlined text-sm ml-1">expand_more</span>
                    </x-btn>
                    <div x-show="open" @click.away="open = false" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-48 bg-surface-dark border border-border-dark rounded-xl shadow-xl z-50 overflow-hidden">
                        <a href="{{ route('sales.create') }}" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-surface-highlight transition">
                            <span class="material-symbols-outlined text-primary">receipt_long</span>
                            Penjualan Baru
                        </a>
                        <a href="{{ route('purchases.create') }}" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-surface-highlight transition">
                            <span class="material-symbols-outlined text-blue-400">shopping_cart</span>
                            Pembelian Baru
                        </a>
                        <a href="{{ route('journals.index') }}" class="flex items-center gap-3 px-4 py-3 text-white hover:bg-surface-highlight transition">
                            <span class="material-symbols-outlined text-orange-400">edit_note</span>
                            Jurnal Manual
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card 
            label="Total Pendapatan" 
            :value="'Rp ' . number_format($totalRevenue, 0, ',', '.')"
            icon="trending_up"
        />
        <x-stat-card 
            label="Total Pengeluaran" 
            :value="'Rp ' . number_format($totalExpense, 0, ',', '.')"
            icon="trending_down"
        />
        <x-stat-card 
            label="Laba Bersih" 
            :value="'Rp ' . number_format($netProfit, 0, ',', '.')"
            :changeType="$netProfit >= 0 ? 'positive' : 'negative'"
            icon="account_balance_wallet"
        />
        <x-stat-card 
            label="Saldo Kas" 
            :value="'Rp ' . number_format($cashBalance, 0, ',', '.')"
            icon="payments"
        />
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="flex items-center gap-4 p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="w-12 h-12 bg-primary/20 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">group</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ $totalCustomers }}</p>
                <p class="text-sm text-text-muted">Pelanggan</p>
            </div>
        </div>
        <div class="flex items-center gap-4 p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-blue-400">local_shipping</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ $totalSuppliers }}</p>
                <p class="text-sm text-text-muted">Supplier</p>
            </div>
        </div>
        <div class="flex items-center gap-4 p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="w-12 h-12 bg-orange-500/20 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-orange-400">warning</span>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ $pendingInvoices }}</p>
                <p class="text-sm text-text-muted">Invoice Jatuh Tempo</p>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Sales -->
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                <h3 class="font-bold text-white">Penjualan Terbaru</h3>
                <a href="{{ route('sales.index') }}" class="text-primary text-sm hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y divide-border-dark/50">
                @forelse($recentSales as $sale)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-surface-highlight/30 transition">
                    <div>
                        <p class="text-white font-medium">{{ $sale->contact->name ?? 'N/A' }}</p>
                        <p class="text-text-muted text-sm">{{ $sale->invoice_number }} • {{ $sale->date->format('d M Y') }}</p>
                    </div>
                    <p class="text-primary font-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</p>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-text-muted">
                    <span class="material-symbols-outlined text-4xl mb-2">receipt_long</span>
                    <p>Belum ada penjualan</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Purchases -->
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                <h3 class="font-bold text-white">Pembelian Terbaru</h3>
                <a href="{{ route('purchases.index') }}" class="text-primary text-sm hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y divide-border-dark/50">
                @forelse($recentPurchases as $purchase)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-surface-highlight/30 transition">
                    <div>
                        <p class="text-white font-medium">{{ $purchase->contact->name ?? 'N/A' }}</p>
                        <p class="text-text-muted text-sm">{{ $purchase->invoice_number }} • {{ $purchase->date->format('d M Y') }}</p>
                    </div>
                    <p class="text-accent-red font-bold">Rp {{ number_format($purchase->total, 0, ',', '.') }}</p>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-text-muted">
                    <span class="material-symbols-outlined text-4xl mb-2">shopping_cart</span>
                    <p>Belum ada pembelian</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Date Filter Modal -->
    <div id="dateFilterModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-surface-dark border border-border-dark rounded-2xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Filter Tanggal</h3>
                <button onclick="document.getElementById('dateFilterModal').classList.add('hidden')" class="text-text-muted hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('dashboard') }}" method="GET">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" 
                               class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" 
                               class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none">
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('dateFilterModal').classList.add('hidden')" 
                            class="flex-1 px-4 py-3 border border-border-dark rounded-xl text-white hover:bg-surface-highlight transition">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl transition">
                        Terapkan Filter
                    </button>
                </div>
            </form>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('dashboard', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" 
                   class="text-xs text-primary hover:underline">Bulan Ini</a>
                <a href="{{ route('dashboard', ['start_date' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->subMonth()->endOfMonth()->format('Y-m-d')]) }}" 
                   class="text-xs text-primary hover:underline">Bulan Lalu</a>
                <a href="{{ route('dashboard', ['start_date' => now()->startOfYear()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}" 
                   class="text-xs text-primary hover:underline">Tahun Ini</a>
            </div>
        </div>
    </div>
</x-app-layout>
