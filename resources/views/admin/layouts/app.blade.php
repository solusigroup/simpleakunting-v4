<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - SimpleAkunting</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0a0f1e;
            color: #e2e8f0;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: rgba(15, 23, 42, 0.95);
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            padding: 0.85rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar .logo {
            font-size: 1.35rem;
            font-weight: 800;
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .navbar .badge {
            padding: 0.25rem 0.75rem;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 99px;
            font-size: 0.75rem;
            color: #818cf8;
            font-weight: 600;
        }
        .navbar .user-info {
            font-size: 0.85rem;
            color: #94a3b8;
        }
        .navbar .user-info strong {
            color: #e2e8f0;
        }

        /* Container */
        .container { max-width: 1140px; margin: 0 auto; padding: 2rem; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }
        .btn-primary:hover { box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4); }
        .btn-secondary {
            background: rgba(148, 163, 184, 0.1);
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        .btn-secondary:hover { background: rgba(148, 163, 184, 0.15); color: #e2e8f0; }
        .btn-danger {
            background: rgba(239, 68, 68, 0.12);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }
        .btn-danger:hover { background: rgba(239, 68, 68, 0.2); }
        .btn-warning {
            background: rgba(245, 158, 11, 0.12);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }
        .btn-warning:hover { background: rgba(245, 158, 11, 0.2); }
        .btn-sm { padding: 0.4rem 0.75rem; font-size: 0.8rem; }
        .btn-logout {
            background: none;
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
        }
        .btn-logout:hover { background: rgba(239, 68, 68, 0.1); }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #4ade80;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
        }

        /* Cards */
        .card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 14px;
            overflow: hidden;
            backdrop-filter: blur(8px);
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header h2 {
            font-size: 1.1rem;
            font-weight: 700;
        }
        .card-body { padding: 1.5rem; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        thead { background: rgba(15, 23, 42, 0.5); }
        th {
            padding: 0.85rem 1.25rem;
            text-align: left;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }
        td {
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            border-top: 1px solid rgba(148, 163, 184, 0.06);
        }
        tr { transition: all 0.15s ease; }
        tr:hover td { background: rgba(99, 102, 241, 0.04); }

        /* Badges */
        .plan-badge {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            border-radius: 99px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .plan-free { background: rgba(148, 163, 184, 0.15); color: #94a3b8; }
        .plan-starter { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
        .plan-pro { background: rgba(168, 85, 247, 0.15); color: #c084fc; }

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(148, 163, 184, 0.1);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            transition: all 0.2s ease;
        }
        .stat-card:hover { border-color: rgba(99, 102, 241, 0.3); }
        .stat-card .label { font-size: 0.8rem; color: #64748b; margin-bottom: 0.35rem; font-weight: 500; }
        .stat-card .value { font-size: 2.25rem; font-weight: 800; }
        .stat-card .value.blue { color: #60a5fa; }
        .stat-card .value.purple { color: #a78bfa; }
        .stat-card .value.green { color: #4ade80; }
        .stat-card .value.amber { color: #fbbf24; }

        /* Links */
        .domain-link {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s;
        }
        .domain-link:hover { color: #93c5fd; text-decoration: underline; }

        /* Forms */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .form-control {
            width: 100%;
            padding: 0.7rem 1rem;
            border-radius: 8px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(15, 23, 42, 0.6);
            color: #e2e8f0;
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }
        .form-error {
            color: #f87171;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        .form-hint {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.3rem;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #64748b;
        }
        .empty-state .icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
        .empty-state p { margin-bottom: 1rem; font-size: 1rem; }

        /* Page header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
        }
        .page-header h1 { font-size: 1.6rem; font-weight: 800; }

        /* Action bar */
        .action-bar {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        /* Detail list */
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        .detail-item {
            padding: 1rem 0;
        }
        .detail-item .label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.35rem;
        }
        .detail-item .value {
            font-size: 1rem;
            color: #e2e8f0;
            font-weight: 500;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 1.25rem;
        }
        .breadcrumb a {
            color: #60a5fa;
            text-decoration: none;
        }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb .sep { color: #475569; }

        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .page-header { flex-direction: column; gap: 1rem; align-items: flex-start; }
            .stats { grid-template-columns: 1fr 1fr; }
            .detail-grid { grid-template-columns: 1fr; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('admin.index') }}" class="logo">SimpleAkunting</a>
        <div class="navbar-right">
            <span class="badge">🔧 Admin Panel</span>
            @if(Auth::guard('admin')->check())
                <span class="user-info">
                    👤 <strong>{{ Auth::guard('admin')->user()->name }}</strong>
                </span>
                <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-logout">Logout</button>
                </form>
            @endif
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">❌ {{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
