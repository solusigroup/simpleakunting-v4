<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Persediaan</h2>
                <p class="text-text-muted text-sm mt-1">Kelola master data persediaan barang</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="window.location.href='/inventory/export'">
                    <span class="material-symbols-outlined text-xl">download</span>
                    Export Excel
                </x-btn>
                <x-btn type="secondary" onclick="window.location.href='/inventory/import'">
                    <span class="material-symbols-outlined text-xl">upload_file</span>
                    Import Excel
                </x-btn>
                <x-btn type="primary" onclick="openCreateModal()">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Tambah Barang
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Item</p>
            <p class="text-2xl font-bold text-white">{{ $items->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Nilai</p>
            <p class="text-2xl font-bold text-primary">Rp {{ number_format($items->sum(fn($i) => $i->getValue()), 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Stok Rendah</p>
            <p class="text-2xl font-bold text-orange-400">{{ $items->filter(fn($i) => $i->isLowStock())->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Tidak Aktif</p>
            <p class="text-2xl font-bold text-text-muted">{{ $items->where('is_active', false)->count() }}</p>
        </div>
    </div>

    <!-- Items Table -->
    <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-surface-highlight/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-text-muted uppercase">Kode</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-text-muted uppercase">Nama Barang</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-text-muted uppercase">Satuan</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-text-muted uppercase">Stok</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-text-muted uppercase">Harga Beli</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-text-muted uppercase">Harga Jual</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-text-muted uppercase">Nilai</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-text-muted uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/50">
                    @forelse($items as $item)
                    <tr class="hover:bg-surface-highlight/30 transition {{ !$item->is_active ? 'opacity-50' : '' }}">
                        <td class="px-6 py-4 text-white font-mono text-sm">{{ $item->code }}</td>
                        <td class="px-6 py-4">
                            <p class="text-white font-medium">{{ $item->name }}</p>
                            @if($item->isLowStock())
                            <span class="text-xs text-orange-400">⚠ Stok Rendah</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-text-muted">{{ $item->unit }}</td>
                        <td class="px-6 py-4 text-right {{ $item->isLowStock() ? 'text-orange-400' : 'text-white' }} font-medium">
                            {{ number_format($item->stock) }}
                        </td>
                        <td class="px-6 py-4 text-right text-text-muted">Rp {{ number_format($item->cost, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-primary">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-white font-medium">Rp {{ number_format($item->getValue(), 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="editItem({{ json_encode($item) }})" class="text-text-muted hover:text-primary">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-5xl mb-3">inventory_2</span>
                            <p>Belum ada data persediaan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="itemModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between sticky top-0 bg-background-dark">
                    <h3 class="text-lg font-bold text-white" id="modalTitle">Tambah Barang</h3>
                    <button onclick="closeModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="itemForm" class="p-6 space-y-4">
                    <input type="hidden" id="itemId">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Kode</label>
                            <input type="text" id="code" required
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Satuan</label>
                            <input type="text" id="unit" required placeholder="pcs, kg, liter"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama Barang</label>
                        <input type="text" id="name" required
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Harga Beli</label>
                            <input type="number" id="cost" required min="0"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Harga Jual</label>
                            <input type="number" id="price" required min="0"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Stok Awal</label>
                            <input type="number" id="stock" required min="0"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Stok Minimum</label>
                            <input type="number" id="min_stock" min="0" value="0"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div id="activeToggle" class="hidden">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="is_active" class="form-checkbox rounded bg-surface-dark border-border-dark text-primary focus:ring-primary">
                            <span class="text-white">Barang Aktif</span>
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
            document.getElementById('modalTitle').textContent = 'Tambah Barang';
            document.getElementById('itemForm').reset();
            document.getElementById('itemId').value = '';
            document.getElementById('code').disabled = false;
            document.getElementById('activeToggle').classList.add('hidden');
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function editItem(item) {
            document.getElementById('modalTitle').textContent = 'Edit Barang';
            document.getElementById('itemId').value = item.id;
            document.getElementById('code').value = item.code;
            document.getElementById('code').disabled = true;
            document.getElementById('name').value = item.name;
            document.getElementById('unit').value = item.unit;
            document.getElementById('cost').value = item.cost;
            document.getElementById('price').value = item.price;
            document.getElementById('stock').value = item.stock;
            document.getElementById('min_stock').value = item.min_stock;
            document.getElementById('is_active').checked = item.is_active;
            document.getElementById('activeToggle').classList.remove('hidden');
            document.getElementById('itemModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('itemModal').classList.add('hidden');
        }

        document.getElementById('itemForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('itemId').value;
            const isEdit = !!id;
            
            const data = {
                code: document.getElementById('code').value,
                name: document.getElementById('name').value,
                unit: document.getElementById('unit').value,
                cost: parseFloat(document.getElementById('cost').value),
                price: parseFloat(document.getElementById('price').value),
                stock: parseInt(document.getElementById('stock').value),
                min_stock: parseInt(document.getElementById('min_stock').value) || 0,
            };

            if (isEdit) {
                data.is_active = document.getElementById('is_active').checked;
            }

            const response = await fetch(isEdit ? `/inventory/${id}` : '/inventory', {
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
</x-app-layout>
