<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Simple Akunting') }} - Masuk</title>

    <!-- PWA -->
    <meta name="theme-color" content="#e86c25">
    <link rel="manifest" href="{{ global_asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ global_asset('images/logo_sa.png') }}">
    <link rel="icon" type="image/png" href="{{ global_asset('images/logo_sa.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Simple Akunting">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-orange: #e86c25;
            --primary-orange-light: #ff8c45;
            --bg-dark: #0a1628;
            --bg-darker: #050d18;
            --bg-card: rgba(15, 35, 60, 0.6);
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-darker) 0%, var(--bg-dark) 50%, #0f2235 100%);
            min-height: 100vh;
            margin: 0;
            color: var(--text-primary);
        }

        /* Navigation */
        .nav-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            background: rgba(10, 22, 40, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .nav-logo img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .nav-logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .nav-logo-text span {
            color: var(--primary-orange);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: var(--text-primary);
        }

        .nav-login-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-login-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--text-primary);
        }

        /* Main Layout */
        .main-container {
            min-height: 100vh;
            display: flex;
            padding-top: 70px;
        }

        .content-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 4rem;
            max-width: 55%;
        }

        .hero-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        /* Badge */
        .cert-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(232, 108, 37, 0.15);
            border: 1px solid rgba(232, 108, 37, 0.3);
            border-radius: 6px;
            color: var(--primary-orange);
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .cert-badge::before {
            content: '●';
            font-size: 0.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Typography */
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin: 0 0 1.5rem 0;
        }

        .hero-title .highlight {
            color: var(--primary-orange);
        }

        .hero-description {
            color: var(--text-secondary);
            font-size: 1.1rem;
            line-height: 1.7;
            margin: 0 0 2rem 0;
            max-width: 450px;
        }

        /* Auth Form Card */
        .auth-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 2rem;
            margin-top: 1rem;
            max-width: 420px;
        }

        .auth-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0 0 1.5rem 0;
        }

        .auth-input {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.3s;
            outline: none;
        }

        .auth-input::placeholder {
            color: var(--text-secondary);
        }

        .auth-input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 3px rgba(232, 108, 37, 0.15);
        }

        .auth-btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--primary-orange-light) 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(232, 108, 37, 0.4);
        }

        .secondary-btn {
            padding: 0.875rem 1.5rem;
            background: transparent;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .secondary-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--text-primary);
        }

        /* Hero Image */
        .hero-image-container {
            position: relative;
            width: 100%;
            max-width: 450px;
        }

        .hero-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
        }

        .hero-stats {
            position: absolute;
            bottom: -20px;
            right: -20px;
            background: rgba(30, 50, 70, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .hero-stats-avatars {
            display: flex;
        }

        .hero-stats-avatars img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid var(--bg-dark);
            margin-left: -10px;
        }

        .hero-stats-avatars img:first-child {
            margin-left: 0;
        }

        .hero-stats-text {
            display: flex;
            flex-direction: column;
        }

        .hero-stats-number {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .hero-stats-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        /* Features */
        .features-row {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(232, 108, 37, 0.15);
            border-radius: 10px;
            color: var(--primary-orange);
        }

        .feature-text h4 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .feature-text p {
            margin: 0;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        /* Footer */
        .login-footer {
            text-align: center;
            padding: 1rem;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .login-footer a {
            color: var(--primary-orange);
            text-decoration: none;
            transition: color 0.3s;
        }

        .login-footer a:hover {
            color: var(--primary-orange-light);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-delay-1 {
            animation-delay: 0.1s;
            animation-fill-mode: both;
        }

        .animate-delay-2 {
            animation-delay: 0.2s;
            animation-fill-mode: both;
        }

        .animate-delay-3 {
            animation-delay: 0.3s;
            animation-fill-mode: both;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-container {
                flex-direction: column;
            }

            .content-section {
                max-width: 100%;
                padding: 2rem;
            }

            .hero-section {
                padding: 0 2rem 2rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-image {
                height: 350px;
            }
        }

        @media (max-width: 640px) {
            .nav-links {
                display: none;
            }

            .content-section {
                padding: 1.5rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .auth-card {
                padding: 1.5rem;
            }

            .features-row {
                flex-direction: column;
                gap: 1rem;
            }

            .hero-stats {
                right: 10px;
                bottom: -15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav-bar">
        <a href="/" class="nav-logo">
            <img src="{{ global_asset('images/logo_sa.png') }}" alt="Logo">
            <span class="nav-logo-text">Simple<span>Akunting</span></span>
        </a>
        <div class="nav-links">
            <a href="/" class="nav-link">Beranda</a>
            <a href="/#fitur" class="nav-link">Fitur</a>
            <a href="/#tentang" class="nav-link">Tentang</a>
            <a href="/#kontak" class="nav-link">Kontak</a>
            <a href="{{ route('login') }}" class="nav-login-btn">
                <span class="material-symbols-outlined" style="font-size: 18px;">login</span>
                Login
            </a>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Content Section -->
        <div class="content-section">
            {{ $slot }}
        </div>

        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-image-container animate-fadeInUp animate-delay-3">
                <img src="{{ asset('images/hero-accounting.jpg') }}" alt="Accounting System" class="hero-image" onerror="this.src='https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&h=1000&fit=crop'">
                <div class="hero-stats">
                    <div class="hero-stats-avatars">
                        <img src="https://i.pravatar.cc/100?img=1" alt="User 1">
                        <img src="https://i.pravatar.cc/100?img=2" alt="User 2">
                        <img src="https://i.pravatar.cc/100?img=3" alt="User 3">
                    </div>
                    <div class="hero-stats-text">
                        <span class="hero-stats-number">500+ Pengguna</span>
                        <span class="hero-stats-label">Percaya pada kami</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- PWA Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('PWA Service Worker registered', reg))
                    .catch(err => console.log('PWA Service Worker failed', err));
            });
        }
    </script>
</body>
</html>

