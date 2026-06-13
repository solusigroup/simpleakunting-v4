@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
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
            <div class="label">Plan Starter</div>
            <div class="value amber">{{ $tenants->where('plan', 'starter')->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Plan Pro</div>
            <div class="value green">{{ $tenants->where('plan', 'pro')->count() }}</div>
        </div>
    </div>

    <div class="page-header">
        <h1>Daftar Tenant</h1>
        <a href="{{ route('admin.create') }}" class="btn btn-primary">+ Buat Tenant Baru</a>
    </div>

    <div class="card">
        @if($tenants->isEmpty())
            <div class="empty-state">
                <div class="icon">🏢</div>
                <p>Belum ada tenant terdaftar.</p>
                <a href="{{ route('admin.create') }}" class="btn btn-primary">Buat Tenant Pertama</a>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Email</th>
                        <th>Subdomain</th>
                        <th>Plan</th>
                        <th>Jurnal</th>
                        <th>Dibuat</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenants as $tenant)
                        @php
                            $subdomain = $tenant->domains->first()?->domain ?? '-';
                            $protocol = request()->getScheme();
                            $fullUrl = "{$protocol}://{$subdomain}.{$centralDomain}";
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.show', $tenant) }}" style="color:#e2e8f0; text-decoration:none;">
                                    <strong>{{ $tenant->name }}</strong>
                                </a>
                                <br><small style="color:#4b5563;">ID: {{ $tenant->id }}</small>
                            </td>
                            <td style="color:#94a3b8;">{{ $tenant->email ?? '-' }}</td>
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
                            <td>
                                <span style="font-family: monospace; background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.3); color: #818cf8; padding: 0.2rem 0.55rem; border-radius: 6px; font-weight: bold; font-size: 0.85rem;">
                                    {{ $tenant->journal_count }}
                                </span>
                            </td>
                            <td style="color:#64748b; font-size:0.8rem;">
                                {{ $tenant->created_at?->format('d M Y') }}
                            </td>
                            <td>
                                <div class="action-bar" style="justify-content: flex-end;">
                                    <a href="{{ route('admin.show', $tenant) }}" class="btn btn-secondary btn-sm" title="Detail">👁️</a>
                                    <a href="{{ route('admin.edit', $tenant) }}" class="btn btn-warning btn-sm" title="Edit">✏️</a>
                                    <form action="{{ route('admin.destroy', $tenant) }}" method="POST"
                                          onsubmit="return confirm('Hapus tenant {{ $tenant->name }}? Semua data akan hilang!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
