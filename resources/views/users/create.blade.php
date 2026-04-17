@php
    $title = 'Tambah Pengguna';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-white">Tambah Pengguna Baru</h2>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('users.store') }}" class="bg-surface-dark rounded-2xl border border-border-dark p-8 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-text-muted mb-2">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-text-muted mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-text-muted mb-2">Role / Hak Akses</label>
                <select name="role_id" class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary appearance-none cursor-pointer">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('role_id') == $role->id || ($role->name == 'Operator' && !old('role_id')))>
                            {{ $role->name }} - {{ $role->description ?: 'Tingkat akses kustom' }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-text-muted">
                    Setiap role memiliki hak akses berbeda yang dapat diatur di menu <a href="{{ route('roles.index') }}" class="text-primary hover:underline">Manajemen Role</a>
                </p>
                @error('role_id')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-text-muted mb-2">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-text-muted mb-2">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-border-dark">
                <a href="{{ route('users.index') }}" 
                   class="px-6 py-3 rounded-xl border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-8 py-3 rounded-xl bg-primary text-white font-semibold hover:bg-[#2ec56a] transition flex items-center gap-2">
                    <span class="material-symbols-outlined">person_add</span>
                    Tambah Pengguna
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
