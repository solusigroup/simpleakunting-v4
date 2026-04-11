@extends('admin.layouts.app')

@section('title', $tenant->name)

@section('content')
    <div class="breadcrumb">
        <a href="{{ route('admin.index') }}">Dashboard</a>
        <span class="sep">›</span>
        <span>{{ $tenant->name }}</span>
    </div>

    <div class="page-header">
        <h1>{{ $tenant->name }}</h1>
        <div class="action-bar">
            @php
                $subdomain = $tenant->domains->first()?->domain ?? null;
                $fullUrl = $subdomain ? "http://{$subdomain}.{$centralDomain}" : '#';
            @endphp
            @if($subdomain)
                <a href="{{ $fullUrl }}" target="_blank" class="btn btn-secondary btn-sm">🌐 Buka Subdomain</a>
            @endif
            <a href="{{ route('admin.edit', $tenant) }}" class="btn btn-warning btn-sm">✏️ Edit</a>
            <form action="{{ route('admin.destroy', $tenant) }}" method="POST"
                  onsubmit="return confirm('Hapus tenant {{ $tenant->name }}? Semua data akan hilang!')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">🗑️ Hapus</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h2>Informasi Tenant</h2>
            <span class="plan-badge plan-{{ $tenant->plan ?? 'free' }}">{{ $tenant->plan ?? 'free' }}</span>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="label">Tenant ID</div>
                    <div class="value" style="font-family:monospace; color:#a78bfa;">{{ $tenant->id }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Nama Perusahaan</div>
                    <div class="value">{{ $tenant->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Email Admin</div>
                    <div class="value">{{ $tenant->email ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Plan</div>
                    <div class="value">
                        <span class="plan-badge plan-{{ $tenant->plan ?? 'free' }}" style="font-size:0.85rem; padding:0.3rem 0.85rem;">
                            {{ ucfirst($tenant->plan ?? 'free') }}
                        </span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="label">Tanggal Dibuat</div>
                    <div class="value">{{ $tenant->created_at?->format('d F Y, H:i') ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Terakhir Diperbarui</div>
                    <div class="value">{{ $tenant->updated_at?->format('d F Y, H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header">
            <h2>Domain</h2>
        </div>
        <div class="card-body">
            @if($tenant->domains->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th>Subdomain</th>
                            <th>URL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenant->domains as $domain)
                            @php $domainUrl = "http://{$domain->domain}.{$centralDomain}"; @endphp
                            <tr>
                                <td style="font-family:monospace; color:#60a5fa;">{{ $domain->domain }}</td>
                                <td>
                                    <a href="{{ $domainUrl }}" target="_blank" class="domain-link">
                                        {{ $domainUrl }} ↗
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color:#64748b; text-align:center; padding:1rem;">Belum ada domain terdaftar.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Database</h2>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="label">Nama Database</div>
                    <div class="value" style="font-family:monospace; color:#4ade80;">{{ $dbName }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
