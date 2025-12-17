<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Laporan Arus Kas</h2>
                <p class="text-text-muted text-sm mt-1">Periode {{ \Carbon\Carbon::parse($period['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end_date'])->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-3" x-data="{ exportOpen: false }">
                <!-- Export Dropdown -->
                <div class="relative">
                    <button @click="exportOpen = !exportOpen" 
                            class="px-4 py-2 rounded-full border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition flex items-center gap-2">
                        <span class="material-symbols-outlined">download</span>
                        <span class="hidden sm:inline">Export</span>
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </button>
                    <div x-show="exportOpen" @click.away="exportOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:leave="transition ease-in duration-150"
                            class="absolute right-0 mt-2 w-48 rounded-xl border border-border-dark bg-surface-dark shadow-xl z-10">
                        <a href="{{ route('reports.cash-flow.export-pdf', ['start_date' => $period['start_date'], 'end_date' => $period['end_date']]) }}" 
                           class="flex items-center gap-3 px-4 py-3 hover:bg-surface-highlight rounded-t-xl transition">
                            <span class="material-symbols-outlined text-accent-red">picture_as_pdf</span>
                            <span class="text-white">Export PDF</span>
                        </a>
                        <a href="#" onclick="window.print(); return false;" 
                           class="flex items-center gap-3 px-4 py-3 hover:bg-surface-highlight rounded-b-xl border-t border-border-dark transition">
                            <span class="material-symbols-outlined text-text-muted">print</span>
                            <span class="text-white">Print</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Filter -->
    <div class="mb-6 p-4 rounded-xl border border-border-dark bg-surface-dark/30">
        <form action="{{ route('reports.cash-flow') }}" method="GET" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm text-text-muted mb-2">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $period['start_date'] }}" 
                       class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
            </div>
            <div class="flex-1">
                <label class="block text-sm text-text-muted mb-2">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $period['end_date'] }}" 
                       class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
            </div>
            <button type="submit" class="px-6 h-12 rounded-full font-medium bg-primary hover:bg-primary-dark text-background-dark transition flex items-center gap-2">
                <span class="material-symbols-outlined">filter_list</span>
                Filter
            </button>
        </form>
    </div>

    <!-- Cash Flow Statement -->
    <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
        <!-- Beginning Balance -->
        <div class="px-6 py-4 border-b border-border-dark bg-surface-highlight/30">
            <div class="flex items-center justify-between">
                <span class="text-white font-medium">Saldo Kas Awal</span>
                <span class="text-white font-bold">Rp {{ number_format($beginning_balance, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Operating Activities -->
        <div class="border-b border-border-dark">
            <div class="px-6 py-4 bg-primary/10">
                <h3 class="font-bold text-primary">Aktivitas Operasional</h3>
            </div>
            <div class="px-6 py-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-text-muted">Penerimaan Kas</span>
                    <span class="text-green-400">Rp {{ number_format($operating['inflow'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-text-muted">Pengeluaran Kas</span>
                    <span class="text-red-400">(Rp {{ number_format($operating['outflow'], 0, ',', '.') }})</span>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-border-dark/50">
                    <span class="text-white font-medium">Arus Kas Bersih - Operasional</span>
                    <span class="font-bold {{ $operating['net'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        Rp {{ number_format($operating['net'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Investing Activities -->
        <div class="border-b border-border-dark">
            <div class="px-6 py-4 bg-blue-500/10">
                <h3 class="font-bold text-blue-400">Aktivitas Investasi</h3>
            </div>
            <div class="px-6 py-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-text-muted">Penerimaan dari Investasi</span>
                    <span class="text-green-400">Rp {{ number_format($investing['inflow'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-text-muted">Pengeluaran untuk Investasi</span>
                    <span class="text-red-400">(Rp {{ number_format($investing['outflow'], 0, ',', '.') }})</span>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-border-dark/50">
                    <span class="text-white font-medium">Arus Kas Bersih - Investasi</span>
                    <span class="font-bold {{ $investing['net'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        Rp {{ number_format($investing['net'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Financing Activities -->
        <div class="border-b border-border-dark">
            <div class="px-6 py-4 bg-purple-500/10">
                <h3 class="font-bold text-purple-400">Aktivitas Pendanaan</h3>
            </div>
            <div class="px-6 py-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-text-muted">Penerimaan dari Pendanaan</span>
                    <span class="text-green-400">Rp {{ number_format($financing['inflow'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-text-muted">Pengeluaran untuk Pendanaan</span>
                    <span class="text-red-400">(Rp {{ number_format($financing['outflow'], 0, ',', '.') }})</span>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-border-dark/50">
                    <span class="text-white font-medium">Arus Kas Bersih - Pendanaan</span>
                    <span class="font-bold {{ $financing['net'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        Rp {{ number_format($financing['net'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Net Change -->
        <div class="px-6 py-4 border-b border-border-dark bg-surface-highlight/20">
            <div class="flex items-center justify-between">
                <span class="text-white font-medium">Perubahan Bersih Kas</span>
                <span class="font-bold text-lg {{ $net_change >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    Rp {{ number_format($net_change, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Ending Balance -->
        <div class="px-6 py-4 bg-primary/20">
            <div class="flex items-center justify-between">
                <span class="text-white font-bold">Saldo Kas Akhir</span>
                <span class="text-primary font-bold text-xl">Rp {{ number_format($ending_balance, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</x-app-layout>
