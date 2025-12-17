<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Daftar Jurnal</h2>
                <p class="text-text-muted text-sm mt-1">Periode {{ \Carbon\Carbon::parse($period['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end_date'])->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="#" onclick="window.print(); return false;" class="px-4 py-2 rounded-full border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition flex items-center gap-2">
                    <span class="material-symbols-outlined">print</span>
                    <span class="hidden sm:inline">Print</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Filter -->
    <div class="mb-6 p-4 rounded-xl border border-border-dark bg-surface-dark/30">
        <form action="{{ route('reports.journal-list') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-text-muted mb-2">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $period['start_date'] }}" 
                       class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-text-muted mb-2">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $period['end_date'] }}" 
                       class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm text-text-muted mb-2">Sumber</label>
                <select name="source" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
                    <option value="">Semua</option>
                    <option value="manual" {{ $source == 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="sales" {{ $source == 'sales' ? 'selected' : '' }}>Penjualan</option>
                    <option value="purchase" {{ $source == 'purchase' ? 'selected' : '' }}>Pembelian</option>
                    <option value="cash_receipt" {{ $source == 'cash_receipt' ? 'selected' : '' }}>Penerimaan Kas</option>
                    <option value="cash_payment" {{ $source == 'cash_payment' ? 'selected' : '' }}>Pengeluaran Kas</option>
                </select>
            </div>
            <button type="submit" class="px-6 h-11 rounded-full font-medium bg-primary hover:bg-primary-dark text-background-dark transition flex items-center gap-2">
                <span class="material-symbols-outlined">filter_list</span>
                Filter
            </button>
        </form>
    </div>

    <!-- Journals Table -->
    <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border-dark bg-surface-highlight/30">
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Tanggal</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">No. Ref</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Keterangan</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Akun</th>
                        <th class="text-right text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Debit</th>
                        <th class="text-right text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @forelse($journals as $journal)
                        @php $first = true; @endphp
                        @foreach($journal->items as $item)
                        <tr class="hover:bg-surface-highlight/20 transition {{ $first ? 'border-t border-border-dark/50' : '' }}">
                            @if($first)
                            <td class="px-4 py-3 text-sm text-white" rowspan="{{ count($journal->items) }}">
                                {{ \Carbon\Carbon::parse($journal->date)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-primary font-mono" rowspan="{{ count($journal->items) }}">
                                {{ $journal->reference }}
                            </td>
                            <td class="px-4 py-3 text-sm text-text-muted" rowspan="{{ count($journal->items) }}">
                                {{ $journal->description }}
                            </td>
                            @endif
                            <td class="px-4 py-3 text-sm text-white {{ $item->debit > 0 ? '' : 'pl-8' }}">
                                {{ $item->account->code }} - {{ $item->account->name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-white font-mono">
                                {{ $item->debit > 0 ? 'Rp ' . number_format($item->debit, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-white font-mono">
                                {{ $item->credit > 0 ? 'Rp ' . number_format($item->credit, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @php $first = false; @endphp
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-2">receipt_long</span>
                            <p>Tidak ada jurnal dalam periode ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t border-border-dark bg-surface-highlight/30">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-sm font-bold text-white text-right">TOTAL</td>
                        <td class="px-4 py-3 text-sm font-bold text-right text-white font-mono">Rp {{ number_format($total_debit, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-right text-white font-mono">Rp {{ number_format($total_credit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $journals->withQueryString()->links() }}
    </div>
</x-app-layout>
