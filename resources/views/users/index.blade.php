@php
    $title = 'Kelola Pengguna';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-white">Kelola Pengguna</h2>
            <a href="{{ route('users.create') }}" class="flex items-center gap-2 px-6 py-3 rounded-xl bg-primary text-white font-semibold hover:bg-[#2ec56a] transition">
                <span class="material-symbols-outlined">person_add</span>
                Tambah Pengguna
            </a>
        </div>
    </x-slot>

    <div class="bg-surface-dark rounded-2xl border border-border-dark p-8">
        @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-500/20 border border-green-500/30 text-green-400">
            {{ session('success') }}
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border-dark">
                        <th class="text-left py-4 px-4 text-text-muted font-medium text-sm uppercase">Nama</th>
                        <th class="text-left py-4 px-4 text-text-muted font-medium text-sm uppercase">Email</th>
                        <th class="text-left py-4 px-4 text-text-muted font-medium text-sm uppercase">Role</th>
                        <th class="text-right py-4 px-4 text-text-muted font-medium text-sm uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="border-b border-border-dark/50 hover:bg-surface-highlight/30 transition">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-dark rounded-full flex items-center justify-center">
                                    <span class="text-primary font-bold">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                                <span class="text-white font-medium">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-text-muted">{{ $user->email }}</td>
                        <td class="py-4 px-4">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-primary/20 text-primary">
                                {{ $user->role ?? 'User' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('users.edit', $user->id) }}" class="p-2 text-text-muted hover:text-primary transition">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user->id) }}" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-text-muted hover:text-red-400 transition">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-5xl mb-2">group_off</span>
                            <p>Belum ada pengguna lain</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
