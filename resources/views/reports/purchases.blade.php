<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Laporan Pembelian</h2>
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
        <form action="{{ route('reports.purchases') }}" method="GET" class="flex flex-wrap items-end gap-4">
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
                <label class="block text-sm text-text-muted mb-2">Supplier</label>
                <select name="contact_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
                    <option value="">Semua</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('contact_id') == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm text-text-muted mb-2">Status</label>
                <select name="status" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
                    <option value="">Semua</option>
                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Diterima</option>
                    <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="Partial" {{ request('status') == 'Partial' ? 'selected' : '' }}>Partial</option>
                </select>
            </div>
            <button type="submit" class="px-6 h-11 rounded-full font-medium bg-primary hover:bg-primary-dark text-background-dark transition flex items-center gap-2">
                <span class="material-symbols-outlined">filter_list</span>
                Filter
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-surface-dark/50 rounded-xl border border-border-dark p-4">
            <div class="text-text-muted text-sm">Total Pembelian</div>
            <div class="text-2xl font-bold text-accent-red mt-1">Rp {{ number_format($summary['total_purchases'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-surface-dark/50 rounded-xl border border-border-dark p-4">
            <div class="text-text-muted text-sm">Jumlah Invoice</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $summary['count'] }}</div>
        </div>
        <div class="bg-surface-dark/50 rounded-xl border border-border-dark p-4">
            <div class="text-text-muted text-sm">Total Pajak</div>
            <div class="text-2xl font-bold text-blue-400 mt-1">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-surface-dark/50 rounded-xl border border-border-dark p-4">
            <div class="text-text-muted text-sm">Total Diskon</div>
            <div class="text-2xl font-bold text-orange-400 mt-1">Rp {{ number_format($summary['total_discount'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- By Supplier Summary -->
    @if($by_supplier->count() > 0)
    <div class="mb-6">
        <h3 class="text-lg font-bold text-white mb-3">Pembelian per Supplier</h3>
        <div class="rounded-xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border-dark bg-surface-highlight/30">
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Supplier</th>
                        <th class="text-center text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Jumlah Invoice</th>
                        <th class="text-right text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @foreach($by_supplier as $item)
                    <tr class="hover:bg-surface-highlight/20 transition">
                        <td class="px-4 py-3 text-sm text-white">{{ $item['contact_name'] }}</td>
                        <td class="px-4 py-3 text-sm text-center text-text-muted">{{ $item['count'] }}</td>
                        <td class="px-4 py-3 text-sm text-right text-white font-mono">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Invoices Table -->
    <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
        <div class="px-4 py-3 border-b border-border-dark">
            <h3 class="font-bold text-white">Detail Invoice</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border-dark bg-surface-highlight/30">
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Tanggal</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">No. Invoice</th>
                        <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Supplier</th>
                        <th class="text-center text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-right text-xs font-medium text-text-muted uppercase tracking-wider px-4 py-3">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-surface-highlight/20 transition">
                        <td class="px-4 py-3 text-sm text-white">{{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-primary font-mono">{{ $invoice->number }}</td>
                        <td class="px-4 py-3 text-sm text-text-muted">{{ $invoice->contact->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center">
                            @php
                            $statusClass = match($invoice->status) {
                                'Paid' => 'bg-green-500/20 text-green-400',
                                'Received' => 'bg-blue-500/20 text-blue-400',
                                'Partial' => 'bg-yellow-500/20 text-yellow-400',
                                default => 'bg-gray-500/20 text-gray-400',
                            };
                            @endphp
                            <span class="px-2 py-1 rounded-lg text-xs font-medium {{ $statusClass }}">{{ $invoice->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-white font-mono">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-2">local_shipping</span>
                            <p>Tidak ada data pembelian dalam periode ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
