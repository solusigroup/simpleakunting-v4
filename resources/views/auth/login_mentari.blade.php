<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Logo Mentari Commsindo -->
    <div class="flex justify-center mb-6">
        <img src="{{ asset('images/logo-mentari.png') }}" alt="Mentari Commsindo" class="h-16 object-contain">
    </div>

    <!-- Header -->
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-white mb-2">Masuk ke Akun Anda</h2>
        <p class="text-text-muted text-sm">Silakan masukkan kredensial Anda untuk melanjutkan</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

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
                   autofocus 
                   autocomplete="username"
                   class="auth-input w-full px-4 py-3 rounded-xl text-white placeholder-text-muted focus:outline-none"
                   placeholder="nama@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-white mb-2">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-primary">lock</span>
                    Password
                </span>
            </label>
            <div class="relative">
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="current-password"
                       class="auth-input w-full px-4 py-3 pr-12 rounded-xl text-white placeholder-text-muted focus:outline-none"
                       placeholder="Masukkan password Anda">
                <button type="button" 
                        onclick="togglePassword()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-white transition-colors focus:outline-none">
                    <span id="eyeIcon" class="material-symbols-outlined text-xl">visibility_off</span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <script>
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.textContent = 'visibility';
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.textContent = 'visibility_off';
                }
            }
        </script>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                <input id="remember_me" 
                       type="checkbox" 
                       class="rounded bg-surface-dark border-border-dark text-primary focus:ring-primary focus:ring-offset-0" 
                       name="remember">
                <span class="ms-2 text-sm text-text-muted group-hover:text-white transition">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" 
                   class="text-sm text-primary hover:text-accent-green transition">
                    Lupa password?
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <button type="submit" 
                class="auth-btn w-full py-3.5 rounded-xl font-semibold text-white flex items-center justify-center gap-2">
            <span class="material-symbols-outlined">login</span>
            Masuk
        </button>

        <!-- Register Link -->
        <div class="text-center pt-4 border-t border-border-dark">
            <p class="text-text-muted text-sm">
                Belum punya akun? 
                <a href="{{ route('register') }}" class="text-primary hover:text-accent-green font-medium transition">
                    Daftar Sekarang
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
