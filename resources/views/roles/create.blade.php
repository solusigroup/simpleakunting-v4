<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Tambah Role Baru</h2>
                <p class="text-text-muted text-sm mt-1">Definisikan tingkat akses pengguna baru</p>
            </div>
            <x-btn type="secondary" href="{{ route('roles.index') }}">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
                Kembali
            </x-btn>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Role Info -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="p-6 rounded-2xl bg-surface-dark border border-border-dark">
                        <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">info</span>
                            Informasi Role
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="name" value="Nama Role" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required placeholder="Contoh: Staff Gudang" />
                                <x-input-error for="name" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="description" value="Deskripsi" />
                                <textarea id="description" name="description" class="mt-1 block w-full rounded-xl bg-surface-highlight border-white/5 border text-white focus:border-primary focus:ring-primary transition-all text-sm" rows="4" placeholder="Jelaskan tanggung jawab role ini...">{{ old('description') }}</textarea>
                                <x-input-error for="description" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="p-6 rounded-2xl bg-primary/5 border border-primary/10">
                        <p class="text-xs text-text-muted leading-relaxed">
                            <span class="text-primary font-bold">Catatan:</span> Pastikan nama role unik dan deskripsi jelas agar memudahkan dalam pengelolaan pengguna nantinya.
                        </p>
                    </div>
                </div>

                <!-- Permission Selection -->
                <div class="lg:col-span-2">
                    <div class="bg-surface-dark rounded-2xl border border-border-dark overflow-hidden">
                        <div class="px-6 py-4 border-b border-border-dark bg-surface-highlight/30 flex items-center justify-between">
                            <h3 class="text-white font-bold flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">security</span>
                                Pengaturan Hak Akses
                            </h3>
                            <button type="button" onclick="toggleAllPermissions(true)" class="text-xs text-primary hover:underline">Pilih Semua</button>
                        </div>
                        
                        <div class="p-6">
                            @php $lastModule = ''; @endphp
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-8">
                                @foreach(collect($permissions)->groupBy('module') as $module => $modulePerms)
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between border-b border-white/5 pb-2">
                                        <h4 class="text-sm font-black text-primary uppercase tracking-wider">{{ $module }}</h4>
                                        <button type="button" onclick="toggleModulePermissions('{{ $module }}', true)" class="text-[10px] text-text-muted hover:text-white uppercase transition-colors">Modul Ini</button>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($modulePerms as $perm)
                                        <label class="flex items-center group cursor-pointer">
                                            <div class="relative flex items-center">
                                                <input type="checkbox" name="permissions[]" value="{{ $perm['slug'] }}" 
                                                    data-module="{{ $module }}"
                                                    class="peer h-5 w-5 rounded-lg border-white/10 bg-surface-highlight text-primary focus:ring-offset-surface-dark focus:ring-primary transition-all">
                                                <div class="ml-3">
                                                    <span class="text-sm text-text-muted group-hover:text-white transition-colors">{{ $perm['name'] }}</span>
                                                    <div class="text-[10px] text-text-muted/50 font-mono">{{ $perm['slug'] }}</div>
                                                </div>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-surface-highlight/20 border-t border-border-dark flex justify-end gap-3">
                            <x-btn type="secondary" href="{{ route('roles.index') }}">Batal</x-btn>
                            <x-btn type="primary" type="submit">Simpan Role</x-btn>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toggleAllPermissions(checked) {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                cb.checked = checked;
            });
        }

        function toggleModulePermissions(module, checked) {
            document.querySelectorAll(`input[data-module="${module}"]`).forEach(cb => {
                cb.checked = checked;
            });
        }
    </script>
</x-app-layout>
