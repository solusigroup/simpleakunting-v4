<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Laporan Biaya Produksi</h2>
                <p class="text-text-muted text-sm mt-1">Analisis biaya produksi per periode</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.manufacturing.material-usage') }}" class="px-4 py-2 rounded-xl bg-surface-dark text-text-muted hover:text-white transition">Material Usage</a>
                <a href="{{ route('reports.manufacturing.wip') }}" class="px-4 py-2 rounded-xl bg-surface-dark text-text-muted hover:text-white transition">WIP Valuation</a>
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
        <button type="submit" class="px-6 py-2 rounded-xl bg-primary text-white font-medium">
            Tampilkan
        </button>
    </form>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Produksi</p>
            <p class="text-2xl font-bold text-white">{{ $productions->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Biaya Material</p>
            <p class="text-xl font-bold text-blue-400">Rp {{ number_format($totalMaterialCost, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Biaya Tenaga Kerja</p>
            <p class="text-xl font-bold text-yellow-400">Rp {{ number_format($totalLaborCost, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Biaya Overhead</p>
            <p class="text-xl font-bold text-purple-400">Rp {{ number_format($totalOverheadCost, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-primary bg-primary/10">
            <p class="text-text-muted text-sm">Total Biaya</p>
            <p class="text-xl font-bold text-primary">Rp {{ number_format($totalCost, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Cost Breakdown Chart -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">Komposisi Biaya</h3>
            <div class="flex items-center justify-center">
                <div class="relative w-48 h-48">
                    @php
                        $materialPct = $totalCost > 0 ? ($totalMaterialCost / $totalCost) * 100 : 0;
                        $laborPct = $totalCost > 0 ? ($totalLaborCost / $totalCost) * 100 : 0;
                        $overheadPct = $totalCost > 0 ? ($totalOverheadCost / $totalCost) * 100 : 0;
                    @endphp
                    <svg viewBox="0 0 36 36" class="w-full h-full">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#1e293b" stroke-width="3"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#3b82f6" stroke-width="3"
                                stroke-dasharray="{{ $materialPct }} {{ 100 - $materialPct }}" stroke-dashoffset="25"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#eab308" stroke-width="3"
                                stroke-dasharray="{{ $laborPct }} {{ 100 - $laborPct }}" stroke-dashoffset="{{ 25 - $materialPct }}"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#a855f7" stroke-width="3"
                                stroke-dasharray="{{ $overheadPct }} {{ 100 - $overheadPct }}" stroke-dashoffset="{{ 25 - $materialPct - $laborPct }}"/>
                    </svg>
                </div>
            </div>
            <div class="flex justify-center gap-6 mt-4">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                    <span class="text-sm text-text-muted">Material {{ round($materialPct) }}%</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <span class="text-sm text-text-muted">Tenaga Kerja {{ round($laborPct) }}%</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                    <span class="text-sm text-text-muted">Overhead {{ round($overheadPct) }}%</span>
                </div>
            </div>
        </div>

        <!-- Average Unit Cost -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">Statistik Produksi</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 rounded-xl bg-surface-highlight/30">
                    <span class="text-text-muted">Total Unit Diproduksi</span>
                    <span class="text-white font-bold">{{ number_format($totalQuantity) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-xl bg-surface-highlight/30">
                    <span class="text-text-muted">Rata-rata Biaya/Unit</span>
                    <span class="text-primary font-bold">Rp {{ number_format($averageUnitCost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-xl bg-surface-highlight/30">
                    <span class="text-text-muted">Jumlah Produk Berbeda</span>
                    <span class="text-white font-bold">{{ $costByProduct->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost By Product -->
    <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 mb-6">
        <h3 class="text-lg font-bold text-white mb-4">Biaya per Produk</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border-dark">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Produk</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Qty</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Material</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Labor</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Overhead</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Total</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Per Unit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/50">
                    @forelse($costByProduct as $product)
                    <tr class="hover:bg-surface-highlight/30">
                        <td class="px-4 py-3">
                            <p class="text-white font-medium">{{ $product['product_name'] }}</p>
                            <p class="text-xs text-text-muted">{{ $product['product_code'] }} • {{ $product['production_count'] }}x produksi</p>
                        </td>
                        <td class="px-4 py-3 text-right text-white">{{ number_format($product['quantity']) }} {{ $product['unit'] }}</td>
                        <td class="px-4 py-3 text-right text-blue-400">Rp {{ number_format($product['material_cost'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-yellow-400">Rp {{ number_format($product['labor_cost'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-purple-400">Rp {{ number_format($product['overhead_cost'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-primary font-bold">Rp {{ number_format($product['total_cost'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-green-400">Rp {{ number_format($product['quantity'] > 0 ? $product['total_cost'] / $product['quantity'] : 0, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-text-muted">
                            Tidak ada data produksi pada periode ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Production Details -->
    <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
        <h3 class="text-lg font-bold text-white mb-4">Detail Produksi</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border-dark">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">No. Produksi</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Produk</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Qty</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Total Biaya</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Biaya/Unit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/50">
                    @foreach($productions as $production)
                    <tr class="hover:bg-surface-highlight/30">
                        <td class="px-4 py-3 text-white font-mono text-sm">{{ $production->production_number }}</td>
                        <td class="px-4 py-3 text-text-muted">{{ $production->production_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-white">{{ $production->assembly->name }}</td>
                        <td class="px-4 py-3 text-right text-white">{{ number_format($production->quantity) }} {{ $production->unit }}</td>
                        <td class="px-4 py-3 text-right text-primary">Rp {{ number_format($production->total_cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-green-400">Rp {{ number_format($production->unit_cost, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
