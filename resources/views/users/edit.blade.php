@php
    $title = 'Edit Pengguna';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold text-white">Edit Pengguna</h2>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('users.update', $user->id) }}" class="bg-surface-dark rounded-2xl border border-border-dark p-8 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-text-muted mb-2">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-text-muted mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-text-muted mb-2">Role</label>
                <select name="role" class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    <option value="User" {{ $user->role == 'User' ? 'selected' : '' }}>User</option>
                    <option value="Admin" {{ $user->role == 'Admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div class="pt-4 border-t border-border-dark">
                <p class="text-sm text-text-muted mb-4">Kosongkan jika tidak ingin mengubah password</p>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Password Baru</label>
                        <input type="password" name="password"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        @error('password')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                               class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-border-dark">
                <a href="{{ route('users.index') }}" 
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
