<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Unit Usaha</h2>
                <p class="text-text-muted text-sm mt-1">Kelola unit usaha BUMDesa</p>
            </div>
            <x-btn type="primary" onclick="openCreateModal()">
                <span class="material-symbols-outlined text-xl">add</span>
                Tambah Unit
            </x-btn>
        </div>
    </x-slot>

    <!-- Info Banner -->
    @if(!$isBumdesa)
    <div class="mb-6 p-4 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center gap-3">
        <span class="material-symbols-outlined text-orange-400">info</span>
        <p class="text-orange-400 text-sm">Unit Usaha hanya tersedia untuk entitas BUMDesa. Silakan ubah jenis entitas di pengaturan perusahaan.</p>
    </div>
    @else

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Unit</p>
            <p class="text-2xl font-bold text-white">{{ $units->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Unit Aktif</p>
            <p class="text-2xl font-bold text-primary">{{ $units->where('is_active', true)->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Unit Nonaktif</p>
            <p class="text-2xl font-bold text-text-muted">{{ $units->where('is_active', false)->count() }}</p>
        </div>
    </div>

    <!-- Units Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="unitsGrid">
        @forelse($units as $unit)
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">store</span>
                </div>
                <span class="px-2 py-1 rounded text-xs font-medium {{ $unit->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                    {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div class="mb-3">
                <span class="text-xs text-text-muted font-mono">{{ $unit->code }}</span>
                <h3 class="text-white font-bold">{{ $unit->name }}</h3>
            </div>
            @if($unit->description)
            <p class="text-text-muted text-sm mb-3 line-clamp-2">{{ $unit->description }}</p>
            @endif
            <div class="flex items-center justify-between pt-3 border-t border-border-dark/50">
                <span class="text-xs text-text-muted">{{ $unit->created_at->format('d M Y') }}</span>
                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition">
                    <button onclick="editUnit({{ $unit->id }}, '{{ $unit->code }}', '{{ addslashes($unit->name) }}', '{{ addslashes($unit->description ?? '') }}', {{ $unit->is_active ? 'true' : 'false' }})" 
                            class="text-text-muted hover:text-primary">
                        <span class="material-symbols-outlined text-xl">edit</span>
                    </button>
                    <button onclick="toggleStatus({{ $unit->id }}, {{ $unit->is_active ? 'false' : 'true' }})" 
                            class="text-text-muted hover:text-{{ $unit->is_active ? 'red-400' : 'green-400' }}">
                        <span class="material-symbols-outlined text-xl">{{ $unit->is_active ? 'visibility_off' : 'visibility' }}</span>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-text-muted">
            <span class="material-symbols-outlined text-5xl mb-3">store</span>
            <p>Belum ada unit usaha</p>
            <p class="text-sm mt-1">Klik tombol "Tambah Unit" untuk menambahkan unit usaha baru</p>
        </div>
        @endforelse
    </div>
    @endif

    <!-- Create/Edit Modal -->
    <div id="unitModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-lg">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white" id="modalTitle">Tambah Unit Usaha</h3>
                    <button onclick="closeModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="unitForm" class="p-6 space-y-4">
                    <input type="hidden" id="unitId">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Kode Unit</label>
                        <input type="text" id="code" required maxlength="10"
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary"
                               placeholder="Contoh: UNIT01">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama Unit</label>
                        <input type="text" id="name" required
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary"
                               placeholder="Contoh: Unit Pertanian">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Deskripsi</label>
                        <textarea id="description" rows="3"
                                  class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary resize-none"
                                  placeholder="Deskripsi singkat unit usaha"></textarea>
                    </div>
                    <div id="activeToggle" class="hidden">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="is_active" class="form-checkbox rounded bg-surface-dark border-border-dark text-primary focus:ring-primary">
                            <span class="text-white">Unit Aktif</span>
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
            document.getElementById('modalTitle').textContent = 'Tambah Unit Usaha';
            document.getElementById('unitForm').reset();
            document.getElementById('unitId').value = '';
            document.getElementById('activeToggle').classList.add('hidden');
            document.getElementById('unitModal').classList.remove('hidden');
        }

        function editUnit(id, code, name, description, isActive) {
            document.getElementById('modalTitle').textContent = 'Edit Unit Usaha';
            document.getElementById('unitId').value = id;
            document.getElementById('code').value = code;
            document.getElementById('code').disabled = true;
            document.getElementById('name').value = name;
            document.getElementById('description').value = description;
            document.getElementById('is_active').checked = isActive;
            document.getElementById('activeToggle').classList.remove('hidden');
            document.getElementById('unitModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('unitModal').classList.add('hidden');
            document.getElementById('code').disabled = false;
        }

        async function toggleStatus(id, newStatus) {
            if (!confirm(`Yakin ingin ${newStatus ? 'mengaktifkan' : 'menonaktifkan'} unit ini?`)) return;
            
            const response = await fetch(`/units/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_active: newStatus })
            });

            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Terjadi kesalahan');
            }
        }

        document.getElementById('unitForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('unitId').value;
            const isEdit = !!id;
            
            const data = {
                code: document.getElementById('code').value,
                name: document.getElementById('name').value,
                description: document.getElementById('description').value,
            };

            if (isEdit) {
                data.is_active = document.getElementById('is_active').checked;
            }

            const response = await fetch(isEdit ? `/units/${id}` : '/units', {
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
