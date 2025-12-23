<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Rekonsiliasi Aset Biologis (PSAK 69)</h2>
                <p class="text-text-muted text-sm mt-1">Laporan rekonsiliasi perubahan nilai tercatat aset biologis</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="window.print()">
                    <span class="material-symbols-outlined text-xl">print</span>
                    Cetak
                </x-btn>
                <x-btn type="secondary" onclick="window.location.href='/biological-assets'">
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                    Kembali
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Period Filter -->
    <div class="mb-6 p-4 rounded-xl border border-border-dark bg-surface-dark/30">
        <form method="GET" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-text-muted mb-2">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-text-muted mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
            </div>
            <x-btn type="primary" type="submit">
                <span class="material-symbols-outlined text-xl">search</span>
                Tampilkan
            </x-btn>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Saldo Awal</p>
            <p class="text-2xl font-bold text-white">Rp {{ number_format($totals['opening_balance'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Penambahan</p>
            <p class="text-2xl font-bold text-green-400">Rp {{ number_format($totals['additions'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Pengurangan</p>
            <p class="text-2xl font-bold text-red-400">Rp {{ number_format($totals['decreases'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Saldo Akhir</p>
            <p class="text-2xl font-bold text-primary">Rp {{ number_format($totals['closing_balance'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Reconciliation Table -->
    <div class="bg-surface-dark/30 rounded-2xl border border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-surface-dark border-b border-border-dark">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-white">Aset Biologis</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-white">Saldo Awal</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-white">Penambahan</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-white">Pengurangan</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-white">Perubahan Nilai Wajar</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-white">Saldo Akhir</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-white">Kuantitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/50">
                    @foreach($reconciliationData as $data)
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-white font-medium">{{ $data['asset']->name }}</p>
                                <p class="text-xs text-text-muted">{{ $data['asset']->code }} • {{ $data['asset']->getCategoryLabel() }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-white">Rp {{ number_format($data['opening_balance'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-green-400">{{ $data['additions'] > 0 ? number_format($data['additions'], 2) : '-' }}</td>
                        <td class="px-6 py-4 text-right text-red-400">{{ $data['decreases'] > 0 ? number_format($data['decreases'], 2) : '-' }}</td>
                        <td class="px-6 py-4 text-right {{ $data['fair_value_changes'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            Rp {{ number_format($data['fair_value_changes'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-primary font-medium">Rp {{ number_format($data['closing_balance'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-text-muted">{{ number_format($data['quantity'], 2) }} {{ $data['asset']->unit }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-surface-dark border-t-2 border-primary">
                    <tr>
                        <td class="px-6 py-4 text-white font-bold">TOTAL</td>
                        <td class="px-6 py-4 text-right text-white font-bold">Rp {{ number_format($totals['opening_balance'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-green-400 font-bold">-</td>
                        <td class="px-6 py-4 text-right text-red-400 font-bold">-</td>
                        <td class="px-6 py-4 text-right {{ $totals['fair_value_changes'] >= 0 ? 'text-green-400' : 'text-red-400' }} font-bold">
                            Rp {{ number_format($totals['fair_value_changes'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-primary font-bold">Rp {{ number_format($totals['closing_balance'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- PSAK 69 Note -->
    <div class="mt-6 p-4 bg-primary/10 border border-primary/30 rounded-xl">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-primary text-2xl">info</span>
            <div>
                <h4 class="text-white font-semibold mb-1">Catatan PSAK 69</h4>
                <p class="text-text-muted text-sm">Laporan ini menunjukkan rekonsiliasi nilai tercatat aset biologis sesuai dengan persyaratan pengungkapan PSAK 69 paragraf 50. Perubahan nilai wajar diakui dalam laba rugi periode berjalan.</p>
            </div>
        </div>
    </div>
</x-app-layout>
