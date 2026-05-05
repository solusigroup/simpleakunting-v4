<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SimpleAkunting</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: #0a0f1e;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Ambient background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 30% 20%, rgba(99, 102, 241, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse at 70% 80%, rgba(139, 92, 246, 0.06) 0%, transparent 60%);
            animation: drift 20s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes drift {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(20px, -20px);
            }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 1.5rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header .logo {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .login-header .badge {
            display: inline-block;
            padding: 0.3rem 0.85rem;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 99px;
            font-size: 0.75rem;
            color: #818cf8;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .login-header p {
            color: #64748b;
            font-size: 0.9rem;
        }

        .card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 16px;
            padding: 2rem;
            backdrop-filter: blur(12px);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(15, 23, 42, 0.7);
            color: #e2e8f0;
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: all 0.2s;
        }

        .form-group input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .form-group input::placeholder {
            color: #475569;
        }

        /* Password toggle */
        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #818cf8;
        }

        .form-group input#password {
            padding-right: 2.75rem;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .remember-row input[type="checkbox"] {
            accent-color: #6366f1;
            width: 16px;
            height: 16px;
        }

        .remember-row label {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 0;
            text-transform: none;
            letter-spacing: 0;
            font-weight: 500;
        }

        .error-text {
            color: #f87171;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .alert-error {
            padding: 0.85rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .btn-login {
            width: 100%;
            padding: 0.8rem;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
        }

        .btn-login:active {
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <div class="badge">🔧 Admin Panel</div>
            <h1 class="logo">SimpleAkunting</h1>
            <p>Masuk sebagai administrator platform</p>
        </div>

        <div class="card">
            @if ($errors->any())
                <div class="alert-error">
                    ❌ {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="kurniawan@petalmail.com">
                    @error('email') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required placeholder="••••••••">
                        <button type="button" id="togglePassword" class="password-toggle"
                            title="Tampilkan/Sembunyikan Kata Sandi">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="eye-icon">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                    @error('password') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="btn-login">🔐 Masuk</button>
            </form>
        </div>
    </div>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');
        const eyeIcon = togglePassword.querySelector('svg');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // toggle the eye icon (optional: switch to eye-off icon)
            if (type === 'text') {
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        });
    </script>
</body>

</html>