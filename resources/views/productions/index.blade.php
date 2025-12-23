<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Produksi</h2>
                <p class="text-text-muted text-sm mt-1">Kelola proses manufaktur dan assembly</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="primary" onclick="window.location.href='{{ route('productions.create') }}'">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Buat Produksi
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Produksi</p>
            <p class="text-2xl font-bold text-white">{{ $productions->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Draft</p>
            <p class="text-2xl font-bold text-gray-400">{{ $productions->where('status', 'draft')->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Dalam Proses</p>
            <p class="text-2xl font-bold text-yellow-400">{{ $productions->where('status', 'in_progress')->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Selesai</p>
            <p class="text-2xl font-bold text-green-400">{{ $productions->where('status', 'completed')->count() }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('productions.index') }}" 
           class="px-4 py-2 rounded-xl text-sm transition {{ !request('status') ? 'bg-primary text-background-dark' : 'bg-surface-dark text-text-muted hover:text-white' }}">
            Semua
        </a>
        <a href="{{ route('productions.index', ['status' => 'draft']) }}" 
           class="px-4 py-2 rounded-xl text-sm transition {{ request('status') == 'draft' ? 'bg-gray-500 text-white' : 'bg-surface-dark text-text-muted hover:text-white' }}">
            Draft
        </a>
        <a href="{{ route('productions.index', ['status' => 'in_progress']) }}" 
           class="px-4 py-2 rounded-xl text-sm transition {{ request('status') == 'in_progress' ? 'bg-yellow-500 text-black' : 'bg-surface-dark text-text-muted hover:text-white' }}">
            Dalam Proses
        </a>
        <a href="{{ route('productions.index', ['status' => 'completed']) }}" 
           class="px-4 py-2 rounded-xl text-sm transition {{ request('status') == 'completed' ? 'bg-green-500 text-white' : 'bg-surface-dark text-text-muted hover:text-white' }}">
            Selesai
        </a>
    </div>

    @if($productions->isEmpty())
    <div class="py-12 text-center text-text-muted">
        <span class="material-symbols-outlined text-5xl mb-3">factory</span>
        <p>Belum ada produksi</p>
        <p class="text-sm mt-1">Buat order produksi baru untuk memulai</p>
        <x-btn type="primary" class="mt-4" onclick="window.location.href='{{ route('productions.create') }}'">
            <span class="material-symbols-outlined text-xl">add</span>
            Buat Produksi
        </x-btn>
    </div>
    @else
    <!-- Production List -->
    <div class="space-y-4">
        @foreach($productions as $production)
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center
                        {{ $production->status == 'completed' ? 'bg-green-500/20' : ($production->status == 'in_progress' ? 'bg-yellow-500/20' : 'bg-gray-500/20') }}">
                        <span class="material-symbols-outlined text-2xl
                            {{ $production->status == 'completed' ? 'text-green-400' : ($production->status == 'in_progress' ? 'text-yellow-400' : 'text-gray-400') }}">
                            {{ $production->status == 'completed' ? 'check_circle' : ($production->status == 'in_progress' ? 'pending' : 'draft') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-text-muted font-mono">{{ $production->production_number }}</span>
                        <h3 class="text-white font-bold">{{ $production->assembly->name }}</h3>
                        <p class="text-sm text-text-muted">
                            {{ $production->production_date->format('d M Y') }} • {{ $production->quantity }} {{ $production->unit }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="text-right">
                        <p class="text-text-muted text-xs">Total Biaya</p>
                        <p class="text-primary font-bold">Rp {{ number_format($production->total_cost, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-text-muted text-xs">Biaya/Unit</p>
                        <p class="text-white font-medium">Rp {{ number_format($production->unit_cost, 0, ',', '.') }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-medium
                        {{ $production->status == 'completed' ? 'bg-green-500/20 text-green-400' : 
                           ($production->status == 'in_progress' ? 'bg-yellow-500/20 text-yellow-400' : 
                           ($production->status == 'cancelled' ? 'bg-red-500/20 text-red-400' : 'bg-gray-500/20 text-gray-400')) }}">
                        {{ $production->getStatusLabel() }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('productions.show', $production->id) }}"
                       class="p-2 rounded-lg bg-surface-dark text-text-muted hover:text-white transition" title="Detail">
                        <span class="material-symbols-outlined">visibility</span>
                    </a>
                    @if($production->status == 'draft')
                    <button onclick="startProduction({{ $production->id }})"
                            class="p-2 rounded-lg bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30 transition" title="Mulai">
                        <span class="material-symbols-outlined">play_arrow</span>
                    </button>
                    <button onclick="cancelProduction({{ $production->id }})"
                            class="p-2 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 transition" title="Batalkan">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                    @endif
                    @if($production->status == 'in_progress')
                    <button onclick="completeProduction({{ $production->id }})"
                            class="p-2 rounded-lg bg-green-500/20 text-green-400 hover:bg-green-500/30 transition" title="Selesaikan">
                        <span class="material-symbols-outlined">check</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @push('scripts')
    <script>
        async function startProduction(id) {
            if (!confirm('Mulai produksi ini?')) return;
            await updateStatus(id, 'start');
        }

        async function completeProduction(id) {
            if (!confirm('Selesaikan produksi ini? Stok akan diupdate dan jurnal akan dibuat.')) return;
            await updateStatus(id, 'complete');
        }

        async function cancelProduction(id) {
            if (!confirm('Batalkan produksi ini?')) return;
            await updateStatus(id, 'cancel');
        }

        async function updateStatus(id, action) {
            try {
                const response = await fetch(`/productions/${id}/${action}`, {
                    method: 'POST',
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
