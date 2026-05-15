<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Riwayat Penarikan</h2>
                <p class="text-text-muted text-sm mt-1">Daftar penarikan uang tunai dari tabungan nasabah</p>
            </div>
            <div>
                <x-btn href="{{ route('waste.withdrawals.create') }}" type="primary" class="bg-orange-600 hover:bg-orange-700 border-orange-600">
                    <span class="material-symbols-outlined text-xl">payments</span>
                    Tarik Tabungan
                </x-btn>
            </div>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-text-muted text-xs uppercase tracking-wider border-b border-border-dark/50">
                        <th class="px-6 py-4 font-semibold">No. Transaksi</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Nasabah</th>
                        <th class="px-6 py-4 font-semibold text-right">Jumlah (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/30">
                    @forelse($withdrawals as $withdrawal)
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4 font-bold text-white text-sm">{{ $withdrawal->withdrawal_number }}</td>
                        <td class="px-6 py-4 text-text-muted text-sm">{{ $withdrawal->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-white font-medium text-sm">{{ $withdrawal->collector->name }}</td>
                        <td class="px-6 py-4 text-right font-bold text-accent-red">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="{{ route('waste.withdrawals.receipt', $withdrawal) }}" class="text-text-muted hover:text-white transition" title="Cetak Struk">
                                            <span class="material-symbols-outlined text-xl">print</span>
                                        </a>
                                        <a href="{{ route('journals.show', $withdrawal->journal_id) }}" class="text-text-muted hover:text-white transition" title="Lihat Jurnal">
                                            <span class="material-symbols-outlined text-xl">receipt_long</span>
                                        </a>
                                    </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-2">history</span>
                            <p>Belum ada data penarikan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($withdrawals->hasPages())
        <div class="px-6 py-4 border-t border-border-dark">
            {{ $withdrawals->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
