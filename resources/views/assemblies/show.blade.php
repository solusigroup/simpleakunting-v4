<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">BOM: {{ $assembly->name }}</h2>
                <p class="text-text-muted text-sm mt-1">{{ $assembly->code }} - Kelola komponen untuk produk ini</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="window.location.href='{{ route('assemblies.index') }}'">
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                    Kembali
                </x-btn>
                <x-btn type="primary" onclick="openAddComponentModal()">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Tambah Komponen
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Assembly Info -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Komponen</p>
            <p class="text-2xl font-bold text-white">{{ $assembly->components->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Biaya BOM</p>
            <p class="text-2xl font-bold text-primary">Rp {{ number_format($totalBomCost, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Stok Assembly</p>
            <p class="text-2xl font-bold text-white">{{ number_format($assembly->stock) }} {{ $assembly->unit }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Harga Jual</p>
            <p class="text-2xl font-bold text-green-400">Rp {{ number_format($assembly->price, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Components Table -->
    <div class="bg-surface-dark/30 rounded-2xl border border-border-dark overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark bg-surface-dark">
            <h3 class="text-lg font-bold text-white">Daftar Komponen (Bill of Materials)</h3>
        </div>
        
        @if($assembly->components->isEmpty())
        <div class="py-12 text-center text-text-muted">
            <span class="material-symbols-outlined text-5xl mb-3">inventory_2</span>
            <p>Belum ada komponen</p>
            <p class="text-sm mt-1">Klik "Tambah Komponen" untuk menambahkan bahan baku</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border-dark">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-white">Komponen</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-white">Kuantitas</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-white">Biaya/Unit</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-white">Total Biaya</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-white">Stok Tersedia</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-white">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/50">
                    @foreach($assembly->components as $component)
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-white font-medium">{{ $component->component->name }}</p>
                                <p class="text-xs text-text-muted">{{ $component->component->code }} • {{ $component->component->getCategoryLabel() }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right text-white">
                            {{ number_format($component->quantity, 2) }} {{ $component->unit }}
                        </td>
                        <td class="px-6 py-4 text-right text-text-muted">
                            Rp {{ number_format($component->component->cost, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-primary font-medium">
                            Rp {{ number_format($component->getTotalCost(), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @php $status = $stockStatus[$component->id] ?? null; @endphp
                            @if($status && $status['sufficient'])
                            <span class="text-green-400">{{ number_format($status['available']) }}</span>
                            @else
                            <span class="text-red-400">{{ number_format($status['available'] ?? 0) }}</span>
                            <span class="text-xs text-red-400 block">Kurang!</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="editComponent({{ json_encode($component) }})" 
                                        class="text-text-muted hover:text-primary" title="Edit">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                <button onclick="deleteComponent({{ $component->id }})" 
                                        class="text-text-muted hover:text-red-400" title="Hapus">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-surface-dark border-t-2 border-primary">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-white font-bold">TOTAL BIAYA BOM</td>
                        <td class="px-6 py-4 text-right text-primary font-bold text-lg">
                            Rp {{ number_format($totalBomCost, 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

    <!-- Add Component Modal -->
    <div id="addComponentModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-lg">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white" id="modalTitle">Tambah Komponen</h3>
                    <button onclick="closeModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="componentForm" class="p-6 space-y-4">
                    <input type="hidden" id="componentId">
                    
                    <div id="selectComponentDiv">
                        <label class="block text-sm font-medium text-text-muted mb-2">Pilih Komponen *</label>
                        <select id="component_id" required
                                class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">-- Pilih Komponen --</option>
                            @foreach(\App\Models\Inventory::where('company_id', auth()->user()->company_id)->where('is_assembly', false)->where('is_active', true)->orderBy('name')->get() as $item)
                            <option value="{{ $item->id }}" data-unit="{{ $item->unit }}" data-cost="{{ $item->cost }}">
                                {{ $item->code }} - {{ $item->name }} (Stok: {{ $item->stock }} {{ $item->unit }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Kuantitas *</label>
                            <input type="number" id="quantity" required min="0.01" step="0.01"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Satuan</label>
                            <input type="text" id="unit" readonly
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-text-muted">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Catatan</label>
                        <textarea id="notes" rows="2"
                                  class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary resize-none"></textarea>
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
        const assemblyId = {{ $assembly->id }};
        
        function openAddComponentModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Komponen';
            document.getElementById('componentForm').reset();
            document.getElementById('componentId').value = '';
            document.getElementById('selectComponentDiv').style.display = 'block';
            document.getElementById('component_id').disabled = false;
            document.getElementById('addComponentModal').classList.remove('hidden');
        }

        function editComponent(component) {
            document.getElementById('modalTitle').textContent = 'Edit Komponen';
            document.getElementById('componentId').value = component.id;
            document.getElementById('component_id').value = component.component_id;
            document.getElementById('component_id').disabled = true;
            document.getElementById('selectComponentDiv').style.display = 'none';
            document.getElementById('quantity').value = component.quantity;
            document.getElementById('unit').value = component.unit;
            document.getElementById('notes').value = component.notes || '';
            document.getElementById('addComponentModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('addComponentModal').classList.add('hidden');
        }

        document.getElementById('component_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            document.getElementById('unit').value = selected.dataset.unit || '';
        });

        document.getElementById('componentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const componentId = document.getElementById('componentId').value;
            const isEdit = !!componentId;
            
            const data = {
                component_id: document.getElementById('component_id').value,
                quantity: parseFloat(document.getElementById('quantity').value),
                notes: document.getElementById('notes').value,
            };

            const url = isEdit 
                ? `/assemblies/components/${componentId}` 
                : `/assemblies/${assemblyId}/components`;
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
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
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan');
            }
        });

        async function deleteComponent(componentId) {
            if (!confirm('Yakin ingin menghapus komponen ini dari BOM?')) return;

            try {
                const response = await fetch(`/assemblies/components/${componentId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan');
            }
        }
    </script>
    @endpush
</x-app-layout>
