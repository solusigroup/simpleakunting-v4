<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Riwayat Setoran</h2>
                <p class="text-text-muted text-sm mt-1">Daftar semua transaksi setoran sampah dari nasabah</p>
            </div>
            <div>
                <x-btn href="{{ route('waste.deposits.create') }}" type="primary">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Setoran Baru
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="card p-4 mb-6">
        <form action="{{ route('waste.deposits.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-[10px] text-text-muted uppercase font-bold mb-1">Dari Tanggal</label>
                <input type="date" name="date_start" value="{{ request('date_start') }}" class="w-full bg-surface-highlight border border-border-dark rounded-lg px-3 py-2 text-white text-sm focus:border-primary focus:outline-none">
            </div>
            <div>
                <label class="block text-[10px] text-text-muted uppercase font-bold mb-1">Sampai Tanggal</label>
                <input type="date" name="date_end" value="{{ request('date_end') }}" class="w-full bg-surface-highlight border border-border-dark rounded-lg px-3 py-2 text-white text-sm focus:border-primary focus:outline-none">
            </div>
            <div>
                <label class="block text-[10px] text-text-muted uppercase font-bold mb-1">Nasabah</label>
                <select name="collector_id" class="w-full bg-surface-highlight border border-border-dark rounded-lg px-3 py-2 text-white text-sm focus:border-primary focus:outline-none">
                    <option value="">Semua Nasabah</option>
                    @foreach($collectors as $c)
                        <option value="{{ $c->id }}" {{ request('collector_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-btn type="secondary" class="w-full justify-center py-2">
                    <span class="material-symbols-outlined text-sm">filter_alt</span>
                    Filter
                </x-btn>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-text-muted text-xs uppercase tracking-wider border-b border-border-dark/50">
                        <th class="px-6 py-4 font-semibold">No. Transaksi</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">Nasabah</th>
                        <th class="px-6 py-4 font-semibold">Jenis Sampah</th>
                        <th class="px-6 py-4 font-semibold text-right">Total (Rp)</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/30">
                    @forelse($deposits as $deposit)
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4 font-bold text-white text-sm">{{ $deposit->deposit_number }}</td>
                        <td class="px-6 py-4 text-text-muted text-sm">{{ $deposit->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-white font-medium text-sm">{{ $deposit->collector->name }}</td>
                        <td class="px-6 py-4 text-text-muted text-sm">{{ $deposit->category->name }} ({{ number_format($deposit->weight, 2) }} {{ $deposit->category->unit }})</td>
                        <td class="px-6 py-4 text-right font-bold text-primary">Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('journals.show', $deposit->journal_id) }}" class="text-text-muted hover:text-white transition" title="Lihat Jurnal">
                                <span class="material-symbols-outlined text-xl">receipt_long</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-2">history</span>
                            <p>Belum ada data setoran</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deposits->hasPages())
        <div class="px-6 py-4 border-t border-border-dark">
            {{ $deposits->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
