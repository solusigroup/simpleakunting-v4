<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Aset Biologis (PSAK 69)</h2>
                <p class="text-text-muted text-sm mt-1">Kelola aset biologis dan produk agrikultur sesuai PSAK 69</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="window.location.href='/reports/biological-reconciliation'">
                    <span class="material-symbols-outlined text-xl">assessment</span>
                    Laporan PSAK 69
                </x-btn>
                <x-btn type="primary" onclick="openCreateModal()">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Tambah Aset Biologis
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Aset Biologis</p>
            <p class="text-2xl font-bold text-white">{{ $assets->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Nilai Tercatat</p>
            <p class="text-2xl font-bold text-primary">Rp {{ number_format($assets->sum('carrying_amount'), 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Aset Dewasa</p>
            <p class="text-2xl font-bold text-green-400">{{ $assets->where('maturity_status', 'mature')->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Aset Belum Dewasa</p>
            <p class="text-2xl font-bold text-orange-400">{{ $assets->where('maturity_status', 'immature')->count() }}</p>
        </div>
    </div>

    <!-- Assets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($assets as $asset)
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition {{ !$asset->is_active ? 'opacity-50' : '' }}">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">
                        @if($asset->category === 'livestock') pets
                        @elseif($asset->category === 'plantation') agriculture
                        @elseif($asset->category === 'aquaculture') phishing
                        @elseif($asset->category === 'forestry') forest
                        @else eco
                        @endif
                    </span>
                </div>
                <div class="flex flex-col gap-1 items-end">
                    <span class="px-2 py-1 rounded text-xs font-medium {{ $asset->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                        {{ $asset->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <span class="px-2 py-1 rounded text-xs font-medium {{ $asset->maturity_status === 'mature' ? 'bg-blue-500/20 text-blue-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                        {{ $asset->getMaturityStatusLabel() }}
                    </span>
                </div>
            </div>
            <div class="mb-3">
                <span class="text-xs text-text-muted font-mono">{{ $asset->code }}</span>
                <h3 class="text-white font-bold">{{ $asset->name }}</h3>
                <p class="text-xs text-text-muted mt-1">{{ $asset->getCategoryLabel() }} • {{ $asset->getAssetTypeLabel() }}</p>
            </div>
            <div class="space-y-2 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-text-muted">Kuantitas</span>
                    <span class="text-white font-medium">{{ number_format($asset->quantity, 2) }} {{ $asset->unit }}</span>
                </div>
                @if($asset->valuation_method === 'fair_value')
                <div class="flex justify-between">
                    <span class="text-text-muted">Nilai Wajar</span>
                    <span class="text-primary">Rp {{ number_format($asset->current_fair_value, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Biaya Jual</span>
                    <span class="text-orange-400">Rp {{ number_format($asset->cost_to_sell, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between font-medium">
                    <span class="text-text-muted">Nilai Tercatat</span>
                    <span class="text-green-400">Rp {{ number_format($asset->carrying_amount, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="pt-3 border-t border-border-dark/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-text-muted">Nilai per Unit</p>
                        <p class="text-primary font-medium">Rp {{ number_format($asset->getUnitValue(), 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-text-muted">Lokasi</p>
                        <p class="text-white font-medium">{{ $asset->location ?? '-' }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-border-dark/50 flex justify-between">
                <div class="flex gap-2">
                    <button onclick="viewAsset({{ $asset->id }})" class="text-text-muted hover:text-primary" title="Lihat Detail">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <button onclick="openValuationModal({{ json_encode($asset) }})" class="text-text-muted hover:text-blue-400" title="Penilaian Nilai Wajar">
                        <span class="material-symbols-outlined">trending_up</span>
                    </button>
                    <button onclick="openTransformationModal({{ json_encode($asset) }})" class="text-text-muted hover:text-green-400" title="Transformasi">
                        <span class="material-symbols-outlined">sync_alt</span>
                    </button>
                    <button onclick="openHarvestModal({{ json_encode($asset) }})" class="text-text-muted hover:text-orange-400" title="Panen">
                        <span class="material-symbols-outlined">agriculture</span>
                    </button>
                </div>
                <button onclick="editAsset({{ json_encode($asset) }})" class="text-text-muted hover:text-primary">
                    <span class="material-symbols-outlined">edit</span>
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-text-muted">
            <span class="material-symbols-outlined text-5xl mb-3">eco</span>
            <p>Belum ada data aset biologis</p>
            <p class="text-sm mt-1">Klik tombol "Tambah Aset Biologis" untuk menambahkan aset biologis baru</p>
        </div>
        @endforelse
    </div>

    <!-- Create/Edit Modal -->
    <div id="assetModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between sticky top-0 bg-background-dark">
                    <h3 class="text-lg font-bold text-white" id="modalTitle">Tambah Aset Biologis</h3>
                    <button onclick="closeModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="assetForm" class="p-6 space-y-4">
                    <input type="hidden" id="assetId">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Kode *</label>
                            <input type="text" id="code" required
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Kategori *</label>
                            <select id="category" required
                                    class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                <option value="livestock">Peternakan</option>
                                <option value="plantation">Perkebunan</option>
                                <option value="aquaculture">Perikanan/Budidaya</option>
                                <option value="forestry">Kehutanan</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama Aset *</label>
                        <input type="text" id="name" required
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Jenis Aset *</label>
                            <select id="asset_type" required
                                    class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                <option value="consumable">Habis Pakai</option>
                                <option value="bearer">Penghasil</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Status Kedewasaan *</label>
                            <select id="maturity_status" required
                                    class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                <option value="immature">Belum Dewasa</option>
                                <option value="mature">Dewasa/Produktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Kuantitas *</label>
                            <input type="number" id="quantity" required min="0" step="0.01"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Satuan *</label>
                            <input type="text" id="unit" required placeholder="ekor, pohon, kg, dll"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Tanggal Perolehan *</label>
                            <input type="date" id="acquisition_date" required
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Biaya Perolehan *</label>
                            <input type="number" id="acquisition_cost" required min="0"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Metode Penilaian *</label>
                        <select id="valuation_method" required onchange="toggleFairValueFields()"
                                class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="fair_value">Nilai Wajar</option>
                            <option value="cost_model">Biaya Perolehan</option>
                        </select>
                    </div>

                    <div id="fairValueFields">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-text-muted mb-2">Nilai Wajar Saat Ini</label>
                                <input type="number" id="current_fair_value" min="0"
                                       class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-text-muted mb-2">Biaya untuk Menjual</label>
                                <input type="number" id="cost_to_sell" min="0" value="0"
                                       class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Lokasi</label>
                        <input type="text" id="location" placeholder="Kandang A, Lahan B, dll"
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Catatan</label>
                        <textarea id="notes" rows="3"
                                  class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Akun Aset Biologis *</label>
                        <select id="coa_id" required
                                class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Akun</option>
                            @foreach($assetAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Akun Keuntungan/Kerugian Nilai Wajar</label>
                        <select id="fair_value_gain_loss_coa_id"
                                class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Akun</option>
                            @foreach($fairValueAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="activeToggle" class="hidden">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="is_active" class="form-checkbox rounded bg-surface-dark border-border-dark text-primary focus:ring-primary">
                            <span class="text-white">Aset Aktif</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <x-btn type="secondary" onclick="closeModal()">Batal</x-btn>
                        <x-btn type="primary" type="submit">Simpan</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('biological-assets.modals')

    @push('scripts')
    <script src="{{ asset('js/biological-assets.js') }}"></script>
    @endpush
</x-app-layout>
