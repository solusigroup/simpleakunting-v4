@php
    $title = 'Edit Investor';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('investors.index') }}" class="p-2 rounded-xl bg-surface-dark border border-border-dark text-text-muted hover:text-white transition">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-3xl font-bold text-white">Edit Investor</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('investors.update', $investor) }}" class="bg-surface-dark rounded-2xl border border-border-dark p-8 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-text-muted mb-2">Nama Investor</label>
                <input type="text" name="name" value="{{ old('name', $investor->name) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Share (Prosentase %)</label>
                    <div class="relative">
                        <input type="number" name="share_percentage" value="{{ old('share_percentage', $investor->share_percentage) }}" step="0.01" min="0" max="100" required
                               class="w-full pl-4 pr-10 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-text-muted font-bold">%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Basis Perhitungan</label>
                    <select name="basis" required
                            class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        <option value="NET_PROFIT" {{ old('basis', $investor->basis) == 'NET_PROFIT' ? 'selected' : '' }}>Laba Bersih (Net)</option>
                        <option value="GROSS_PROFIT" {{ old('basis', $investor->basis) == 'GROSS_PROFIT' ? 'selected' : '' }}>Laba Kotor (Gross)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $investor->is_active ? 'checked' : '' }}
                           class="form-checkbox rounded bg-background-dark border-border-dark text-primary focus:ring-primary w-5 h-5">
                    <span class="text-white">Investor Aktif</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-border-dark">
                <a href="{{ route('investors.index') }}" 
                   class="px-6 py-3 rounded-xl border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-8 py-3 rounded-xl bg-primary text-white font-semibold hover:bg-[#2ec56a] transition flex items-center gap-2">
                    <span class="material-symbols-outlined">save</span>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
