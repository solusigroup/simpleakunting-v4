<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Badge -->
    <div class="cert-badge animate-fadeInUp">
        Sistem Akuntansi Terpercaya
    </div>

    <!-- Hero Title -->
    <h1 class="hero-title animate-fadeInUp animate-delay-1">
        Kelola <span class="highlight">Keuangan</span>,<br>
        Wujudkan Keberhasilan.
    </h1>

    <!-- Description -->
    <p class="hero-description animate-fadeInUp animate-delay-2">
        Kami menyediakan solusi akuntansi modern untuk UMKM & BUMDesa. 
        Transformasi pengelolaan keuangan Anda dengan sistem yang mudah dan efisien.
    </p>

    <!-- Auth Card -->
    <div class="auth-card animate-fadeInUp animate-delay-2">
        <h2 class="auth-card-title">Masuk ke Akun Anda</h2>
        
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div style="margin-bottom: 1rem;">
                <label for="email" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-secondary);">
                    Email
                </label>
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       autocomplete="username"
                       class="auth-input"
                       placeholder="nama@email.com">
                <x-input-error :messages="$errors->get('email')" class="mt-2" style="color: #f87171; font-size: 0.875rem; margin-top: 0.5rem;" />
            </div>

            <!-- Password -->
            <div style="margin-bottom: 1rem;">
                <label for="password" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-secondary);">
                    Password
                </label>
                <div style="position: relative;">
                    <input id="password" 
                           type="password" 
                           name="password" 
                           required 
                           autocomplete="current-password"
                           class="auth-input"
                           style="padding-right: 3rem;"
                           placeholder="Masukkan password Anda">
                    <button type="button" 
                            onclick="togglePassword()" 
                            style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 0.25rem; transition: color 0.3s;"
                            onmouseover="this.style.color='#fff'" 
                            onmouseout="this.style.color='var(--text-secondary)'">
                        <span id="eye-icon" class="material-symbols-outlined" style="font-size: 20px;">visibility_off</span>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" style="color: #f87171; font-size: 0.875rem; margin-top: 0.5rem;" />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                <label for="remember_me" style="display: inline-flex; align-items: center; cursor: pointer;">
                    <input id="remember_me" 
                           type="checkbox" 
                           style="width: 16px; height: 16px; border-radius: 4px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); accent-color: var(--primary-orange);" 
                           name="remember">
                    <span style="margin-left: 0.5rem; font-size: 0.875rem; color: var(--text-secondary);">Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" 
                       style="font-size: 0.875rem; color: var(--primary-orange); text-decoration: none; transition: color 0.3s;">
                        Lupa password?
                    </a>
                @endif
            </div>

            <!-- Login Button -->
            <button type="submit" class="auth-btn">
                <span class="material-symbols-outlined" style="font-size: 20px;">login</span>
                Masuk
            </button>

            <!-- Register Link -->
            <div style="text-align: center; padding-top: 1.25rem; margin-top: 1.25rem; border-top: 1px solid var(--border-color);">
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0;">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" style="color: var(--primary-orange); font-weight: 500; text-decoration: none;">
                        Daftar Sekarang
                    </a>
                </p>
            </div>
        </form>
    </div>

    <!-- Features -->
    <div class="features-row animate-fadeInUp animate-delay-3">
        <div class="feature-item">
            <div class="feature-icon">
                <span class="material-symbols-outlined">verified_user</span>
            </div>
            <div class="feature-text">
                <h4>Aman & Terpercaya</h4>
                <p>Standar Keamanan Tinggi</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon">
                <span class="material-symbols-outlined">bolt</span>
            </div>
            <div class="feature-text">
                <h4>Cepat & Efisien</h4>
                <p>Sistem Modern</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="login-footer" style="margin-top: 2rem;">
        <a href="https://simpleakunting.my.id/riwayathidupku.html" target="_blank" rel="noopener noreferrer">
            © {{ date('Y') }} Simple Akunting. All rights reserved.
        </a>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = 'visibility';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = 'visibility_off';
            }
        }
    </script>
</x-guest-layout>
