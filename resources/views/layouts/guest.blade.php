<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Simple Akunting') }} - Masuk</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Enhanced auth styling */
        body {
            background: linear-gradient(135deg, #0a150e 0%, #112117 50%, #1a2e22 100%);
        }
        
        .auth-card {
            backdrop-filter: blur(16px);
            background: rgba(26, 46, 34, 0.7);
            border: 1px solid rgba(54, 99, 72, 0.3);
        }
        
        .auth-input {
            background: rgba(17, 33, 23, 0.5);
            border: 1px solid rgba(54, 99, 72, 0.5);
            transition: all 0.3s ease;
        }
        
        .auth-input:focus {
            background: rgba(17, 33, 23, 0.8);
            border-color: #36e27b;
            box-shadow: 0 0 0 3px rgba(54, 226, 123, 0.1);
        }
        
        .auth-btn {
            background: linear-gradient(135deg, #36e27b 0%, #0bda43 100%);
            transition: all 0.3s ease;
        }
        
        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(54, 226, 123, 0.3);
        }
        
        .logo-glow {
            box-shadow: 0 0 40px rgba(54, 226, 123, 0.3);
        }
        
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
    </style>
</head>
<body class="font-body antialiased">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md animate-fadeInUp">
            <!-- Logo & Branding -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-surface-dark rounded-2xl logo-glow mb-4 overflow-hidden border border-border-dark">
                    <img src="{{ asset('images/logo_apartement.jpg') }}" alt="Logo" class="w-full h-full object-contain p-2">
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Simple Akunting</h1>
                <p class="text-text-muted text-sm">Sistem Akuntansi Modern untuk UMKM & BUMDesa</p>
            </div>

            <!-- Auth Card -->
            <div class="auth-card rounded-2xl p-8 shadow-2xl">
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-text-muted text-sm">
                    <a href="https://simpleakunting.my.id/riwayathidupku.html" target="_blank" rel="noopener noreferrer" class="hover:text-primary transition-colors">
                        © {{ date('Y') }} Simple Akunting. All rights reserved.
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
