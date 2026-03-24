<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - SimpleAkunting</title>
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
            backdrop-filter: blur(12px);
        }
        .navbar .logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .navbar .badge {
            padding: 0.25rem 0.75rem;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 99px;
            font-size: 0.75rem;
            color: #818cf8;
        }
        .container { max-width: 1100px; margin: 0 auto; padding: 2rem; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .header h1 { font-size: 1.75rem; font-weight: 700; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
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
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .btn-danger:hover { background: rgba(239, 68, 68, 0.25); }
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #4ade80;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
        }
        .card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: rgba(15, 23, 42, 0.5); }
        th {
            padding: 0.85rem 1.25rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }
        td {
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            border-top: 1px solid rgba(148, 163, 184, 0.08);
        }
        tr:hover td { background: rgba(99, 102, 241, 0.04); }
        .domain-link {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 500;
        }
        .domain-link:hover { text-decoration: underline; }
        .plan-badge {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .plan-free { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }
        .plan-starter { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
        .plan-pro { background: rgba(168, 85, 247, 0.15); color: #c084fc; }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #64748b;
        }
        .empty-state p { margin-bottom: 1rem; font-size: 1rem; }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 12px;
            padding: 1.25rem;
        }
        .stat-card .label { font-size: 0.8rem; color: #64748b; margin-bottom: 0.25rem; }
        .stat-card .value { font-size: 2rem; font-weight: 700; }
        .stat-card .value.blue { color: #60a5fa; }
        .stat-card .value.purple { color: #a78bfa; }
        .stat-card .value.green { color: #4ade80; }
    </style>
</head>
<body>
    <nav class="navbar">
        <span class="logo">SimpleAkunting</span>
        <span class="badge">🔧 Admin Panel</span>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif

        <div class="stats">
            <div class="stat-card">
                <div class="label">Total Tenant</div>
                <div class="value blue">{{ $tenants->count() }}</div>
            </div>
            <div class="stat-card">
                <div class="label">Plan Free</div>
                <div class="value purple">{{ $tenants->where('plan', 'free')->count() }}</div>
            </div>
            <div class="stat-card">
                <div class="label">Plan Pro</div>
                <div class="value green">{{ $tenants->where('plan', 'pro')->count() }}</div>
            </div>
        </div>

        <div class="header">
            <h1>Daftar Tenant</h1>
            <a href="/admin/create" class="btn btn-primary">+ Buat Tenant Baru</a>
        </div>

        <div class="card">
            @if($tenants->isEmpty())
                <div class="empty-state">
                    <p>Belum ada tenant terdaftar.</p>
                    <a href="/admin/create" class="btn btn-primary">Buat Tenant Pertama</a>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Email</th>
                            <th>Subdomain</th>
                            <th>Plan</th>
                            <th>Dibuat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $tenant)
                            @php
                                $subdomain = $tenant->domains->first()?->domain ?? '-';
                                $fullUrl = "http://{$subdomain}.{$centralDomain}";
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $tenant->name }}</strong>
                                    <br><small style="color:#64748b">ID: {{ $tenant->id }}</small>
                                </td>
                                <td>{{ $tenant->email ?? '-' }}</td>
                                <td>
                                    <a href="{{ $fullUrl }}" target="_blank" class="domain-link">
                                        {{ $subdomain }}.{{ $centralDomain }}
                                    </a>
                                </td>
                                <td>
                                    <span class="plan-badge plan-{{ $tenant->plan ?? 'free' }}">
                                        {{ $tenant->plan ?? 'free' }}
                                    </span>
                                </td>
                                <td style="color:#64748b; font-size:0.8rem;">
                                    {{ $tenant->created_at?->format('d M Y') }}
                                </td>
                                <td>
                                    <form action="/admin/{{ $tenant->id }}" method="POST"
                                          onsubmit="return confirm('Hapus tenant {{ $tenant->name }}? Semua data akan hilang!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="font-size:0.8rem; padding:0.4rem 0.75rem;">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</body>
</html>
