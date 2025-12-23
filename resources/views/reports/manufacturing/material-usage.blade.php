<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Laporan Penggunaan Material</h2>
                <p class="text-text-muted text-sm mt-1">Analisis konsumsi bahan baku dalam produksi</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.manufacturing.production-cost') }}" class="px-4 py-2 rounded-xl bg-surface-dark text-text-muted hover:text-white transition">Production Cost</a>
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
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Material Digunakan</p>
            <p class="text-2xl font-bold text-white">{{ $materialUsage->count() }} jenis</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Biaya Material</p>
            <p class="text-xl font-bold text-primary">Rp {{ number_format($totalMaterialCost, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border {{ $materialsWithWaste > 0 ? 'border-red-500/50 bg-red-500/10' : 'border-border-dark bg-surface-dark/30' }}">
            <p class="text-text-muted text-sm">Material dengan Waste</p>
            <p class="text-xl font-bold {{ $materialsWithWaste > 0 ? 'text-red-400' : 'text-white' }}">{{ $materialsWithWaste }}</p>
        </div>
        <div class="p-4 rounded-xl border {{ $materialsWithSavings > 0 ? 'border-green-500/50 bg-green-500/10' : 'border-border-dark bg-surface-dark/30' }}">
            <p class="text-text-muted text-sm">Material dengan Efisiensi</p>
            <p class="text-xl font-bold {{ $materialsWithSavings > 0 ? 'text-green-400' : 'text-white' }}">{{ $materialsWithSavings }}</p>
        </div>
    </div>

    <!-- High Variance Alert -->
    @if($highVarianceMaterials->count() > 0)
    <div class="mb-6 p-4 rounded-xl bg-yellow-500/20 border border-yellow-500/50">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-yellow-400">warning</span>
            <div>
                <p class="text-white font-bold">Perhatian: Variansi Tinggi Terdeteksi</p>
                <p class="text-sm text-text-muted">{{ $highVarianceMaterials->count() }} material memiliki variansi lebih dari 5%</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Materials -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        @foreach($topMaterials as $index => $material)
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <span class="text-xs text-text-muted">#{{ $index + 1 }}</span>
                    <p class="text-white font-bold">{{ $material['component_name'] }}</p>
                </div>
                <span class="px-2 py-1 rounded text-xs {{ $material['variance'] > 0 ? 'bg-red-500/20 text-red-400' : ($material['variance'] < 0 ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400') }}">
                    {{ $material['variance'] > 0 ? '+' : '' }}{{ number_format($material['variance_percent'], 1) }}%
                </span>
            </div>
            <p class="text-sm text-text-muted mb-2">{{ $material['component_code'] }}</p>
            <div class="flex justify-between text-sm">
                <span class="text-text-muted">Digunakan</span>
                <span class="text-white">{{ number_format($material['quantity_used'], 2) }} {{ $material['unit'] }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-text-muted">Total Biaya</span>
                <span class="text-primary">Rp {{ number_format($material['total_cost'], 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Material Usage Table -->
    <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
        <h3 class="text-lg font-bold text-white mb-4">Detail Penggunaan Material</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border-dark">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Material</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Dibutuhkan</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Digunakan</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Variansi</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Total Biaya</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Stok Saat Ini</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Produk Terkait</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/50">
                    @forelse($materialUsage as $material)
                    <tr class="hover:bg-surface-highlight/30 {{ abs($material['variance_percent']) > 5 ? 'bg-yellow-500/5' : '' }}">
                        <td class="px-4 py-3">
                            <p class="text-white font-medium">{{ $material['component_name'] }}</p>
                            <p class="text-xs text-text-muted">{{ $material['component_code'] }}</p>
                        </td>
                        <td class="px-4 py-3 text-right text-text-muted">{{ number_format($material['quantity_required'], 2) }} {{ $material['unit'] }}</td>
                        <td class="px-4 py-3 text-right text-white">{{ number_format($material['quantity_used'], 2) }} {{ $material['unit'] }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="{{ $material['variance'] > 0 ? 'text-red-400' : ($material['variance'] < 0 ? 'text-green-400' : 'text-text-muted') }}">
                                {{ $material['variance'] > 0 ? '+' : '' }}{{ number_format($material['variance'], 2) }}
                                <span class="text-xs">({{ $material['variance_percent'] > 0 ? '+' : '' }}{{ number_format($material['variance_percent'], 1) }}%)</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-primary font-medium">Rp {{ number_format($material['total_cost'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right {{ $material['current_stock'] < 10 ? 'text-orange-400' : 'text-white' }}">
                            {{ number_format($material['current_stock'], 2) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($material['products']->take(2) as $product)
                                <span class="px-2 py-0.5 rounded text-xs bg-surface-highlight text-text-muted">{{ $product }}</span>
                                @endforeach
                                @if($material['products']->count() > 2)
                                <span class="px-2 py-0.5 rounded text-xs bg-surface-highlight text-text-muted">+{{ $material['products']->count() - 2 }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-text-muted">
                            Tidak ada data penggunaan material pada periode ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-primary">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-white font-bold">Total</td>
                        <td class="px-4 py-3 text-right text-primary font-bold">Rp {{ number_format($totalMaterialCost, 0, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
