<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Kategori Sampah</h2>
                <p class="text-text-muted text-sm mt-1">Atur jenis sampah dan harga beli dari nasabah</p>
            </div>
            <div>
                <x-btn type="primary" onclick="document.getElementById('addCategoryModal').classList.remove('hidden')">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Tambah Kategori
                </x-btn>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-300">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-text-muted text-xs uppercase tracking-wider border-b border-border-dark/50">
                        <th class="px-6 py-4 font-semibold">Nama Kategori</th>
                        <th class="px-6 py-4 font-semibold">Satuan</th>
                        <th class="px-6 py-4 font-semibold">Harga Beli</th>
                        <th class="px-6 py-4 font-semibold">Harga Jual</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/30">
                    @forelse($categories as $category)
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4 font-bold text-white">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-text-muted">{{ $category->unit }}</td>
                        <td class="px-6 py-4 text-white">Rp {{ number_format($category->buy_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-white">Rp {{ number_format($category->sell_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            @if($category->is_active)
                                <x-badge type="success">Aktif</x-badge>
                            @else
                                <x-badge type="danger">Non-aktif</x-badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="document.getElementById('editCategoryModal{{ $category->id }}').classList.remove('hidden')" class="text-primary hover:underline text-sm font-bold">
                                Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div id="editCategoryModal{{ $category->id }}" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
                        <div class="bg-surface-dark border border-border-dark rounded-2xl p-6 w-full max-w-md mx-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold text-white">Edit Kategori</h3>
                                <button onclick="document.getElementById('editCategoryModal{{ $category->id }}').classList.add('hidden')" class="text-text-muted hover:text-white">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            <form action="{{ route('waste.categories.update', $category) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm text-text-muted mb-2">Nama Kategori</label>
                                        <input type="text" name="name" value="{{ $category->name }}" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-text-muted mb-2">Satuan</label>
                                        <input type="text" name="unit" value="{{ $category->unit }}" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm text-text-muted mb-2">Harga Beli</label>
                                            <input type="number" name="buy_price" value="{{ $category->buy_price }}" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm text-text-muted mb-2">Harga Jual</label>
                                            <input type="number" name="sell_price" value="{{ $category->sell_price }}" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }} id="is_active_{{ $category->id }}" class="rounded bg-surface-highlight border-border-dark text-primary focus:ring-primary">
                                        <label for="is_active_{{ $category->id }}" class="text-sm text-white">Kategori Aktif</label>
                                    </div>
                                </div>
                                <div class="flex gap-3 mt-6">
                                    <button type="button" onclick="document.getElementById('editCategoryModal{{ $category->id }}').classList.add('hidden')" class="flex-1 px-4 py-3 border border-border-dark rounded-xl text-white hover:bg-surface-highlight transition">
                                        Batal
                                    </button>
                                    <button type="submit" class="flex-1 px-4 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl transition">
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-2">tags</span>
                            <p>Belum ada kategori sampah</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addCategoryModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-surface-dark border border-border-dark rounded-2xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Tambah Kategori Baru</h3>
                <button onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="text-text-muted hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('waste.categories.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Nama Kategori</label>
                        <input type="text" name="name" placeholder="Contoh: Plastik PET" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Satuan</label>
                        <input type="text" name="unit" placeholder="kg" value="kg" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-text-muted mb-2">Harga Beli</label>
                            <input type="number" name="buy_price" placeholder="0" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm text-text-muted mb-2">Harga Jual</label>
                            <input type="number" name="sell_price" placeholder="0" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="flex-1 px-4 py-3 border border-border-dark rounded-xl text-white hover:bg-surface-highlight transition">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-primary hover:bg-primary-dark text-white rounded-xl transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
