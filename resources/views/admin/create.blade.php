<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Tenant Baru - SimpleAkunting</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
        }
        .navbar {
            background: rgba(15, 23, 42, 0.95);
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .navbar .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .navbar a { color: #94a3b8; text-decoration: none; font-size: 0.9rem; }
        .navbar a:hover { color: #e2e8f0; }
        .container { max-width: 600px; margin: 0 auto; padding: 2rem; }
        h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; }
        .card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 12px;
            padding: 2rem;
        }
        .form-group { margin-bottom: 1.25rem; }
        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.4rem;
        }
        input, select {
            width: 100%;
            padding: 0.7rem 1rem;
            border-radius: 8px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(15, 23, 42, 0.6);
            color: #e2e8f0;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus, select:focus { border-color: #6366f1; }
        .subdomain-preview {
            margin-top: 0.35rem;
            font-size: 0.8rem;
            color: #64748b;
        }
        .subdomain-preview span { color: #60a5fa; }
        .error {
            color: #f87171;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            width: 100%;
            justify-content: center;
        }
        .btn-primary:hover { opacity: 0.9; }
        .btn-back {
            color: #94a3b8;
            background: none;
            padding: 0;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .alert-error {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <span class="logo">SimpleAkunting</span>
        <a href="/admin">← Kembali ke Admin Panel</a>
    </nav>

    <div class="container">
        @if(session('error'))
            <div class="alert-error">❌ {{ session('error') }}</div>
        @endif

        <h1>Buat Tenant Baru</h1>

        <div class="card">
            <form action="/admin" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Perusahaan</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           placeholder="PT. Contoh Sukses Mandiri">
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Admin</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           placeholder="admin@perusahaan.com">
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="subdomain">Subdomain</label>
                    <input type="text" id="subdomain" name="subdomain" value="{{ old('subdomain') }}" required
                           placeholder="contoh-sukses" oninput="updatePreview()">
                    <div class="subdomain-preview">
                        URL: <span id="preview">___</span>.{{ env('CENTRAL_DOMAIN', 'simpleakunting4-0.test') }}
                    </div>
                    @error('subdomain') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="plan">Plan</label>
                    <select id="plan" name="plan">
                        <option value="free" {{ old('plan') === 'free' ? 'selected' : '' }}>Free</option>
                        <option value="starter" {{ old('plan') === 'starter' ? 'selected' : '' }}>Starter</option>
                        <option value="pro" {{ old('plan') === 'pro' ? 'selected' : '' }}>Pro</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">🚀 Buat Tenant</button>
            </form>
        </div>

        <p style="margin-top:1rem; font-size:0.8rem; color:#64748b; text-align:center;">
            Password default admin: <code style="color:#a78bfa;">password</code>
        </p>
    </div>

    <script>
        function updatePreview() {
            const val = document.getElementById('subdomain').value.toLowerCase().replace(/[^a-z0-9-]/g, '');
            document.getElementById('preview').textContent = val || '___';
        }
    </script>
</body>
</html>
