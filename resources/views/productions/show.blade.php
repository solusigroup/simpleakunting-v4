<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">{{ $production->production_number }}</h2>
                <p class="text-text-muted text-sm mt-1">Detail produksi {{ $production->assembly->name }}</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="window.location.href='{{ route('productions.index') }}'">
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                    Kembali
                </x-btn>
                @if($production->status == 'draft')
                <x-btn type="primary" onclick="startProduction()">
                    <span class="material-symbols-outlined text-xl">play_arrow</span>
                    Mulai Produksi
                </x-btn>
                @endif
                @if($production->status == 'in_progress')
                <x-btn type="primary" onclick="completeProduction()">
                    <span class="material-symbols-outlined text-xl">check</span>
                    Selesaikan
                </x-btn>
                @endif
            </div>
        </div>
    </x-slot>

    <!-- Status Banner -->
    <div class="mb-6 p-4 rounded-xl border
        {{ $production->status == 'completed' ? 'bg-green-500/10 border-green-500/30' : 
           ($production->status == 'in_progress' ? 'bg-yellow-500/10 border-yellow-500/30' : 
           ($production->status == 'cancelled' ? 'bg-red-500/10 border-red-500/30' : 'bg-gray-500/10 border-gray-500/30')) }}">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-2xl
                {{ $production->status == 'completed' ? 'text-green-400' : 
                   ($production->status == 'in_progress' ? 'text-yellow-400' : 
                   ($production->status == 'cancelled' ? 'text-red-400' : 'text-gray-400')) }}">
                {{ $production->status == 'completed' ? 'check_circle' : 
                   ($production->status == 'in_progress' ? 'pending' : 
                   ($production->status == 'cancelled' ? 'cancel' : 'draft')) }}
            </span>
            <div>
                <p class="font-bold text-white">Status: {{ $production->getStatusLabel() }}</p>
                <p class="text-sm text-text-muted">
                    Dibuat oleh {{ $production->creator->name }} pada {{ $production->created_at->format('d M Y H:i') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Tanggal Produksi</p>
            <p class="text-xl font-bold text-white">{{ $production->production_date->format('d M Y') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Kuantitas</p>
            <p class="text-xl font-bold text-white">{{ number_format($production->quantity) }} {{ $production->unit }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Biaya</p>
            <p class="text-xl font-bold text-primary">Rp {{ number_format($production->total_cost, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Biaya/Unit</p>
            <p class="text-xl font-bold text-green-400">Rp {{ number_format($production->unit_cost, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Info -->
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4">Produk Dihasilkan</h3>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-primary/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-3xl text-primary">precision_manufacturing</span>
                    </div>
                    <div>
                        <p class="text-xs text-text-muted">{{ $production->assembly->code }}</p>
                        <p class="text-xl font-bold text-white">{{ $production->assembly->name }}</p>
                        <p class="text-sm text-text-muted">COA: {{ $production->assembly->account->code ?? '-' }} - {{ $production->assembly->account->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Components Used -->
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4">Komponen yang Digunakan</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-border-dark">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-white">Komponen</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-white">Dibutuhkan</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-white">Digunakan</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-white">Variansi</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-white">Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark/50">
                            @foreach($production->components as $component)
                            @php $variance = $component->getVariance(); @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="text-white">{{ $component->component->name }}</p>
                                    <p class="text-xs text-text-muted">{{ $component->component->code }}</p>
                                </td>
                                <td class="px-4 py-3 text-right text-text-muted">
                                    {{ number_format($component->quantity_required, 2) }} {{ $component->unit }}
                                </td>
                                <td class="px-4 py-3 text-right text-white">
                                    {{ number_format($component->quantity_used, 2) }} {{ $component->unit }}
                                </td>
                                <td class="px-4 py-3 text-right {{ $variance > 0 ? 'text-red-400' : ($variance < 0 ? 'text-green-400' : 'text-text-muted') }}">
                                    {{ $variance > 0 ? '+' : '' }}{{ number_format($variance, 2) }}
                                    @if($component->hasWaste()) <span class="text-xs">(waste)</span> @endif
                                </td>
                                <td class="px-4 py-3 text-right text-primary font-medium">
                                    Rp {{ number_format($component->total_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-primary">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-white font-bold">Total Biaya Material</td>
                                <td class="px-4 py-3 text-right text-primary font-bold">
                                    Rp {{ number_format($production->total_material_cost, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Cost Breakdown -->
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4">Rincian Biaya</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-text-muted">Biaya Material</span>
                        <span class="text-white">Rp {{ number_format($production->total_material_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Biaya Tenaga Kerja</span>
                        <span class="text-white">Rp {{ number_format($production->labor_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Biaya Overhead</span>
                        <span class="text-white">Rp {{ number_format($production->overhead_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="pt-3 border-t border-border-dark flex justify-between">
                        <span class="text-white font-bold">Total</span>
                        <span class="text-primary font-bold">Rp {{ number_format($production->total_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="pt-3 border-t border-border-dark flex justify-between">
                        <span class="text-white font-bold">Per Unit</span>
                        <span class="text-green-400 font-bold">Rp {{ number_format($production->unit_cost, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Journal Entry -->
            @if($production->journal)
            <div class="p-6 rounded-2xl border border-green-500/30 bg-green-500/10">
                <h3 class="text-lg font-bold text-white mb-4">Jurnal Terbentuk</h3>
                <p class="text-sm text-text-muted mb-2">{{ $production->journal->description }}</p>
                <a href="{{ route('journals.index') }}" class="text-primary text-sm hover:underline">
                    Lihat Jurnal &rarr;
                </a>
            </div>
            @endif

            <!-- Notes -->
            @if($production->notes)
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="text-lg font-bold text-white mb-4">Catatan</h3>
                <p class="text-text-muted">{{ $production->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        async function startProduction() {
            if (!confirm('Mulai produksi ini?')) return;
            await updateStatus('start');
        }

        async function completeProduction() {
            if (!confirm('Selesaikan produksi ini? Stok akan diupdate dan jurnal akan dibuat.')) return;
            await updateStatus('complete');
        }

        async function updateStatus(action) {
            try {
                const response = await fetch(`/productions/{{ $production->id }}/${action}`, {
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
