<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Pengungkapan Aset Biologis</h2>
                <p class="text-text-muted text-sm mt-1">PSAK 69 - Biological Asset Disclosure</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.biological-reconciliation') }}" class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-text-muted hover:text-white transition">Rekonsiliasi</a>
                <a href="{{ route('reports.biological-fair-value') }}" class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-text-muted hover:text-white transition">Nilai Wajar</a>
            </div>
        </div>
    </x-slot>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-primary bg-primary/10">
            <p class="text-text-muted text-sm">Total Aset</p>
            <p class="text-xl font-bold text-primary">{{ $summary['total_assets'] }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Nilai Tercatat</p>
            <p class="text-lg font-bold text-white">Rp {{ number_format($summary['total_carrying_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-green-500/50 bg-green-500/10">
            <p class="text-text-muted text-sm">Aset Aktif</p>
            <p class="text-xl font-bold text-green-400">{{ $summary['active_assets'] }}</p>
        </div>
        <div class="p-4 rounded-xl border border-gray-500/50 bg-gray-500/10">
            <p class="text-text-muted text-sm">Aset Nonaktif</p>
            <p class="text-xl font-bold text-gray-400">{{ $summary['inactive_assets'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- By Category -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">Per Kategori</h3>
            <div class="space-y-3">
                @foreach($byCategory as $cat)
                <div class="p-4 rounded-xl bg-surface-highlight/30">
                    <div class="flex justify-between items-center">
                        <p class="text-white font-medium">{{ $cat['label'] }}</p>
                        <p class="text-primary font-bold">Rp {{ number_format($cat['total_carrying_amount'], 0, ',', '.') }}</p>
                    </div>
                    <p class="text-xs text-text-muted mt-1">{{ number_format($cat['total_quantity'], 2) }} unit • {{ $cat['assets']->count() }} aset</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- By Maturity -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">Per Status Kedewasaan</h3>
            <div class="space-y-3">
                @foreach($byMaturity as $mat)
                <div class="p-4 rounded-xl bg-surface-highlight/30 flex justify-between items-center">
                    <div>
                        <p class="text-white font-medium">{{ $mat['label'] }}</p>
                        <p class="text-xs text-text-muted">{{ $mat['count'] }} aset</p>
                    </div>
                    <p class="text-primary font-bold">Rp {{ number_format($mat['total_carrying_amount'], 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- By Type -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">Per Jenis Aset</h3>
            <div class="space-y-3">
                @foreach($byType as $type)
                <div class="p-4 rounded-xl bg-surface-highlight/30 flex justify-between items-center">
                    <div>
                        <p class="text-white font-medium">{{ $type['label'] }}</p>
                        <p class="text-xs text-text-muted">{{ $type['count'] }} aset</p>
                    </div>
                    <p class="text-primary font-bold">Rp {{ number_format($type['total_carrying_amount'], 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- By Valuation Method -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">Per Metode Penilaian</h3>
            <div class="space-y-3">
                @foreach($byValuationMethod as $method)
                <div class="p-4 rounded-xl bg-surface-highlight/30 flex justify-between items-center">
                    <div>
                        <p class="text-white font-medium">{{ $method['label'] }}</p>
                        <p class="text-xs text-text-muted">{{ $method['count'] }} aset</p>
                    </div>
                    <p class="text-primary font-bold">Rp {{ number_format($method['total_carrying_amount'], 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Asset List -->
    <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
        <h3 class="text-lg font-bold text-white mb-4">Daftar Aset Biologis</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border-dark">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Kode</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Nama</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-white">Kategori</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-white">Status</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Kuantitas</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Nilai Tercatat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/50">
                    @foreach($assets as $asset)
                    <tr class="hover:bg-surface-highlight/30">
                        <td class="px-4 py-3 font-mono text-sm text-text-muted">{{ $asset->code }}</td>
                        <td class="px-4 py-3 text-white font-medium">{{ $asset->name }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded text-xs bg-surface-highlight text-white">{{ $asset->getCategoryLabel() }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded text-xs {{ $asset->maturity_status === 'mature' ? 'bg-blue-500/20 text-blue-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                                {{ $asset->getMaturityStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-white">{{ number_format($asset->quantity, 2) }} {{ $asset->unit }}</td>
                        <td class="px-4 py-3 text-right text-primary font-bold">Rp {{ number_format($asset->carrying_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-primary">
                    <tr class="font-bold">
                        <td colspan="5" class="px-4 py-3 text-white">TOTAL</td>
                        <td class="px-4 py-3 text-right text-primary">Rp {{ number_format($summary['total_carrying_amount'], 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
