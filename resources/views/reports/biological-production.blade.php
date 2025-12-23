<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Laporan Produksi & Panen</h2>
                <p class="text-text-muted text-sm mt-1">PSAK 69 - Production & Harvest Report</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.biological-reconciliation') }}" class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-text-muted hover:text-white transition">Rekonsiliasi</a>
                <a href="{{ route('reports.biological-fair-value') }}" class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-text-muted hover:text-white transition">Nilai Wajar</a>
            </div>
        </div>
    </x-slot>

    <!-- Date Filter -->
    <form method="GET" class="mb-6 flex items-end gap-4 p-4 rounded-xl bg-surface-dark/30 border border-border-dark">
        <div>
            <label class="block text-sm font-medium text-text-muted mb-2">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $startDate }}"
                   class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary">
        </div>
        <div>
            <label class="block text-sm font-medium text-text-muted mb-2">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $endDate }}"
                   class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary">
        </div>
        <button type="submit" class="px-6 py-2 rounded-xl bg-primary text-white font-medium">Tampilkan</button>
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-orange-500/50 bg-orange-500/10">
            <p class="text-text-muted text-sm">Total Panen</p>
            <p class="text-xl font-bold text-orange-400">{{ $summary['total_harvests'] }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Jumlah Dipanen</p>
            <p class="text-xl font-bold text-white">{{ number_format($summary['total_quantity'], 2) }}</p>
        </div>
        <div class="p-4 rounded-xl border border-primary bg-primary/10">
            <p class="text-text-muted text-sm">Nilai Tercatat</p>
            <p class="text-xl font-bold text-primary">Rp {{ number_format($summary['total_carrying_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Produk Unik</p>
            <p class="text-xl font-bold text-white">{{ $summary['unique_products'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- By Product -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">Panen per Produk</h3>
            @if($byProduct->count() > 0)
            <div class="space-y-3">
                @foreach($byProduct as $product)
                <div class="p-4 rounded-xl bg-surface-highlight/30 flex justify-between items-center">
                    <div>
                        <p class="text-white font-medium">{{ $product['product_name'] }}</p>
                        <p class="text-xs text-text-muted">{{ $product['harvest_count'] }}x panen</p>
                    </div>
                    <div class="text-right">
                        <p class="text-orange-400 font-bold">{{ number_format($product['total_quantity'], 2) }} {{ $product['unit'] }}</p>
                        <p class="text-xs text-primary">Rp {{ number_format($product['total_carrying_amount'], 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-center text-text-muted py-8">Tidak ada data panen</p>
            @endif
        </div>

        <!-- By Asset -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">Panen per Aset</h3>
            @if($byAsset->count() > 0)
            <div class="space-y-3">
                @foreach($byAsset as $item)
                <div class="p-4 rounded-xl bg-surface-highlight/30 flex justify-between items-center">
                    <div>
                        <p class="text-white font-medium">{{ $item['asset']->name }}</p>
                        <p class="text-xs text-text-muted">{{ $item['asset']->code }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-orange-400 font-bold">{{ $item['total_harvests'] }}x</p>
                        <p class="text-xs text-primary">Rp {{ number_format($item['total_value'], 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-center text-text-muted py-8">Tidak ada data panen</p>
            @endif
        </div>
    </div>

    <!-- Harvest Details -->
    <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
        <h3 class="text-lg font-bold text-white mb-4">Detail Panen</h3>
        @if($harvests->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border-dark">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Aset Biologis</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Produk</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Kuantitas</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Nilai Wajar</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Nilai Tercatat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/50">
                    @foreach($harvests as $h)
                    <tr class="hover:bg-surface-highlight/30">
                        <td class="px-4 py-3 text-white">{{ $h->harvest_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-text-muted">{{ $h->biologicalAsset->name }}</td>
                        <td class="px-4 py-3 text-white font-medium">{{ $h->product_name }}</td>
                        <td class="px-4 py-3 text-right text-white">{{ number_format($h->quantity, 2) }} {{ $h->unit }}</td>
                        <td class="px-4 py-3 text-right text-text-muted">Rp {{ number_format($h->fair_value_at_harvest, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-primary font-bold">Rp {{ number_format($h->carrying_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-center text-text-muted py-8">Tidak ada data panen pada periode ini</p>
        @endif
    </div>
</x-app-layout>
