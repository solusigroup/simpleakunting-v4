<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('waste.collectors.index') }}" class="text-white hover:text-primary transition flex-shrink-0">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Detail Nasabah</h2>
                <p class="text-text-muted text-sm mt-1">Data profil dan riwayat transaksi nasabah</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Info -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-border-dark bg-surface-dark/30 p-8 text-center">
                <div class="w-20 h-20 bg-primary/20 rounded-full flex items-center justify-center mx-auto mb-4 border border-primary/30">
                    <span class="text-3xl font-bold text-primary">{{ substr($collector->name, 0, 1) }}</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-1">{{ $collector->name }}</h3>
                <p class="text-text-muted text-sm mb-6">ID: {{ $collector->collector_number }}</p>

                <div class="bg-surface-highlight border border-border-dark rounded-2xl p-4 mb-6">
                    <p class="text-xs text-text-muted mb-1">Saldo Tabungan Saat Ini</p>
                    <p class="text-2xl font-bold text-primary">Rp {{ number_format($collector->balance, 0, ',', '.') }}</p>
                </div>

                <div class="space-y-4 text-left border-t border-border-dark/50 pt-6">
                    <div>
                        <p class="text-[10px] text-text-muted uppercase tracking-wider font-bold">No. Telepon</p>
                        <p class="text-white">{{ $collector->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-text-muted uppercase tracking-wider font-bold">Alamat</p>
                        <p class="text-white text-sm">{{ $collector->address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <x-btn href="{{ route('waste.deposits.create', ['collector_id' => $collector->id]) }}" type="primary" class="justify-center py-4">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Catat Setoran
                </x-btn>
                <x-btn href="{{ route('waste.withdrawals.create', ['collector_id' => $collector->id]) }}" type="secondary" class="justify-center py-4 border-orange-500/50 text-orange-400">
                    <span class="material-symbols-outlined text-xl">payments</span>
                    Tarik Tabungan
                </x-btn>
            </div>
        </div>

        <!-- Main Content (History) -->
        <div class="lg:col-span-2" x-data="{ tab: 'deposits' }">
            <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
                <div class="flex border-b border-border-dark bg-surface-dark/50">
                    <button @click="tab = 'deposits'" :class="tab === 'deposits' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-white'" class="flex-1 py-4 text-sm font-bold border-b-2 transition">
                        Riwayat Setoran
                    </button>
                    <button @click="tab = 'withdrawals'" :class="tab === 'withdrawals' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-white'" class="flex-1 py-4 text-sm font-bold border-b-2 transition">
                        Riwayat Penarikan
                    </button>
                </div>

                <div class="p-0">
                    <!-- Deposits Tab -->
                    <div x-show="tab === 'deposits'">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-text-muted text-[10px] uppercase tracking-wider border-b border-border-dark/30">
                                    <th class="px-6 py-4 font-semibold">Tanggal</th>
                                    <th class="px-6 py-4 font-semibold">Jenis Sampah</th>
                                    <th class="px-6 py-4 font-semibold">Berat</th>
                                    <th class="px-6 py-4 font-semibold text-right">Total (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-dark/20">
                                @forelse($collector->deposits as $dep)
                                <tr class="hover:bg-surface-highlight/20 transition">
                                    <td class="px-6 py-4 text-text-muted text-sm">{{ $dep->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-white text-sm font-medium">{{ $dep->category->name }}</td>
                                    <td class="px-6 py-4 text-text-muted text-sm">{{ number_format($dep->weight, 2) }} {{ $dep->category->unit }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-primary text-sm">+Rp {{ number_format($dep->total_amount, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-text-muted">Belum ada setoran</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Withdrawals Tab -->
                    <div x-show="tab === 'withdrawals'" x-cloak>
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-text-muted text-[10px] uppercase tracking-wider border-b border-border-dark/30">
                                    <th class="px-6 py-4 font-semibold">Tanggal</th>
                                    <th class="px-6 py-4 font-semibold">Keterangan</th>
                                    <th class="px-6 py-4 font-semibold text-right">Jumlah (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-dark/20">
                                @forelse($collector->withdrawals as $wit)
                                <tr class="hover:bg-surface-highlight/20 transition">
                                    <td class="px-6 py-4 text-text-muted text-sm">{{ $wit->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-white text-sm font-medium">{{ $wit->note ?? 'Penarikan Tunai' }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-accent-red text-sm">-Rp {{ number_format($wit->amount, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-text-muted">Belum ada penarikan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
