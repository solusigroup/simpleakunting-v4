<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Nasabah Bank Sampah</h2>
                <p class="text-text-muted text-sm mt-1">Kelola data nasabah dan saldo tabungan mereka</p>
            </div>
            <div>
                <x-btn type="primary" onclick="document.getElementById('addCollectorModal').classList.remove('hidden')">
                    <span class="material-symbols-outlined text-xl">person_add</span>
                    Tambah Nasabah
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
                        <th class="px-6 py-4 font-semibold">ID Nasabah</th>
                        <th class="px-6 py-4 font-semibold">Nama Lengkap</th>
                        <th class="px-6 py-4 font-semibold">No. Telepon</th>
                        <th class="px-6 py-4 font-semibold">Saldo Tabungan</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark/30">
                    @forelse($collectors as $collector)
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4 font-bold text-white">{{ $collector->collector_number }}</td>
                        <td class="px-6 py-4 text-white font-medium">{{ $collector->name }}</td>
                        <td class="px-6 py-4 text-text-muted">{{ $collector->phone ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="text-primary font-bold">Rp {{ number_format($collector->balance, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('waste.collectors.show', $collector) }}" class="text-primary hover:underline text-sm font-bold">Detail</a>
                            <button onclick="document.getElementById('editCollectorModal{{ $collector->id }}').classList.remove('hidden')" class="text-text-muted hover:text-white transition">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div id="editCollectorModal{{ $collector->id }}" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
                        <div class="bg-surface-dark border border-border-dark rounded-2xl p-6 w-full max-w-md mx-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold text-white">Edit Nasabah</h3>
                                <button onclick="document.getElementById('editCollectorModal{{ $collector->id }}').classList.add('hidden')" class="text-text-muted hover:text-white">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                            <form action="{{ route('waste.collectors.update', $collector) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm text-text-muted mb-2">Nama Lengkap</label>
                                        <input type="text" name="name" value="{{ $collector->name }}" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-text-muted mb-2">No. Telepon</label>
                                        <input type="text" name="phone" value="{{ $collector->phone }}" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-text-muted mb-2">Alamat</label>
                                        <textarea name="address" rows="3" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none">{{ $collector->address }}</textarea>
                                    </div>
                                </div>
                                <div class="flex gap-3 mt-6">
                                    <button type="button" onclick="document.getElementById('editCollectorModal{{ $collector->id }}').classList.add('hidden')" class="flex-1 px-4 py-3 border border-border-dark rounded-xl text-white hover:bg-surface-highlight transition">
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
                        <td colspan="5" class="px-6 py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-2">person</span>
                            <p>Belum ada nasabah terdaftar</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addCollectorModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-surface-dark border border-border-dark rounded-2xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Tambah Nasabah Baru</h3>
                <button onclick="document.getElementById('addCollectorModal').classList.add('hidden')" class="text-text-muted hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('waste.collectors.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">ID Nasabah (Otomatis)</label>
                        <input type="text" name="collector_number" value="{{ $nextId }}" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Nama Lengkap</label>
                        <input type="text" name="name" placeholder="Nama Lengkap Nasabah" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">No. Telepon</label>
                        <input type="text" name="phone" placeholder="08xxxxxx" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Alamat</label>
                        <textarea name="address" rows="3" placeholder="Alamat lengkap nasabah" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none"></textarea>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('addCollectorModal').classList.add('hidden')" class="flex-1 px-4 py-3 border border-border-dark rounded-xl text-white hover:bg-surface-highlight transition">
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
