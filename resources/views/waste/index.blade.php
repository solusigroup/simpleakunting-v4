<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Bank Sampah</h2>
                <p class="text-text-muted text-sm mt-1">Kelola tabungan sampah nasabah dan persediaan</p>
            </div>
            <div class="flex items-center gap-3">
                <x-btn href="{{ route('waste.settings.edit') }}" type="secondary">
                    <span class="material-symbols-outlined text-xl">settings</span>
                    Pengaturan
                </x-btn>
                <x-btn href="{{ route('waste.deposits.create') }}" type="primary">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Catat Setoran
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card 
            label="Total Nasabah" 
            :value="number_format($stats['total_collectors'])"
            icon="groups"
        />
        <x-stat-card 
            label="Total Tabungan" 
            :value="'Rp ' . number_format($stats['total_balance'], 0, ',', '.')"
            icon="wallet"
        />
        <x-stat-card 
            label="Total Setoran" 
            :value="number_format($stats['total_deposits'])"
            icon="recycling"
        />
        <x-stat-card 
            label="Total Penjualan" 
            :value="number_format($stats['total_sales'])"
            icon="sell"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Stock Table -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="font-bold text-white text-lg">Stok Persediaan Sampah</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-text-muted text-xs uppercase tracking-wider border-b border-border-dark/50">
                                <th class="px-6 py-4 font-semibold">Jenis Sampah</th>
                                <th class="px-6 py-4 font-semibold">Harga Beli</th>
                                <th class="px-6 py-4 font-semibold">Stok Saat Ini</th>
                                <th class="px-6 py-4 font-semibold">Nilai Persediaan</th>
                                <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark/30">
                            @forelse($stats['waste_stock'] as $cat)
                            <tr class="hover:bg-surface-highlight/30 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white">{{ $cat->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-white">Rp {{ number_format($cat->buy_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-surface-highlight text-white border border-border-dark">
                                        {{ number_format($cat->stock, 2) }} {{ $cat->unit }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-white font-bold">Rp {{ number_format($cat->stock * $cat->buy_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('waste.sales.create', ['category_id' => $cat->id]) }}" class="text-primary hover:underline text-sm font-bold">
                                        Jual Stok
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-text-muted">
                                    <span class="material-symbols-outlined text-4xl mb-2">inventory_2</span>
                                    <p>Belum ada stok sampah tersedia</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="font-bold text-white">Setoran Terakhir</h3>
                    <a href="{{ route('waste.deposits.index') }}" class="text-primary text-sm hover:underline">Lihat Semua</a>
                </div>
                <div class="divide-y divide-border-dark/50">
                    @forelse($recent_deposits as $dep)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-surface-highlight/30 transition">
                        <div>
                            <p class="text-white font-medium">{{ $dep->collector->name }}</p>
                            <p class="text-text-muted text-xs">{{ $dep->category->name }} • {{ number_format($dep->weight, 2) }} {{ $dep->category->unit }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-primary font-bold text-sm">+Rp {{ number_format($dep->total_amount, 0, ',', '.') }}</p>
                            <p class="text-text-muted text-[10px]">{{ $dep->date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-text-muted">
                        <p class="text-sm">Belum ada aktivitas</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Links -->
            <div class="rounded-2xl bg-primary p-6 shadow-lg shadow-primary/20">
                <h4 class="text-white font-bold mb-4">Menu Cepat</h4>
                <div class="grid gap-3">
                    <a href="{{ route('waste.collectors.index') }}" class="flex items-center gap-3 p-3 bg-white/10 hover:bg-white/20 rounded-xl text-white transition">
                        <span class="material-symbols-outlined">group</span>
                        <span class="text-sm font-medium">Kelola Nasabah</span>
                    </a>
                    <a href="{{ route('waste.categories.index') }}" class="flex items-center gap-3 p-3 bg-white/10 hover:bg-white/20 rounded-xl text-white transition">
                        <span class="material-symbols-outlined">sell</span>
                        <span class="text-sm font-medium">Harga Sampah</span>
                    </a>
                    <a href="{{ route('waste.withdrawals.index') }}" class="flex items-center gap-3 p-3 bg-white/10 hover:bg-white/20 rounded-xl text-white transition">
                        <span class="material-symbols-outlined">payments</span>
                        <span class="text-sm font-medium">Penarikan Tabungan</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
