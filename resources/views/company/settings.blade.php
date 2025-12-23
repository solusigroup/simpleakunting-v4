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

        <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data" class="bg-surface-dark rounded-2xl border border-border-dark p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Company Basic Info -->
            <div>
                <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">business</span>
                    Informasi Dasar
                </h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 mb-4">
                        <label class="block text-sm font-medium text-text-muted mb-2">Logo Perusahaan</label>
                        <div class="flex items-center gap-6">
                            <div class="w-24 h-24 bg-surface-highlight rounded-xl flex items-center justify-center overflow-hidden border border-border-dark">
                                @if($company->logo)
                                    <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="w-full h-full object-contain">
                                @else
                                    <span class="material-symbols-outlined text-4xl text-text-muted">business</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file" name="logo" accept="image/*"
                                       class="w-full text-sm text-text-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-background-dark hover:file:bg-primary-dark cursor-pointer">
                                <p class="mt-2 text-xs text-text-muted">Format: JPG, PNG. Maksimal 2MB.</p>
                                @error('logo')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
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

            <!-- PSAK 69 / Aset Biologis Section -->
            <div class="pt-6 border-t border-border-dark">
                <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-400">eco</span>
                    Fitur Aset Biologis (PSAK 69)
                </h3>
                
                <div class="p-4 rounded-xl bg-green-500/10 border border-green-500/30 mb-4">
                    <p class="text-sm text-text-muted">
                        PSAK 69 mengatur pengakuan, pengukuran, dan pengungkapan aset biologis (hewan ternak, tanaman perkebunan, dll).
                        Aktifkan fitur ini jika bisnis Anda bergerak di bidang agribisnis.
                    </p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Sektor Bisnis</label>
                        <select name="business_sector" id="business_sector" onchange="togglePsak69()"
                                class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="general" {{ old('business_sector', $company->business_sector) == 'general' ? 'selected' : '' }}>Umum (Non-Agribisnis)</option>
                            <option value="livestock" {{ old('business_sector', $company->business_sector) == 'livestock' ? 'selected' : '' }}>Peternakan</option>
                            <option value="plantation" {{ old('business_sector', $company->business_sector) == 'plantation' ? 'selected' : '' }}>Perkebunan</option>
                            <option value="aquaculture" {{ old('business_sector', $company->business_sector) == 'aquaculture' ? 'selected' : '' }}>Perikanan</option>
                            <option value="forestry" {{ old('business_sector', $company->business_sector) == 'forestry' ? 'selected' : '' }}>Kehutanan</option>
                            <option value="mixed_agriculture" {{ old('business_sector', $company->business_sector) == 'mixed_agriculture' ? 'selected' : '' }}>Agribisnis Campuran</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <label class="flex items-center gap-3 cursor-pointer p-4 rounded-xl bg-background-dark border border-border-dark hover:border-green-500/50 transition w-full">
                            <input type="hidden" name="enable_psak69" value="0">
                            <input type="checkbox" name="enable_psak69" id="enable_psak69" value="1" 
                                   {{ old('enable_psak69', $company->enable_psak69) ? 'checked' : '' }}
                                   class="form-checkbox rounded bg-surface-dark border-border-dark text-green-500 focus:ring-green-500 w-5 h-5">
                            <div>
                                <span class="text-white font-medium">Aktifkan PSAK 69</span>
                                <p class="text-xs text-text-muted">Menu aset biologis akan muncul</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div id="psak69_info" class="mt-4 p-4 rounded-xl bg-primary/10 border border-primary/30 {{ old('enable_psak69', $company->enable_psak69) ? '' : 'hidden' }}">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-primary">info</span>
                        <div class="text-sm">
                            <p class="text-white font-medium mb-1">Fitur yang akan diaktifkan:</p>
                            <ul class="text-text-muted space-y-1">
                                <li>• Pencatatan aset biologis (hewan/tanaman)</li>
                                <li>• Transformasi biologis (pertumbuhan, panen, reproduksi)</li>
                                <li>• Valuasi aset biologis (nilai wajar)</li>
                                <li>• Laporan rekonsiliasi aset biologis</li>
                            </ul>
                        </div>
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

    @push('scripts')
    <script>
        function togglePsak69() {
            const sector = document.getElementById('business_sector').value;
            const checkbox = document.getElementById('enable_psak69');
            const info = document.getElementById('psak69_info');
            
            const agriSectors = ['livestock', 'plantation', 'aquaculture', 'forestry', 'mixed_agriculture'];
            
            if (agriSectors.includes(sector)) {
                checkbox.checked = true;
                info.classList.remove('hidden');
            }
        }

        document.getElementById('enable_psak69').addEventListener('change', function() {
            const info = document.getElementById('psak69_info');
            if (this.checked) {
                info.classList.remove('hidden');
            } else {
                info.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-app-layout>
