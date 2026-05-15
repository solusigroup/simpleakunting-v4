<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('waste.reports.index') }}" class="text-white hover:text-primary transition flex-shrink-0">
                    <span class="material-symbols-outlined text-2xl">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white font-display">Daftar Saldo Nasabah</h2>
                    <p class="text-text-muted text-sm mt-1">Total kewajiban tabungan sampah kepada seluruh nasabah</p>
                </div>
            </div>
            <div class="flex gap-2">
                <x-btn href="{{ route('waste.reports.collectors', ['export' => 1]) }}" type="secondary" class="print:hidden">
                    <span class="material-symbols-outlined text-xl">download</span>
                    Export Excel
                </x-btn>
                <x-btn type="ghost" onclick="window.print()" class="print:hidden">
                    <span class="material-symbols-outlined text-xl">print</span>
                    Cetak
                </x-btn>
            </div>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-text-muted text-xs uppercase tracking-wider border-b border-border-dark/50 bg-surface-dark/50">
                        <th class="px-6 py-4 font-semibold">ID Nasabah</th>
                        <th class="px-6 py-4 font-semibold">Nama Nasabah</th>
                        <th class="px-6 py-4 font-semibold">No. Telepon</th>
                        <th class="px-6 py-4 font-semibold text-right">Saldo Tabungan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/30">
                    @php $totalBalance = 0; @endphp
                    @forelse($collectors as $c)
                    @php $totalBalance += $c->balance; @endphp
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4 font-mono text-xs text-text-muted">{{ $c->collector_number }}</td>
                        <td class="px-6 py-4 font-bold text-white">{{ $c->name }}</td>
                        <td class="px-6 py-4 text-text-muted text-sm">{{ $c->phone ?? '-' }}</td>
                        <td class="px-6 py-4 text-white text-right font-bold font-mono">Rp {{ number_format($c->balance, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-text-muted">Tidak ada data nasabah</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($collectors->count() > 0)
                <tfoot>
                    <tr class="bg-primary/10 border-t border-primary">
                        <td colspan="3" class="px-6 py-4 text-white font-bold text-right uppercase tracking-wider">TOTAL KEWAJIBAN TABUNGAN</td>
                        <td class="px-6 py-4 text-primary font-bold text-right text-lg font-mono">Rp {{ number_format($totalBalance, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-app-layout>
