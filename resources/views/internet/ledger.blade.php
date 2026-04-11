<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Buku Bantu Piutang Internet</h2>
                <p class="text-text-muted text-sm mt-1">Ringkasan piutang per pelanggan internet</p>
            </div>
        </div>
    </x-slot>

    <!-- Grand Totals -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">receipt_long</span>
                </div>
                <div>
                    <div class="text-text-muted text-sm">Total Tagihan</div>
                    <div class="text-xl font-bold text-white">Rp {{ number_format($grandTotals['total_billed'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">payments</span>
                </div>
                <div>
                    <div class="text-text-muted text-sm">Total Terbayar</div>
                    <div class="text-xl font-bold text-primary">Rp {{ number_format($grandTotals['total_paid'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-orange-400">account_balance</span>
                </div>
                <div>
                    <div class="text-text-muted text-sm">Sisa Piutang</div>
                    <div class="text-xl font-bold text-orange-400">Rp {{ number_format($grandTotals['total_outstanding'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="rounded-2xl border border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-surface-dark/50">
                        <th class="p-4 text-left text-xs font-bold text-text-muted uppercase">ID</th>
                        <th class="p-4 text-left text-xs font-bold text-text-muted uppercase">Pelanggan</th>
                        <th class="p-4 text-left text-xs font-bold text-text-muted uppercase">Paket</th>
                        <th class="p-4 text-right text-xs font-bold text-text-muted uppercase">Tarif/Bulan</th>
                        <th class="p-4 text-right text-xs font-bold text-text-muted uppercase">Total Tagihan</th>
                        <th class="p-4 text-right text-xs font-bold text-text-muted uppercase">Total Dibayar</th>
                        <th class="p-4 text-right text-xs font-bold text-text-muted uppercase">Sisa Piutang</th>
                        <th class="p-4 text-center text-xs font-bold text-text-muted uppercase">Belum Lunas</th>
                        <th class="p-4 text-center text-xs font-bold text-text-muted uppercase">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    @php
                        $outstanding = (float)$customer->total_billed - (float)$customer->total_paid_amount;
                    @endphp
                    <tr class="border-t border-border-dark/50 hover:bg-surface-dark/30 transition cursor-pointer" onclick="window.location='{{ route('internet.show', $customer->id) }}'">
                        <td class="p-4 text-white font-mono text-sm">{{ $customer->customer_id }}</td>
                        <td class="p-4">
                            <div class="text-white font-medium">{{ $customer->name }}</div>
                            <div class="text-text-muted text-xs">{{ $customer->phone ?? $customer->email ?? '' }}</div>
                        </td>
                        <td class="p-4 text-white text-sm">{{ $customer->package_name }}</td>
                        <td class="p-4 text-right text-white font-mono text-sm">Rp {{ number_format($customer->monthly_rate, 0, ',', '.') }}</td>
                        <td class="p-4 text-right text-white font-mono text-sm">Rp {{ number_format($customer->total_billed, 0, ',', '.') }}</td>
                        <td class="p-4 text-right text-primary font-mono text-sm">Rp {{ number_format($customer->total_paid_amount, 0, ',', '.') }}</td>
                        <td class="p-4 text-right font-mono text-sm font-bold {{ $outstanding > 0 ? 'text-orange-400' : 'text-primary' }}">
                            Rp {{ number_format($outstanding, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            @if($customer->unpaid_count > 0)
                                <span class="px-2 py-1 rounded text-xs font-medium bg-orange-500/20 text-orange-400">{{ $customer->unpaid_count }}</span>
                            @else
                                <span class="text-primary text-sm">0</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($customer->status == 'active')
                                <span class="px-2 py-1 rounded text-xs font-medium bg-primary/20 text-primary">Aktif</span>
                            @elseif($customer->status == 'suspended')
                                <span class="px-2 py-1 rounded text-xs font-medium bg-orange-500/20 text-orange-400">Suspended</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs font-medium bg-red-500/20 text-red-400">Terminated</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-5xl mb-3">account_balance</span>
                            <p>Belum ada data piutang pelanggan internet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($customers->isNotEmpty())
                <tfoot>
                    <tr class="bg-surface-dark/50 border-t-2 border-primary/30">
                        <td colspan="4" class="p-4 text-right text-white font-bold">TOTAL</td>
                        <td class="p-4 text-right text-white font-mono font-bold">Rp {{ number_format($grandTotals['total_billed'], 0, ',', '.') }}</td>
                        <td class="p-4 text-right text-primary font-mono font-bold">Rp {{ number_format($grandTotals['total_paid'], 0, ',', '.') }}</td>
                        <td class="p-4 text-right text-orange-400 font-mono font-bold">Rp {{ number_format($grandTotals['total_outstanding'], 0, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-app-layout>
