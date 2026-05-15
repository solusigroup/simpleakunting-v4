<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('waste.reports.index') }}" class="text-white hover:text-primary transition flex-shrink-0 print:hidden">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Buku Bantu Nasabah</h2>
                <p class="text-text-muted text-sm mt-1">Detail transaksi tabungan per nasabah</p>
            </div>
        </div>
    </x-slot>

    <!-- Filter Card -->
    <div class="card p-6 mb-8 print:hidden">
        <form action="{{ route('waste.reports.ledger') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="block text-sm text-text-muted mb-2 font-bold uppercase tracking-wider">Pilih Nasabah</label>
                <select name="waste_collector_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                    <option value="">Pilih Nasabah</option>
                    @foreach($all_collectors as $c)
                        <option value="{{ $c->id }}" {{ request('waste_collector_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->collector_number }} - {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-btn type="primary" class="w-full justify-center py-3">
                    <span class="material-symbols-outlined text-xl">search</span>
                    Tampilkan
                </x-btn>
            </div>
            @if($collector)
            <div>
                <x-btn type="ghost" onclick="window.print()" class="w-full justify-center py-3">
                    <span class="material-symbols-outlined text-xl">print</span>
                    Cetak
                </x-btn>
            </div>
            @endif
        </form>
    </div>

    @if($collector)
    <div class="space-y-6">
        <!-- Header Info (Visible on Print) -->
        <div class="hidden print:block text-center mb-8 border-b border-border-dark pb-6">
            <h2 class="text-3xl font-bold text-white">BUKU BANTU TABUNGAN</h2>
            <p class="text-xl text-primary font-bold mt-2">{{ $collector->name }} ({{ $collector->collector_number }})</p>
            <p class="text-text-muted text-sm italic">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden shadow-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-text-muted text-xs uppercase tracking-wider border-b border-border-dark/50 bg-surface-dark/50">
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                        <th class="px-6 py-4 font-semibold">No. Ref</th>
                        <th class="px-6 py-4 font-semibold">Keterangan</th>
                        <th class="px-6 py-4 font-semibold text-right">Debit (Tarik)</th>
                        <th class="px-6 py-4 font-semibold text-right">Kredit (Setor)</th>
                        <th class="px-6 py-4 font-semibold text-right">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/30">
                    @php $runningBalance = 0; @endphp
                    @forelse($transactions as $trx)
                    @php 
                        $runningBalance += ($trx->type == 'Deposit' ? $trx->amount : $trx->amount); // Withdrawal is already negative
                    @endphp
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4 text-text-muted text-sm">{{ $trx->date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-white font-mono text-xs">{{ $trx->deposit_number ?? $trx->withdrawal_number }}</td>
                        <td class="px-6 py-4 text-white text-sm">
                            @if($trx->type == 'Deposit')
                                Setoran: {{ $trx->category->name }} ({{ number_format($trx->weight, 2) }} {{ $trx->category->unit }})
                            @else
                                Penarikan Tunai
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-accent-red">
                            @if($trx->type == 'Withdrawal')
                                Rp {{ number_format(abs($trx->amount), 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-mono text-green-400">
                            @if($trx->type == 'Deposit')
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-white">
                            Rp {{ number_format($runningBalance, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-text-muted italic">Tidak ada mutasi transaksi untuk periode ini</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-primary/20 border-t-2 border-primary">
                        <td colspan="5" class="px-6 py-4 text-white font-bold text-right uppercase tracking-wider">Saldo Tabungan Saat Ini</td>
                        <td class="px-6 py-4 text-primary font-bold text-right text-xl font-mono">Rp {{ number_format($collector->balance, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="hidden print:flex justify-between mt-12 px-12">
            <div class="text-center">
                <p class="text-text-muted mb-16">Petugas Bank Sampah</p>
                <div class="w-40 border-b border-white mx-auto"></div>
            </div>
            <div class="text-center">
                <p class="text-text-muted mb-16">Nasabah</p>
                <p class="text-white font-bold">{{ $collector->name }}</p>
                <div class="w-40 border-b border-white mx-auto"></div>
            </div>
        </div>
    </div>
    @endif
</x-app-layout>
