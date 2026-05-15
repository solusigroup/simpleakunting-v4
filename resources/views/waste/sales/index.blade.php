<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Riwayat Penjualan</h2>
                <p class="text-text-muted text-sm mt-1">Daftar penjualan stok sampah ke agregator/pembeli</p>
            </div>
            <div>
                <x-btn href="{{ route('waste.sales.create') }}" type="primary" class="bg-green-600 hover:bg-green-700 border-green-600">
                    <span class="material-symbols-outlined text-xl">sell</span>
                    Jual Stok
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
                        <th class="px-6 py-4 font-semibold">Jenis Sampah</th>
                        <th class="px-6 py-4 font-semibold">Pembeli</th>
                        <th class="px-6 py-4 font-semibold text-right">Total (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/30">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4 font-bold text-white text-sm">{{ $sale->sale_number }}</td>
                        <td class="px-6 py-4 text-text-muted text-sm">{{ $sale->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-white font-medium text-sm">{{ $sale->category->name }} ({{ number_format($sale->weight, 2) }} {{ $sale->category->unit }})</td>
                        <td class="px-6 py-4 text-text-muted text-sm">{{ $sale->buyer_name ?? '-' }}</td>
                        <td class="px-6 py-4 text-right font-bold text-green-400">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('journals.show', $sale->journal_id) }}" class="text-text-muted hover:text-white transition" title="Lihat Jurnal">
                                <span class="material-symbols-outlined text-xl">receipt_long</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-2">history</span>
                            <p>Belum ada data penjualan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="px-6 py-4 border-t border-border-dark">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
