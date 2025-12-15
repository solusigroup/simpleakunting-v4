<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Analisis Keuangan</h2>
                <p class="text-text-muted text-sm mt-1">Per {{ \Carbon\Carbon::parse($period['end_date'])->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <x-btn type="secondary" onclick="window.print()">
                    <span class="material-symbols-outlined text-xl">print</span>
                    Cetak
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Filter -->
    <div class="mb-6 p-4 rounded-xl border border-border-dark bg-surface-dark/30">
        <form action="{{ route('reports.financial-analysis') }}" method="GET" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm text-text-muted mb-2">Periode Laba Rugi</label>
                <input type="date" name="start_date" value="{{ $period['start_date'] }}" 
                       class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
            </div>
            <div class="flex-1">
                <label class="block text-sm text-text-muted mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $period['end_date'] }}" 
                       class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
            </div>
            <x-btn type="primary" type="submit">Filter</x-btn>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Aset</p>
            <p class="text-xl font-bold text-white">Rp {{ number_format($balances['total_assets'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Liabilitas</p>
            <p class="text-xl font-bold text-red-400">Rp {{ number_format($balances['total_liabilities'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Ekuitas</p>
            <p class="text-xl font-bold text-blue-400">Rp {{ number_format($balances['equity'], 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Laba Bersih</p>
            <p class="text-xl font-bold {{ $net_profit >= 0 ? 'text-green-400' : 'text-red-400' }}">Rp {{ number_format($net_profit, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Ratios Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Liquidity Ratios -->
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-primary/10">
                <h3 class="font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined">water_drop</span>
                    Rasio Likuiditas
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-text-muted text-sm">Current Ratio</span>
                        <span class="text-white font-bold">{{ $ratios['liquidity']['current_ratio'] ?? '-' }}</span>
                    </div>
                    <p class="text-xs text-text-muted">Kemampuan membayar hutang jangka pendek</p>
                    @if($ratios['liquidity']['current_ratio'])
                    <div class="mt-2 h-2 bg-surface-highlight rounded-full overflow-hidden">
                        <div class="h-full {{ $ratios['liquidity']['current_ratio'] >= 1 ? 'bg-green-500' : 'bg-red-500' }}" 
                             style="width: {{ min($ratios['liquidity']['current_ratio'] * 50, 100) }}%"></div>
                    </div>
                    @endif
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-text-muted text-sm">Quick Ratio</span>
                        <span class="text-white font-bold">{{ $ratios['liquidity']['quick_ratio'] ?? '-' }}</span>
                    </div>
                    <p class="text-xs text-text-muted">Likuiditas tanpa persediaan</p>
                    @if($ratios['liquidity']['quick_ratio'])
                    <div class="mt-2 h-2 bg-surface-highlight rounded-full overflow-hidden">
                        <div class="h-full {{ $ratios['liquidity']['quick_ratio'] >= 1 ? 'bg-green-500' : 'bg-red-500' }}" 
                             style="width: {{ min($ratios['liquidity']['quick_ratio'] * 50, 100) }}%"></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Profitability Ratios -->
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-green-500/10">
                <h3 class="font-bold text-green-400 flex items-center gap-2">
                    <span class="material-symbols-outlined">trending_up</span>
                    Rasio Profitabilitas
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-text-muted text-sm">Net Profit Margin</span>
                        <span class="text-white font-bold">{{ $ratios['profitability']['net_profit_margin'] ?? '-' }}%</span>
                    </div>
                    <p class="text-xs text-text-muted">Persentase laba dari pendapatan</p>
                    @if($ratios['profitability']['net_profit_margin'])
                    <div class="mt-2 h-2 bg-surface-highlight rounded-full overflow-hidden">
                        <div class="h-full {{ $ratios['profitability']['net_profit_margin'] >= 0 ? 'bg-green-500' : 'bg-red-500' }}" 
                             style="width: {{ min(abs($ratios['profitability']['net_profit_margin']), 100) }}%"></div>
                    </div>
                    @endif
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-text-muted text-sm">Return on Assets (ROA)</span>
                        <span class="text-white font-bold">{{ $ratios['profitability']['return_on_assets'] ?? '-' }}%</span>
                    </div>
                    <p class="text-xs text-text-muted">Efisiensi penggunaan aset</p>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-text-muted text-sm">Return on Equity (ROE)</span>
                        <span class="text-white font-bold">{{ $ratios['profitability']['return_on_equity'] ?? '-' }}%</span>
                    </div>
                    <p class="text-xs text-text-muted">Pengembalian modal pemilik</p>
                </div>
            </div>
        </div>

        <!-- Leverage Ratios -->
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-orange-500/10">
                <h3 class="font-bold text-orange-400 flex items-center gap-2">
                    <span class="material-symbols-outlined">balance</span>
                    Rasio Leverage
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-text-muted text-sm">Debt to Equity</span>
                        <span class="text-white font-bold">{{ $ratios['leverage']['debt_to_equity'] ?? '-' }}</span>
                    </div>
                    <p class="text-xs text-text-muted">Perbandingan hutang dengan modal</p>
                    @if($ratios['leverage']['debt_to_equity'])
                    <div class="mt-2 h-2 bg-surface-highlight rounded-full overflow-hidden">
                        <div class="h-full {{ $ratios['leverage']['debt_to_equity'] <= 1 ? 'bg-green-500' : 'bg-orange-500' }}" 
                             style="width: {{ min($ratios['leverage']['debt_to_equity'] * 50, 100) }}%"></div>
                    </div>
                    @endif
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-text-muted text-sm">Debt Ratio</span>
                        <span class="text-white font-bold">{{ $ratios['leverage']['debt_ratio'] ?? '-' }}%</span>
                    </div>
                    <p class="text-xs text-text-muted">Persentase aset yang dibiayai hutang</p>
                    @if($ratios['leverage']['debt_ratio'])
                    <div class="mt-2 h-2 bg-surface-highlight rounded-full overflow-hidden">
                        <div class="h-full {{ $ratios['leverage']['debt_ratio'] <= 50 ? 'bg-green-500' : 'bg-orange-500' }}" 
                             style="width: {{ min($ratios['leverage']['debt_ratio'], 100) }}%"></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Interpretation Guide -->
    <div class="mt-6 p-4 rounded-xl border border-border-dark bg-surface-dark/30">
        <h4 class="text-white font-bold mb-3">Panduan Interpretasi</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-primary font-medium mb-1">Likuiditas</p>
                <p class="text-text-muted">Current Ratio > 1 menunjukkan kemampuan bayar yang baik. Quick Ratio > 1 menunjukkan likuiditas sangat baik.</p>
            </div>
            <div>
                <p class="text-green-400 font-medium mb-1">Profitabilitas</p>
                <p class="text-text-muted">NPM positif menunjukkan operasi menguntungkan. ROA & ROE lebih tinggi lebih baik.</p>
            </div>
            <div>
                <p class="text-orange-400 font-medium mb-1">Leverage</p>
                <p class="text-text-muted">Debt to Equity < 1 menunjukkan struktur modal yang sehat. Debt Ratio < 50% ideal.</p>
            </div>
        </div>
    </div>
</x-app-layout>
