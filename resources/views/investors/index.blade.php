@php
    $title = 'Daftar Investor';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-white">Kelola Investor</h2>
            <a href="{{ route('investors.create') }}" 
               class="px-5 py-2.5 bg-primary hover:bg-[#2ec56a] text-white rounded-xl font-semibold transition flex items-center gap-2">
                <span class="material-symbols-outlined">add</span>
                Tambah Investor
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
        <div class="p-4 rounded-xl bg-green-500/20 border border-green-500/30 text-green-400">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-400">
            {{ session('error') }}
        </div>
        @endif

        <div class="bg-surface-dark rounded-2xl border border-border-dark overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-background-dark/50 border-b border-border-dark">
                        <th class="px-6 py-4 text-sm font-semibold text-text-muted uppercase">Nama Investor</th>
                        <th class="px-6 py-4 text-sm font-semibold text-text-muted uppercase text-center">Share (%)</th>
                        <th class="px-6 py-4 text-sm font-semibold text-text-muted uppercase">Basis Perhitungan</th>
                        <th class="px-6 py-4 text-sm font-semibold text-text-muted uppercase text-center">Status</th>
                        <th class="px-6 py-4 text-sm font-semibold text-text-muted uppercase text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-dark">
                    @forelse($investors as $investor)
                    <tr class="hover:bg-surface-highlight/30 transition">
                        <td class="px-6 py-4 text-white font-medium">{{ $investor->name }}</td>
                        <td class="px-6 py-4 text-white text-center">{{ number_format($investor->share_percentage, 2) }}%</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $investor->basis == 'GROSS_PROFIT' ? 'bg-orange-500/10 text-orange-400' : 'bg-blue-500/10 text-blue-400' }}">
                                {{ $investor->basis == 'GROSS_PROFIT' ? 'Laba Kotor (Gross)' : 'Laba Bersih (Net)' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($investor->is_active)
                                <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-400 text-xs font-semibold">Aktif</span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-400 text-xs font-semibold">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('investors.edit', $investor) }}" class="p-2 text-text-muted hover:text-primary transition inline-block">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                            <form action="{{ route('investors.destroy', $investor) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus investor ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-text-muted hover:text-red-400 transition">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-20">group_off</span>
                            <p>Belum ada data investor.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
