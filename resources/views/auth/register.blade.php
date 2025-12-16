<x-guest-layout>
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white mb-2">Buat Akun Baru</h2>
        <p class="text-text-muted text-sm">Daftar untuk mulai mengelola akuntansi bisnis Anda</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-white mb-2">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-primary">person</span>
                    Nama Lengkap
                </span>
            </label>
            <input id="name" 
                   type="text" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required 
                   autofocus 
                   autocomplete="name"
                   class="auth-input w-full px-4 py-3 rounded-xl text-white placeholder-text-muted focus:outline-none"
                   placeholder="Nama lengkap Anda">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-white mb-2">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-primary">mail</span>
                    Email
                </span>
            </label>
            <input id="email" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autocomplete="username"
                   class="auth-input w-full px-4 py-3 rounded-xl text-white placeholder-text-muted focus:outline-none"
                   placeholder="nama@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Company Name -->
        <div>
            <label for="company_name" class="block text-sm font-medium text-white mb-2">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-primary">business</span>
                    Nama Perusahaan/Usaha
                </span>
            </label>
            <input id="company_name" 
                   type="text" 
                   name="company_name" 
                   value="{{ old('company_name') }}" 
                   required
                   class="auth-input w-full px-4 py-3 rounded-xl text-white placeholder-text-muted focus:outline-none"
                   placeholder="PT. Contoh Usaha">
            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
        </div>

        <!-- Entity Type -->
        <div>
            <label for="entity_type" class="block text-sm font-medium text-white mb-2">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-primary">account_balance</span>
                    Jenis Entitas
                </span>
            </label>
            <select id="entity_type" 
                    name="entity_type" 
                    required
                    class="auth-input w-full px-4 py-3 rounded-xl text-white focus:outline-none">
                <option value="" class="bg-surface-dark">-- Pilih Jenis Entitas --</option>
                <option value="UMKM" {{ old('entity_type') == 'UMKM' ? 'selected' : '' }} class="bg-surface-dark">
                    UMKM (SAK Entitas Privat)
                </option>
                <option value="BUMDesa" {{ old('entity_type') == 'BUMDesa' ? 'selected' : '' }} class="bg-surface-dark">
                    BUMDesa (Kepmendesa 136/2022)
                </option>
            </select>
            <p class="mt-2 text-xs text-text-muted flex items-start gap-1">
                <span class="material-symbols-outlined text-sm">info</span>
                <span>Menentukan standar akuntansi dan template Chart of Accounts yang akan digunakan</span>
            </p>
            <x-input-error :messages="$errors->get('entity_type')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-white mb-2">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-primary">lock</span>
                    Password
                </span>
            </label>
            <input id="password" 
                   type="password" 
                   name="password" 
                   required 
                   autocomplete="new-password"
                   class="auth-input w-full px-4 py-3 rounded-xl text-white placeholder-text-muted focus:outline-none"
                   placeholder="Minimal 8 karakter">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-white mb-2">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-primary">lock_reset</span>
                    Konfirmasi Password
                </span>
            </label>
            <input id="password_confirmation" 
                   type="password" 
                   name="password_confirmation" 
                   required 
                   autocomplete="new-password"
                   class="auth-input w-full px-4 py-3 rounded-xl text-white placeholder-text-muted focus:outline-none"
                   placeholder="Ketik ulang password Anda">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Register Button -->
        <button type="submit" 
                class="auth-btn w-full py-3.5 rounded-xl font-semibold text-background-dark flex items-center justify-center gap-2 mt-6">
            <span class="material-symbols-outlined">person_add</span>
            Daftar Sekarang
        </button>

        <!-- Login Link -->
        <div class="text-center pt-4 border-t border-border-dark">
            <p class="text-text-muted text-sm">
                Sudah punya akun? 
                <a href="{{ route('login') }}" class="text-primary hover:text-accent-green font-medium transition">
                    Masuk di sini
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
