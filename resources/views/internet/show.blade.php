<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Detail Pelanggan</h2>
                <p class="text-text-muted text-sm mt-1">{{ $customer->customer_id }} — {{ $customer->name }}</p>
            </div>
            <a href="{{ route('internet.index') }}" class="text-text-muted hover:text-white transition">
                <span class="material-symbols-outlined">arrow_back</span> Kembali
            </a>
        </div>
    </x-slot>

    <!-- Customer Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-1 p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 rounded-full bg-primary/20 flex items-center justify-center">
                    <span class="text-primary text-2xl font-bold">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                </div>
                <div>
                    <h3 class="text-white text-lg font-bold">{{ $customer->name }}</h3>
                    <span class="text-text-muted text-sm font-mono">{{ $customer->customer_id }}</span>
                </div>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-text-muted">Paket</span><span class="text-white font-medium">{{ $customer->package_name }}</span></div>
                <div class="flex justify-between"><span class="text-text-muted">Tarif</span><span class="text-white font-mono">Rp {{ number_format($customer->monthly_rate, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-text-muted">Tgl Tagih</span><span class="text-white">Tanggal {{ $customer->billing_date }}</span></div>
                <div class="flex justify-between"><span class="text-text-muted">Telepon</span><span class="text-white">{{ $customer->phone ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-text-muted">Email</span><span class="text-white">{{ $customer->email ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-text-muted">Alamat</span><span class="text-white text-right max-w-[180px]">{{ $customer->address ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-text-muted">Aktif Sejak</span><span class="text-white">{{ $customer->activated_at ? $customer->activated_at->format('d/m/Y') : '-' }}</span></div>
                <div class="flex justify-between"><span class="text-text-muted">Status</span>
                    @if($customer->status == 'active')
                        <span class="px-2 py-1 rounded text-xs font-medium bg-primary/20 text-primary">Aktif</span>
                    @elseif($customer->status == 'suspended')
                        <span class="px-2 py-1 rounded text-xs font-medium bg-orange-500/20 text-orange-400">Suspended</span>
                    @else
                        <span class="px-2 py-1 rounded text-xs font-medium bg-red-500/20 text-red-400">Terminated</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <!-- Summary Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="p-4 rounded-2xl border border-border-dark bg-surface-dark/30">
                    <div class="text-text-muted text-xs">Total Tagihan</div>
                    <div class="text-lg font-bold text-white mt-1">Rp {{ number_format($summary['total_billed'], 0, ',', '.') }}</div>
                </div>
                <div class="p-4 rounded-2xl border border-border-dark bg-surface-dark/30">
                    <div class="text-text-muted text-xs">Total Dibayar</div>
                    <div class="text-lg font-bold text-primary mt-1">Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}</div>
                </div>
                <div class="p-4 rounded-2xl border border-border-dark bg-surface-dark/30">
                    <div class="text-text-muted text-xs">Sisa Piutang</div>
                    <div class="text-lg font-bold {{ $summary['outstanding'] > 0 ? 'text-orange-400' : 'text-primary' }} mt-1">Rp {{ number_format($summary['outstanding'], 0, ',', '.') }}</div>
                </div>
                <div class="p-4 rounded-2xl border border-border-dark bg-surface-dark/30">
                    <div class="text-text-muted text-xs">Jatuh Tempo</div>
                    <div class="text-lg font-bold {{ $summary['overdue_count'] > 0 ? 'text-red-400' : 'text-white' }} mt-1">{{ $summary['overdue_count'] }} tagihan</div>
                </div>
            </div>

            <!-- Billing History -->
            <div class="rounded-2xl border border-border-dark overflow-hidden">
                <div class="px-6 py-4 bg-surface-dark/50 border-b border-border-dark">
                    <h3 class="text-white font-bold">Riwayat Tagihan & Pembayaran</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-surface-dark/30">
                                <th class="p-3 text-left text-xs font-bold text-text-muted uppercase">Periode</th>
                                <th class="p-3 text-right text-xs font-bold text-text-muted uppercase">Tagihan</th>
                                <th class="p-3 text-right text-xs font-bold text-text-muted uppercase">Dibayar</th>
                                <th class="p-3 text-right text-xs font-bold text-text-muted uppercase">Sisa</th>
                                <th class="p-3 text-center text-xs font-bold text-text-muted uppercase">Status</th>
                                <th class="p-3 text-left text-xs font-bold text-text-muted uppercase">Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($billings as $billing)
                            <tr class="border-t border-border-dark/50 hover:bg-surface-dark/30">
                                <td class="p-3 text-white text-sm">{{ $billing->period_label }}</td>
                                <td class="p-3 text-right text-white font-mono text-sm">Rp {{ number_format($billing->amount, 0, ',', '.') }}</td>
                                <td class="p-3 text-right text-primary font-mono text-sm">Rp {{ number_format($billing->paid_amount, 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-mono text-sm {{ $billing->remaining_amount > 0 ? 'text-orange-400' : 'text-primary' }}">
                                    Rp {{ number_format($billing->remaining_amount, 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-center">
                                    @if($billing->status == 'paid')
                                        <span class="px-2 py-1 rounded text-xs bg-primary/20 text-primary">Lunas</span>
                                    @elseif($billing->status == 'partial')
                                        <span class="px-2 py-1 rounded text-xs bg-blue-500/20 text-blue-400">Sebagian</span>
                                    @elseif($billing->status == 'overdue')
                                        <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-400">Jatuh Tempo</span>
                                    @else
                                        <span class="px-2 py-1 rounded text-xs bg-orange-500/20 text-orange-400">Belum Bayar</span>
                                    @endif
                                </td>
                                <td class="p-3 text-xs">
                                    @foreach($billing->payments as $pay)
                                    <div class="text-text-muted">
                                        {{ $pay->payment_date->format('d/m/Y') }} — 
                                        <span class="text-primary font-mono">Rp {{ number_format($pay->amount, 0, ',', '.') }}</span>
                                        @if($pay->discount > 0)
                                            <span class="text-orange-400 font-mono text-[10px]">(Pot. Rp {{ number_format($pay->discount, 0, ',', '.') }})</span>
                                        @endif
                                        ({{ $pay->cashBankAccount->name ?? '-' }})
                                    </div>
                                    @endforeach
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="p-8 text-center text-text-muted">Belum ada riwayat tagihan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $billings->links() }}
        </div>
    </div>
</x-app-layout>
