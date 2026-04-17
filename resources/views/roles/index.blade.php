<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Managemen Role & Hak Akses</h2>
                <p class="text-text-muted text-sm mt-1">Matriks izin akses untuk setiap tingkatan pengguna</p>
            </div>
            <div class="flex items-center gap-3">
                <x-btn type="primary" href="{{ route('roles.create') }}">
                    <span class="material-symbols-outlined text-xl">add_circle</span>
                    Tambah Role Baru
                </x-btn>
                <x-btn type="secondary" href="{{ route('users.index') }}">
                    <span class="material-symbols-outlined text-xl">group</span>
                    Kelola Pengguna
                </x-btn>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Roles List -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($roles as $role)
            <div class="p-5 rounded-2xl bg-surface-dark border border-border-dark flex flex-col justify-between group hover:border-primary/50 transition-all">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary">
                            @if($role->name == 'Super User') polyline @elseif($role->name == 'Administrator') terminal @elseif($role->name == 'Manajer') badge @elseif($role->name == 'Operator') edit_note @else visibility @endif
                        </span>
                    </div>
                    <div class="flex gap-1">
                        @if(!$role->is_system || $role->name !== 'Super User')
                        <a href="{{ route('roles.edit', $role->id) }}" class="p-2 rounded-lg hover:bg-white/5 text-text-muted hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </a>
                        @endif
                        @if(!$role->is_system)
                        <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus role ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg hover:bg-red-500/10 text-text-muted hover:text-red-500 transition-colors">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-white font-bold">{{ $role->name }}</h3>
                        @if($role->is_system)
                        <span class="px-2 py-0.5 rounded-full bg-primary/10 border border-primary/20 text-[10px] text-primary font-bold uppercase tracking-wider">System</span>
                        @endif
                    </div>
                    <p class="text-xs text-text-muted leading-relaxed line-clamp-2">
                        {{ $role->description ?: 'Tidak ada deskripsi.' }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Permission Matrix Table -->
        <div class="bg-surface-dark rounded-2xl border border-border-dark overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-surface-highlight/30 flex items-center justify-between">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">list_alt</span>
                    Matriks Izin Akses (RBAC)
                </h3>
                <span class="text-xs text-text-muted">Centang menandakan hak akses tersedia</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-dark border-b border-border-dark">
                        <tr>
                            <th class="px-6 py-4 text-text-muted font-medium w-64">Modul & Tindakan</th>
                            @foreach($roles as $role)
                            <th class="px-6 py-4 text-center text-text-muted font-medium">{{ $role->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark/50">
                        @php $lastModule = ''; @endphp
                        @foreach($matrix as $row)
                            @php 
                                $module = explode('.', $row['permission'])[0];
                                $isNewModule = $module !== $lastModule;
                                $lastModule = $module;
                            @endphp
                            
                            @if($isNewModule)
                            <tr class="bg-primary/5">
                                <td colspan="{{ count($roles) + 1 }}" class="px-6 py-2">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-primary">{{ strtoupper($module) }}</span>
                                </td>
                            </tr>
                            @endif

                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-1.5 h-1.5 rounded-full bg-border-dark group-hover:bg-primary transition-colors"></div>
                                        <span class="text-white font-medium">{{ $row['permission'] }}</span>
                                    </div>
                                </td>
                                @foreach($roles as $role)
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $hasPerm = false;
                                        if ($role->name === 'Super User') {
                                            $hasPerm = true;
                                        } elseif ($role->permissions()->where('slug', $row['permission'])->exists()) {
                                            $hasPerm = true;
                                        } else {
                                            $hasPerm = $row[$role->name] ?? false;
                                        }
                                    @endphp
                                    @if($hasPerm)
                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-500/10 border border-green-500/20">
                                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-500/5 border border-white/5">
                                            <span class="material-symbols-outlined text-text-muted/30 text-lg">block</span>
                                        </div>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info Note -->
        <div class="p-6 rounded-2xl bg-surface-dark border border-border-dark flex gap-4">
            <span class="material-symbols-outlined text-primary text-3xl">info</span>
            <div>
                <h4 class="text-white font-bold mb-1">Pengaturan Hak Akses</h4>
                <p class="text-sm text-text-muted leading-relaxed">
                    Role dengan tanda <span class="text-primary font-bold">SYSTEM</span> adalah role bawaan sistem yang memiliki konfigurasi standar. 
                    Role <strong>Super User</strong> memiliki akses penuh ke seluruh fitur aplikasi dan tidak disarankan untuk digunakan dalam operasional harian kecuali untuk administrasi tingkat tinggi.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
