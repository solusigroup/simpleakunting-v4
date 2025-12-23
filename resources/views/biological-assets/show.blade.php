<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('biological-assets.index') }}" class="text-text-muted hover:text-white transition">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white font-display">{{ $asset->name }}</h2>
                    <p class="text-text-muted text-sm mt-1">{{ $asset->code }} • {{ $asset->getCategoryLabel() }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="window.location.href='{{ route('biological-assets.index') }}'">
                    <span class="material-symbols-outlined">list</span>
                    Daftar Aset
                </x-btn>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Asset Summary Card -->
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl bg-primary/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-3xl text-primary">
                                @if($asset->category === 'livestock') pets
                                @elseif($asset->category === 'plantation') agriculture
                                @elseif($asset->category === 'aquaculture') phishing
                                @elseif($asset->category === 'forestry') forest
                                @else eco
                                @endif
                            </span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">{{ $asset->name }}</h3>
                            <p class="text-text-muted">{{ $asset->getCategoryLabel() }} • {{ $asset->getAssetTypeLabel() }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $asset->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                            {{ $asset->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $asset->maturity_status === 'mature' ? 'bg-blue-500/20 text-blue-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                            {{ $asset->getMaturityStatusLabel() }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-surface-highlight/30">
                        <p class="text-text-muted text-sm">Kuantitas</p>
                        <p class="text-xl font-bold text-white">{{ number_format($asset->quantity, 2) }} {{ $asset->unit }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-surface-highlight/30">
                        <p class="text-text-muted text-sm">Nilai Tercatat</p>
                        <p class="text-xl font-bold text-primary">Rp {{ number_format($asset->carrying_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-surface-highlight/30">
                        <p class="text-text-muted text-sm">Nilai per Unit</p>
                        <p class="text-xl font-bold text-green-400">Rp {{ number_format($asset->getUnitValue(), 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-surface-highlight/30">
                        <p class="text-text-muted text-sm">Lokasi</p>
                        <p class="text-xl font-bold text-white">{{ $asset->location ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Financial Details -->
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">payments</span>
                    Informasi Keuangan
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="p-3 rounded-xl bg-surface-highlight/30">
                        <p class="text-text-muted text-sm">Biaya Perolehan</p>
                        <p class="text-white font-bold">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-surface-highlight/30">
                        <p class="text-text-muted text-sm">Tanggal Perolehan</p>
                        <p class="text-white font-bold">{{ $asset->acquisition_date->format('d M Y') }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-surface-highlight/30">
                        <p class="text-text-muted text-sm">Metode Penilaian</p>
                        <p class="text-white font-bold">{{ $asset->valuation_method === 'fair_value' ? 'Nilai Wajar' : 'Biaya Perolehan' }}</p>
                    </div>
                    @if($asset->valuation_method === 'fair_value')
                    <div class="p-3 rounded-xl bg-surface-highlight/30">
                        <p class="text-text-muted text-sm">Nilai Wajar Saat Ini</p>
                        <p class="text-primary font-bold">Rp {{ number_format($asset->current_fair_value, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-surface-highlight/30">
                        <p class="text-text-muted text-sm">Biaya untuk Menjual</p>
                        <p class="text-orange-400 font-bold">Rp {{ number_format($asset->cost_to_sell, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-surface-highlight/30">
                        <p class="text-text-muted text-sm">Tanggal Valuasi</p>
                        <p class="text-white font-bold">{{ $asset->valuation_date ? $asset->valuation_date->format('d M Y') : '-' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Transformations History -->
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-400">sync_alt</span>
                    Riwayat Transformasi
                </h3>
                @if($transformations->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-border-dark">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm text-text-muted">Tanggal</th>
                                <th class="px-4 py-2 text-left text-sm text-text-muted">Jenis</th>
                                <th class="px-4 py-2 text-right text-sm text-text-muted">Perubahan</th>
                                <th class="px-4 py-2 text-left text-sm text-text-muted">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark/50">
                            @foreach($transformations as $t)
                            <tr class="hover:bg-surface-highlight/30">
                                <td class="px-4 py-3 text-white">{{ $t->transaction_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs
                                        @if($t->transformation_type === 'growth') bg-green-500/20 text-green-400
                                        @elseif($t->transformation_type === 'harvest') bg-orange-500/20 text-orange-400
                                        @elseif($t->transformation_type === 'death') bg-red-500/20 text-red-400
                                        @else bg-blue-500/20 text-blue-400
                                        @endif">
                                        {{ ucfirst($t->transformation_type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right {{ $t->quantity_change >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $t->quantity_change >= 0 ? '+' : '' }}{{ number_format($t->quantity_change, 2) }}
                                </td>
                                <td class="px-4 py-3 text-text-muted">{{ $t->description ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-text-muted py-8">Belum ada riwayat transformasi</p>
                @endif
            </div>

            <!-- Valuations History -->
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-400">trending_up</span>
                    Riwayat Penilaian Nilai Wajar
                </h3>
                @if($valuations->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-border-dark">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm text-text-muted">Tanggal</th>
                                <th class="px-4 py-2 text-right text-sm text-text-muted">Nilai Sebelum</th>
                                <th class="px-4 py-2 text-right text-sm text-text-muted">Nilai Sekarang</th>
                                <th class="px-4 py-2 text-right text-sm text-text-muted">Perubahan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark/50">
                            @foreach($valuations as $v)
                            <tr class="hover:bg-surface-highlight/30">
                                <td class="px-4 py-3 text-white">{{ $v->valuation_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right text-text-muted">Rp {{ number_format($v->previous_fair_value, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-white">Rp {{ number_format($v->current_fair_value, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right {{ $v->fair_value_change >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $v->fair_value_change >= 0 ? '+' : '' }}Rp {{ number_format($v->fair_value_change, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-center text-text-muted py-8">Belum ada riwayat penilaian nilai wajar</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4">Aksi Cepat</h3>
                <div class="space-y-3">
                    <button onclick="window.location.href='{{ route('biological-assets.index') }}'" class="w-full px-4 py-3 rounded-xl bg-blue-500/20 text-blue-400 hover:bg-blue-500/30 transition flex items-center gap-2">
                        <span class="material-symbols-outlined">trending_up</span>
                        Penilaian Nilai Wajar
                    </button>
                    <button onclick="window.location.href='{{ route('biological-assets.index') }}'" class="w-full px-4 py-3 rounded-xl bg-green-500/20 text-green-400 hover:bg-green-500/30 transition flex items-center gap-2">
                        <span class="material-symbols-outlined">sync_alt</span>
                        Catat Transformasi
                    </button>
                    <button onclick="window.location.href='{{ route('biological-assets.index') }}'" class="w-full px-4 py-3 rounded-xl bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 transition flex items-center gap-2">
                        <span class="material-symbols-outlined">agriculture</span>
                        Catat Panen
                    </button>
                </div>
            </div>

            <!-- Account Info -->
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4">Akun Terkait</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-text-muted text-sm">Akun Aset Biologis</p>
                        @if($asset->account)
                        <p class="text-white font-medium">{{ $asset->account->code }} - {{ $asset->account->name }}</p>
                        @else
                        <p class="text-text-muted">-</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-text-muted text-sm">Akun Keuntungan/Kerugian Nilai Wajar</p>
                        @if($asset->fairValueAccount)
                        <p class="text-white font-medium">{{ $asset->fairValueAccount->code }} - {{ $asset->fairValueAccount->name }}</p>
                        @else
                        <p class="text-text-muted">Belum diatur</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Harvest Summary -->
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4">Ringkasan Panen</h3>
                <div class="text-center py-4">
                    <p class="text-3xl font-bold text-orange-400">{{ number_format($totalHarvested, 2) }}</p>
                    <p class="text-text-muted">Total Dipanen</p>
                </div>
                @if($produce->count() > 0)
                <div class="mt-4 space-y-2">
                    @foreach($produce->take(3) as $p)
                    <div class="p-3 rounded-xl bg-surface-highlight/30 flex justify-between items-center">
                        <div>
                            <p class="text-white text-sm">{{ $p->product_name }}</p>
                            <p class="text-xs text-text-muted">{{ $p->harvest_date->format('d M Y') }}</p>
                        </div>
                        <p class="text-orange-400">{{ number_format($p->quantity, 2) }} {{ $p->unit }}</p>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-center text-text-muted text-sm mt-4">Belum ada hasil panen</p>
                @endif
            </div>

            <!-- Notes -->
            @if($asset->notes)
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4">Catatan</h3>
                <p class="text-text-muted">{{ $asset->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
