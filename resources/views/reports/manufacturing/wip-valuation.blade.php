<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Laporan WIP Valuation</h2>
                <p class="text-text-muted text-sm mt-1">Valuasi Work in Process (Barang Dalam Proses)</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.manufacturing.production-cost') }}" class="px-4 py-2 rounded-xl bg-surface-dark text-text-muted hover:text-white transition">Production Cost</a>
                <a href="{{ route('reports.manufacturing.material-usage') }}" class="px-4 py-2 rounded-xl bg-surface-dark text-text-muted hover:text-white transition">Material Usage</a>
            </div>
        </div>
    </x-slot>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-gray-500/50 bg-gray-500/10">
            <p class="text-text-muted text-sm">Draft</p>
            <p class="text-2xl font-bold text-gray-400">{{ $wipSummary['draft']['count'] }}</p>
            <p class="text-sm text-text-muted">Rp {{ number_format($wipSummary['draft']['total_cost'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-yellow-500/50 bg-yellow-500/10">
            <p class="text-text-muted text-sm">Dalam Proses</p>
            <p class="text-2xl font-bold text-yellow-400">{{ $wipSummary['in_progress']['count'] }}</p>
            <p class="text-sm text-text-muted">Rp {{ number_format($wipSummary['in_progress']['total_cost'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-primary bg-primary/10">
            <p class="text-text-muted text-sm">Total WIP</p>
            <p class="text-2xl font-bold text-primary">{{ $wipSummary['total']['count'] }}</p>
        </div>
        <div class="p-4 rounded-xl border border-primary bg-primary/10">
            <p class="text-text-muted text-sm">Total Nilai WIP</p>
            <p class="text-xl font-bold text-primary">Rp {{ number_format($wipSummary['total']['total_cost'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- WIP by Product -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">WIP per Produk</h3>
            @if($wipByProduct->count() > 0)
            <div class="space-y-3">
                @foreach($wipByProduct as $product)
                <div class="p-3 rounded-xl bg-surface-highlight/30 flex items-center justify-between">
                    <div>
                        <p class="text-white font-medium">{{ $product['product_name'] }}</p>
                        <p class="text-xs text-text-muted">{{ $product['product_code'] }} • {{ number_format($product['quantity']) }} {{ $product['unit'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-primary font-bold">Rp {{ number_format($product['total_cost'], 0, ',', '.') }}</p>
                        <div class="flex gap-2 text-xs">
                            @if($product['draft_count'] > 0)
                            <span class="text-gray-400">{{ $product['draft_count'] }} draft</span>
                            @endif
                            @if($product['in_progress_count'] > 0)
                            <span class="text-yellow-400">{{ $product['in_progress_count'] }} proses</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-center text-text-muted py-8">Tidak ada WIP saat ini</p>
            @endif
        </div>

        <!-- WIP by COA -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">WIP per Akun (untuk Neraca)</h3>
            @if($wipByAccount->count() > 0)
            <div class="space-y-3">
                @foreach($wipByAccount as $account)
                <div class="p-3 rounded-xl bg-surface-highlight/30 flex items-center justify-between">
                    <div>
                        <p class="text-white font-medium">{{ $account['coa_name'] }}</p>
                        <p class="text-xs text-text-muted">{{ $account['coa_code'] }} • {{ $account['count'] }} produksi</p>
                    </div>
                    <p class="text-primary font-bold">Rp {{ number_format($account['total_cost'], 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-center text-text-muted py-8">Tidak ada WIP saat ini</p>
            @endif
        </div>
    </div>

    <!-- Aging Analysis -->
    @if($aging->where('risk_level', '!=', 'low')->count() > 0)
    <div class="mb-6 p-4 rounded-xl bg-yellow-500/20 border border-yellow-500/50">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-yellow-400">schedule</span>
            <div>
                <p class="text-white font-bold">Peringatan Aging</p>
                <p class="text-sm text-text-muted">{{ $aging->where('risk_level', 'high')->count() }} produksi berusia >30 hari, {{ $aging->where('risk_level', 'medium')->count() }} berusia >14 hari</p>
            </div>
        </div>
    </div>
    @endif

    <!-- WIP Details -->
    <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
        <h3 class="text-lg font-bold text-white mb-4">Detail WIP</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border-dark">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">No. Produksi</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Produk</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-white">Status</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Nilai</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Umur (hari)</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-white">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/50">
                    @forelse($wipProductions as $production)
                    @php
                        $daysOld = now()->diffInDays($production->production_date);
                        $riskLevel = $daysOld > 30 ? 'high' : ($daysOld > 14 ? 'medium' : 'low');
                    @endphp
                    <tr class="hover:bg-surface-highlight/30 {{ $riskLevel == 'high' ? 'bg-red-500/5' : ($riskLevel == 'medium' ? 'bg-yellow-500/5' : '') }}">
                        <td class="px-4 py-3">
                            <p class="text-white font-mono text-sm">{{ $production->production_number }}</p>
                            <p class="text-xs text-text-muted">{{ $production->production_date->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-white">{{ $production->assembly->name }}</p>
                            <p class="text-xs text-text-muted">{{ number_format($production->quantity) }} {{ $production->unit }}</p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                {{ $production->status == 'in_progress' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-gray-500/20 text-gray-400' }}">
                                {{ $production->status == 'in_progress' ? 'Dalam Proses' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-primary font-bold">
                            Rp {{ number_format($production->total_cost, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="{{ $riskLevel == 'high' ? 'text-red-400' : ($riskLevel == 'medium' ? 'text-yellow-400' : 'text-white') }}">
                                {{ $daysOld }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('productions.show', $production->id) }}" 
                               class="text-text-muted hover:text-primary transition">
                                <span class="material-symbols-outlined">visibility</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-3xl mb-2">check_circle</span>
                            <p>Tidak ada Work in Process saat ini</p>
                            <p class="text-sm">Semua produksi telah selesai</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
