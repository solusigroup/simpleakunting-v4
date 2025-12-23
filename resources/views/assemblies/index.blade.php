<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Bill of Materials</h2>
                <p class="text-text-muted text-sm mt-1">Kelola komponen untuk produk rakitan (assembly)</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="window.location.href='/inventory'">
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                    Ke Persediaan
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Total Assembly</p>
            <p class="text-2xl font-bold text-white">{{ $assemblies->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Dengan BOM</p>
            <p class="text-2xl font-bold text-green-400">{{ $assemblies->filter(fn($a) => $a->components->count() > 0)->count() }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm">Belum Ada BOM</p>
            <p class="text-2xl font-bold text-orange-400">{{ $assemblies->filter(fn($a) => $a->components->count() == 0)->count() }}</p>
        </div>
    </div>

    @if($assemblies->isEmpty())
    <div class="py-12 text-center text-text-muted">
        <span class="material-symbols-outlined text-5xl mb-3">precision_manufacturing</span>
        <p>Belum ada item assembly</p>
        <p class="text-sm mt-1">Buat item dengan checkbox "Assembly" di halaman Persediaan</p>
        <x-btn type="primary" class="mt-4" onclick="window.location.href='/inventory'">
            <span class="material-symbols-outlined text-xl">add</span>
            Ke Persediaan
        </x-btn>
    </div>
    @else
    <!-- Assembly Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($assemblies as $assembly)
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">precision_manufacturing</span>
                </div>
                <span class="px-2 py-1 rounded text-xs font-medium {{ $assembly->components->count() > 0 ? 'bg-green-500/20 text-green-400' : 'bg-orange-500/20 text-orange-400' }}">
                    {{ $assembly->components->count() }} Komponen
                </span>
            </div>
            <div class="mb-3">
                <span class="text-xs text-text-muted font-mono">{{ $assembly->code }}</span>
                <h3 class="text-white font-bold">{{ $assembly->name }}</h3>
                <p class="text-xs text-text-muted mt-1">{{ $assembly->getCategoryLabel() }}</p>
            </div>
            <div class="space-y-2 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-text-muted">Stok</span>
                    <span class="text-white">{{ number_format($assembly->stock) }} {{ $assembly->unit }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Biaya Komponen</span>
                    <span class="text-primary">Rp {{ number_format($assembly->getComponentsCost(), 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-text-muted">Harga Jual</span>
                    <span class="text-green-400">Rp {{ number_format($assembly->price, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="pt-3 border-t border-border-dark/50">
                <a href="{{ route('assemblies.show', $assembly->id) }}" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-primary/20 text-primary hover:bg-primary/30 transition">
                    <span class="material-symbols-outlined text-xl">settings</span>
                    Kelola BOM
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-app-layout>
