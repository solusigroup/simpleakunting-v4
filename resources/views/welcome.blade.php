<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleAkunting - Multi-Tenant Accounting</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }
        .logo {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #60a5fa, #a78bfa, #f472b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        .subtitle {
            font-size: 1.15rem;
            color: #94a3b8;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }
        .badge {
            display: inline-block;
            padding: 0.35rem 1rem;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 99px;
            font-size: 0.85rem;
            color: #818cf8;
            margin-bottom: 1.5rem;
        }
        .info-box {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.12);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: left;
            margin-top: 1rem;
        }
        .info-box h3 {
            color: #60a5fa;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .info-box code {
            display: block;
            background: #0f172a;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            font-family: 'Cascadia Code', 'Fira Code', monospace;
            font-size: 0.85rem;
            color: #a5f3fc;
            margin-bottom: 0.5rem;
            overflow-x: auto;
        }
        .info-box p {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 0.5rem;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="badge">🏢 Multi-Tenant Platform</div>
        <h1 class="logo">SimpleAkunting</h1>
        <p class="subtitle">
            Aplikasi Akuntansi Multi-Tenant. Setiap perusahaan memiliki database terpisah dengan isolasi penuh.
        </p>
        
        <div class="info-box">
            <h3>📋 Memulai</h3>
            <code>php artisan tenant:create "Nama Perusahaan" --email=admin@contoh.com</code>
            <p>
                Jalankan perintah di atas untuk membuat tenant baru. Database, user admin, dan subdomain akan otomatis disediakan.
                Kemudian akses via subdomain, contoh: <strong>nama-perusahaan.simpleakunting.test</strong>
            </p>
        </div>
    </div>
</body>
</html>
