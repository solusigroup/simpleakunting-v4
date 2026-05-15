<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-white font-display">Laporan Bank Sampah</h2>
        <p class="text-text-muted text-sm mt-1">Akses berbagai laporan operasional dan keuangan bank sampah</p>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Stock Report -->
        <a href="{{ route('waste.reports.stock') }}" class="group">
            <div class="rounded-2xl border border-border-dark bg-surface-dark/30 p-6 hover:bg-surface-highlight transition h-full flex flex-col">
                <div class="w-12 h-12 bg-primary/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <span class="material-symbols-outlined text-primary">inventory_2</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Laporan Stok Sampah</h3>
                <p class="text-text-muted text-sm flex-1">Lihat sisa stok sampah di gudang dan taksiran nilainya.</p>
                <div class="mt-4 text-primary text-sm font-bold flex items-center gap-1">
                    Buka Laporan
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </div>
            </div>
        </a>

        <!-- Collectors Balance -->
        <a href="{{ route('waste.reports.collectors') }}" class="group">
            <div class="rounded-2xl border border-border-dark bg-surface-dark/30 p-6 hover:bg-surface-highlight transition h-full flex flex-col">
                <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <span class="material-symbols-outlined text-blue-400">account_balance_wallet</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Saldo Tabungan Nasabah</h3>
                <p class="text-text-muted text-sm flex-1">Daftar saldo akhir seluruh nasabah bank sampah.</p>
                <div class="mt-4 text-primary text-sm font-bold flex items-center gap-1">
                    Buka Laporan
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </div>
            </div>
        </a>

        <!-- Detailed Ledger -->
        <a href="{{ route('waste.reports.ledger') }}" class="group">
            <div class="rounded-2xl border border-border-dark bg-surface-dark/30 p-6 hover:bg-surface-highlight transition h-full flex flex-col">
                <div class="w-12 h-12 bg-orange-500/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                    <span class="material-symbols-outlined text-orange-400">list_alt</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Buku Bantu Per Nasabah</h3>
                <p class="text-text-muted text-sm flex-1">Detail mutasi setoran dan penarikan untuk satu nasabah tertentu.</p>
                <div class="mt-4 text-primary text-sm font-bold flex items-center gap-1">
                    Buka Laporan
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </div>
            </div>
        </a>
    </div>
</x-app-layout>
