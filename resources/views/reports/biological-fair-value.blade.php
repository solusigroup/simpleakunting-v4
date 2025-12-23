<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Laporan Perubahan Nilai Wajar</h2>
                <p class="text-text-muted text-sm mt-1">PSAK 69 - Fair Value Changes Report</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.biological-reconciliation') }}" class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-text-muted hover:text-white transition">Rekonsiliasi</a>
                <a href="{{ route('reports.biological-production') }}" class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-text-muted hover:text-white transition">Produksi</a>
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
        <div class="p-4 rounded-xl border border-green-500/50 bg-green-500/10">
            <p class="text-text-muted text-sm">Total Keuntungan</p>
            <p class="text-xl font-bold text-green-400">Rp {{ number_format($summary['total_gains'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-red-500/50 bg-red-500/10">
            <p class="text-text-muted text-sm">Total Kerugian</p>
            <p class="text-xl font-bold text-red-400">Rp {{ number_format($summary['total_losses'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-primary bg-primary/10">
            <p class="text-text-muted text-sm">Perubahan Bersih</p>
            <p class="text-xl font-bold {{ $summary['net_change'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                {{ $summary['net_change'] >= 0 ? '+' : '' }}Rp {{ number_format($summary['net_change'], 0, ',', '.') }}
            </p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Jumlah Penilaian</p>
            <p class="text-xl font-bold text-white">{{ $summary['total_valuations'] }}</p>
        </div>
    </div>

    <!-- By Category -->
    @if(count($byCategory) > 0)
    <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 mb-6">
        <h3 class="text-lg font-bold text-white mb-4">Perubahan per Kategori</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($byCategory as $cat)
            <div class="p-4 rounded-xl bg-surface-highlight/30">
                <p class="text-white font-bold">{{ $cat['label'] }}</p>
                <div class="mt-2 space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-text-muted">Keuntungan</span>
                        <span class="text-green-400">+Rp {{ number_format($cat['gains'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Kerugian</span>
                        <span class="text-red-400">-Rp {{ number_format($cat['losses'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-medium border-t border-border-dark/50 pt-1">
                        <span class="text-text-muted">Bersih</span>
                        <span class="{{ $cat['net'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $cat['net'] >= 0 ? '+' : '' }}Rp {{ number_format($cat['net'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Valuation Details -->
    <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
        <h3 class="text-lg font-bold text-white mb-4">Detail Penilaian Nilai Wajar</h3>
        @if($valuations->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border-dark">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Tanggal</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-white">Aset</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Nilai Sebelum</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Nilai Sesudah</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-white">Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/50">
                    @foreach($valuations as $v)
                    <tr class="hover:bg-surface-highlight/30">
                        <td class="px-4 py-3 text-white">{{ $v->valuation_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <p class="text-white font-medium">{{ $v->biologicalAsset->name }}</p>
                            <p class="text-xs text-text-muted">{{ $v->biologicalAsset->code }}</p>
                        </td>
                        <td class="px-4 py-3 text-right text-text-muted">Rp {{ number_format($v->previous_fair_value, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-white">Rp {{ number_format($v->current_fair_value, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $v->fair_value_change >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $v->fair_value_change >= 0 ? '+' : '' }}Rp {{ number_format($v->fair_value_change, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-center text-text-muted py-8">Tidak ada penilaian nilai wajar pada periode ini</p>
        @endif
    </div>
</x-app-layout>
