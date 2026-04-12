<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Managemen Role & Hak Akses</h2>
                <p class="text-text-muted text-sm mt-1">Matriks izin akses untuk setiap tingkatan pengguna</p>
            </div>
            <div class="flex items-center gap-3">
                <x-btn type="secondary" href="{{ route('users.index') }}">
                    <span class="material-symbols-outlined text-xl">group</span>
                    Kelola Pengguna
                </x-btn>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach($roles as $role)
            <div class="p-4 rounded-2xl bg-surface-dark border border-border-dark flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">
                        @if($role == 'Administrator') terminal @elseif($role == 'Manajer') badge @elseif($role == 'Operator') edit_note @else visibility @endif
                    </span>
                </div>
                <div>
                    <h3 class="text-white font-bold">{{ $role }}</h3>
                    <p class="text-xs text-text-muted">Status: Active</p>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Permission Matrix Table -->
        <div class="bg-surface-dark rounded-2xl border border-border-dark overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-surface-highlight/30">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">list_alt</span>
                    Matriks Izin Akses (RBAC)
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-dark border-b border-border-dark">
                        <tr>
                            <th class="px-6 py-4 text-text-muted font-medium w-64">Modul & Tindakan</th>
                            @foreach($roles as $role)
                            <th class="px-6 py-4 text-center text-text-muted font-medium">{{ $role }}</th>
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
                                    <span class="text-[10px] font-black uppercase tracking-widest text-primary">{{ $module }}</span>
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
                                    @if($row[$role])
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

        <!-- Documentation Note -->
        <div class="p-6 rounded-2xl bg-primary/10 border border-primary/20 flex gap-4">
            <span class="material-symbols-outlined text-primary text-3xl">info</span>
            <div>
                <h4 class="text-white font-bold mb-1">Informasi Hak Akses</h4>
                <p class="text-sm text-text-muted leading-relaxed">
                    Matriks di atas menampilkan izin akses yang berlaku saat ini. Pengaturan hak akses diatur secara sentral melalui <code>PermissionHelper</code> sistem untuk menjaga integritas data akuntansi. Perubahan pada role pengguna melalui menu <strong>Kelola Pengguna</strong> akan secara otomatis mengikuti matriks izin di atas.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
