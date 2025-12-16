@php
    $title = 'Pengaturan Perusahaan';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-white">Pengaturan Perusahaan</h2>
        </div>
    </x-slot>

    <div class="max-w-4xl">
        @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-500/20 border border-green-500/30 text-green-400">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('company.update') }}" class="bg-surface-dark rounded-2xl border border-border-dark p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Company Basic Info -->
            <div>
                <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">business</span>
                    Informasi Dasar
                </h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama Perusahaan</label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">No. Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">NPWP</label>
                        <input type="text" name="npwp" value="{{ old('npwp', $company->npwp) }}"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Awal Tahun Fiskal</label>
                        <input type="date" name="fiscal_start" value="{{ old('fiscal_start', $company->fiscal_start?->format('Y-m-d')) }}"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-text-muted mb-2">Alamat</label>
                        <textarea name="address" rows="2"
                                  class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">{{ old('address', $company->address) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Signatory Section -->
            <div class="pt-6 border-t border-border-dark">
                <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">verified</span>
                    Pejabat Penandatangan Laporan
                </h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama Direktur</label>
                        <input type="text" name="director_name" value="{{ old('director_name', $company->director_name) }}"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary"
                               placeholder="Nama lengkap">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Jabatan Direktur</label>
                        <input type="text" name="director_title" value="{{ old('director_title', $company->director_title) }}"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary"
                               placeholder="Direktur">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama Sekretaris/Wadir</label>
                        <input type="text" name="secretary_name" value="{{ old('secretary_name', $company->secretary_name) }}"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary"
                               placeholder="Nama lengkap">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Jabatan</label>
                        <select name="secretary_title"
                                class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="Sekretaris" {{ old('secretary_title', $company->secretary_title) == 'Sekretaris' ? 'selected' : '' }}>Sekretaris</option>
                            <option value="Wakil Direktur Keuangan" {{ old('secretary_title', $company->secretary_title) == 'Wakil Direktur Keuangan' ? 'selected' : '' }}>Wakil Direktur Keuangan</option>
                            <option value="Kepala Keuangan" {{ old('secretary_title', $company->secretary_title) == 'Kepala Keuangan' ? 'selected' : '' }}>Kepala Keuangan</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama Staff (Pembuat Laporan)</label>
                        <input type="text" name="staff_name" value="{{ old('staff_name', $company->staff_name) }}"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary"
                               placeholder="Nama lengkap staff akuntansi">
                        <p class="mt-1 text-xs text-text-muted">Opsional - untuk signature "Dibuat Oleh"</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Jabatan Staff</label>
                        <input type="text" name="staff_title" value="{{ old('staff_title', $company->staff_title) }}"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary"
                               placeholder="Staff Akuntansi">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3 pt-6 border-t border-border-dark">
                <a href="{{ route('dashboard') }}" 
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
