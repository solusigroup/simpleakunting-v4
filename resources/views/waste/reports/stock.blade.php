<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('waste.reports.index') }}" class="text-white hover:text-primary transition flex-shrink-0">
                    <span class="material-symbols-outlined text-2xl">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white font-display">Laporan Stok Sampah</h2>
                    <p class="text-text-muted text-sm mt-1">Sisa stok fisik sampah dan estimasi nilai persediaan</p>
                </div>
            </div>
            <div class="flex gap-2">
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
                        <th class="px-6 py-4 font-semibold">Jenis Sampah</th>
                        <th class="px-6 py-4 font-semibold text-right">Stok Fisik</th>
                        <th class="px-6 py-4 font-semibold text-right">Harga Beli Rata-rata</th>
                        <th class="px-6 py-4 font-semibold text-right">Nilai Persediaan (Asset)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/30">
                    @php $totalValue = 0; @endphp
                    @forelse($categories as $cat)
                    @php $totalValue += $cat->stock_value; @endphp
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4 font-bold text-white">{{ $cat->name }}</td>
                        <td class="px-6 py-4 text-white text-right">
                            <span class="font-mono">{{ number_format($cat->stock, 2) }}</span> 
                            <span class="text-text-muted text-xs uppercase">{{ $cat->unit }}</span>
                        </td>
                        <td class="px-6 py-4 text-text-muted text-right font-mono italic">Rp {{ number_format($cat->buy_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-white text-right font-bold font-mono">Rp {{ number_format($cat->stock_value, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-text-muted">Tidak ada data stok</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($categories->count() > 0)
                <tfoot>
                    <tr class="bg-primary/10 border-t border-primary">
                        <td colspan="3" class="px-6 py-4 text-white font-bold text-right">TOTAL NILAI PERSEDIAAN</td>
                        <td class="px-6 py-4 text-primary font-bold text-right text-lg font-mono">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-app-layout>
