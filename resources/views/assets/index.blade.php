<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Aset Tetap</h2>
                <p class="text-text-muted text-sm mt-1">Kelola master data aset tetap dan penyusutan</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="openDepreciationModal()">
                    <span class="material-symbols-outlined text-xl">calculate</span>
                    Jalankan Depresiasi
                </x-btn>
                <x-btn type="secondary" onclick="window.location.href='/assets/export'">
                    <span class="material-symbols-outlined text-xl">download</span>
                    Export Excel
                </x-btn>
                <x-btn type="secondary" onclick="window.location.href='/assets/import'">
                    <span class="material-symbols-outlined text-xl">upload_file</span>
                    Import Excel
                </x-btn>
                <x-btn type="primary" onclick="openCreateModal()">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Tambah Aset
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Aset</p>
            <p class="text-2xl font-bold text-white">{{ $assets->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Nilai Perolehan</p>
            <p class="text-2xl font-bold text-primary">Rp {{ number_format($assets->sum('acquisition_cost'), 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Akumulasi Penyusutan</p>
            <p class="text-2xl font-bold text-orange-400">Rp {{ number_format($assets->sum('accumulated_depreciation'), 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Nilai Buku</p>
            <p class="text-2xl font-bold text-green-400">Rp {{ number_format($assets->sum(fn($a) => $a->getBookValue()), 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Assets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($assets as $asset)
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition {{ !$asset->is_active ? 'opacity-50' : '' }}">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">apartment</span>
                </div>
                <span class="px-2 py-1 rounded text-xs font-medium {{ $asset->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                    {{ $asset->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div class="mb-3">
                <span class="text-xs text-text-muted font-mono">{{ $asset->code }}</span>
                <h3 class="text-white font-bold">{{ $asset->name }}</h3>
            </div>
            <div class="space-y-2 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-text-muted">Nilai Perolehan</span>
                    <span class="text-white">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Akumulasi Penyusutan</span>
                    <span class="text-orange-400">Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-medium">
                    <span class="text-text-muted">Nilai Buku</span>
                    <span class="text-green-400">Rp {{ number_format($asset->getBookValue(), 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="pt-3 border-t border-border-dark/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-text-muted">Penyusutan/bulan</p>
                        <p class="text-primary font-medium">Rp {{ number_format($asset->getMonthlyDepreciation(), 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-text-muted">Sisa Umur</p>
                        <p class="text-white font-medium">{{ $asset->getRemainingLife() }} bulan</p>
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-border-dark/50 flex justify-end">
                <button onclick="editAsset({{ json_encode($asset) }})" class="text-text-muted hover:text-primary">
                    <span class="material-symbols-outlined">edit</span>
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-text-muted">
            <span class="material-symbols-outlined text-5xl mb-3">apartment</span>
            <p>Belum ada data aset tetap</p>
            <p class="text-sm mt-1">Klik tombol "Tambah Aset" untuk menambahkan aset tetap baru</p>
        </div>
        @endforelse
    </div>

    <!-- Create/Edit Modal -->
    <div id="assetModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between sticky top-0 bg-background-dark">
                    <h3 class="text-lg font-bold text-white" id="modalTitle">Tambah Aset Tetap</h3>
                    <button onclick="closeModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="assetForm" class="p-6 space-y-4">
                    <input type="hidden" id="assetId">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Kode</label>
                            <input type="text" id="code" required
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Tanggal Perolehan</label>
                            <input type="date" id="acquisition_date" required
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama Aset</label>
                        <input type="text" id="name" required
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Nilai Perolehan</label>
                            <input type="number" id="acquisition_cost" required min="0"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Nilai Residu</label>
                            <input type="number" id="salvage_value" required min="0"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Umur Manfaat (bulan)</label>
                            <input type="number" id="useful_life_months" required min="1"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Metode Penyusutan</label>
                            <select id="depreciation_method" required
                                    class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                <option value="straight_line">Garis Lurus</option>
                                <option value="declining_balance">Saldo Menurun</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Akun Beban Penyusutan</label>
                        <select id="expense_coa_id"
                                class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">-- Pilih Akun Beban --</option>
                            @foreach($expenseAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-text-muted mt-1">Diperlukan untuk menjalankan depresiasi otomatis</p>
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

    @push('scripts')
    <script>
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Aset Tetap';
            document.getElementById('assetForm').reset();
            document.getElementById('assetId').value = '';
            document.getElementById('code').disabled = false;
            document.getElementById('acquisition_date').disabled = false;
            document.getElementById('acquisition_cost').disabled = false;
            document.getElementById('useful_life_months').disabled = false;
            document.getElementById('depreciation_method').disabled = false;
            document.getElementById('activeToggle').classList.add('hidden');
            document.getElementById('assetModal').classList.remove('hidden');
        }

        function editAsset(asset) {
            document.getElementById('modalTitle').textContent = 'Edit Aset Tetap';
            document.getElementById('assetId').value = asset.id;
            document.getElementById('code').value = asset.code;
            document.getElementById('code').disabled = true;
            document.getElementById('name').value = asset.name;
            document.getElementById('acquisition_date').value = asset.acquisition_date.split('T')[0];
            document.getElementById('acquisition_date').disabled = true;
            document.getElementById('acquisition_cost').value = asset.acquisition_cost;
            document.getElementById('acquisition_cost').disabled = true;
            document.getElementById('salvage_value').value = asset.salvage_value;
            document.getElementById('useful_life_months').value = asset.useful_life_months;
            document.getElementById('useful_life_months').disabled = true;
            document.getElementById('depreciation_method').value = asset.depreciation_method;
            document.getElementById('depreciation_method').disabled = true;
            document.getElementById('expense_coa_id').value = asset.expense_coa_id || '';
            document.getElementById('is_active').checked = asset.is_active;
            document.getElementById('activeToggle').classList.remove('hidden');
            document.getElementById('assetModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('assetModal').classList.add('hidden');
        }

        document.getElementById('assetForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('assetId').value;
            const isEdit = !!id;
            
            const data = isEdit ? {
                name: document.getElementById('name').value,
                salvage_value: parseFloat(document.getElementById('salvage_value').value),
                expense_coa_id: document.getElementById('expense_coa_id').value || null,
                is_active: document.getElementById('is_active').checked,
            } : {
                code: document.getElementById('code').value,
                name: document.getElementById('name').value,
                acquisition_date: document.getElementById('acquisition_date').value,
                acquisition_cost: parseFloat(document.getElementById('acquisition_cost').value),
                salvage_value: parseFloat(document.getElementById('salvage_value').value),
                useful_life_months: parseInt(document.getElementById('useful_life_months').value),
                depreciation_method: document.getElementById('depreciation_method').value,
                expense_coa_id: document.getElementById('expense_coa_id').value || null,
            };

            const response = await fetch(isEdit ? `/assets/${id}` : '/assets', {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Terjadi kesalahan');
            }
        });
    </script>
    @endpush

    <!-- Depreciation Modal -->
    <div id="depreciationModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDepreciationModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-md">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Jalankan Depresiasi</h3>
                    <button onclick="closeDepreciationModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="p-4 rounded-xl bg-orange-500/10 border border-orange-500/30">
                        <p class="text-orange-400 text-sm"><span class="material-symbols-outlined align-middle text-base mr-1">warning</span> Proses ini akan membuat jurnal penyusutan untuk semua aset aktif yang memiliki akun penyusutan lengkap.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Tahun</label>
                            <select id="depYear" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                @for($y = now()->year; $y >= now()->year - 5; $y--)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Bulan</label>
                            <select id="depMonth" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $name)
                                <option value="{{ $i + 1 }}" {{ ($i + 1) == now()->month ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <x-btn type="secondary" onclick="closeDepreciationModal()">Batal</x-btn>
                        <x-btn type="primary" onclick="runDepreciation()" id="runDepBtn">
                            <span class="material-symbols-outlined text-xl">calculate</span>
                            Jalankan
                        </x-btn>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openDepreciationModal() {
            document.getElementById('depreciationModal').classList.remove('hidden');
        }

        function closeDepreciationModal() {
            document.getElementById('depreciationModal').classList.add('hidden');
        }

        async function runDepreciation() {
            const year = document.getElementById('depYear').value;
            const month = document.getElementById('depMonth').value;
            
            if (!confirm(`Jalankan depresiasi untuk periode ${month}/${year}?`)) return;

            const btn = document.getElementById('runDepBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined text-xl animate-spin">progress_activity</span> Memproses...';

            try {
                const response = await fetch('/assets/depreciate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ year: parseInt(year), month: parseInt(month) })
                });

                const result = await response.json();
                
                if (result.success) {
                    alert(`Berhasil! Jurnal penyusutan telah dibuat.\n\nTotal Penyusutan: Rp ${result.data.total_depreciation.toLocaleString('id-ID')}\nAset diproses: ${result.data.assets_processed}`);
                    closeDepreciationModal();
                    location.reload();
                } else {
                    alert(result.message || 'Terjadi kesalahan');
                }

            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan saat menjalankan depresiasi');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined text-xl">calculate</span> Jalankan';
            }
        }
    </script>
    @endpush
</x-app-layout>
