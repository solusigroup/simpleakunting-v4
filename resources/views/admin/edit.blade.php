@extends('admin.layouts.app')

@section('title', 'Edit ' . $tenant->name)

@section('content')
    <div class="breadcrumb">
        <a href="{{ route('admin.index') }}">Dashboard</a>
        <span class="sep">›</span>
        <a href="{{ route('admin.show', $tenant) }}">{{ $tenant->name }}</a>
        <span class="sep">›</span>
        <span>Edit</span>
    </div>

    <div class="page-header">
        <h1>Edit Tenant</h1>
    </div>

    <div class="card" style="max-width:640px;">
        <div class="card-body">
            <form action="{{ route('admin.update', $tenant) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nama Perusahaan</label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="{{ old('name', $tenant->name) }}" required>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Admin</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="{{ old('email', $tenant->email) }}" required>
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Subdomain</label>
                    @php $subdomain = $tenant->domains->first()?->domain ?? '-'; @endphp
                    <input type="text" class="form-control" value="{{ $subdomain }}.{{ $centralDomain }}" disabled
                           style="opacity:0.6;">
                    <div class="form-hint">Subdomain tidak bisa diubah setelah dibuat.</div>
                </div>

                <div class="form-group">
                    <label for="plan">Plan</label>
                    <select id="plan" name="plan" class="form-control">
                        <option value="free" {{ old('plan', $tenant->plan) === 'free' ? 'selected' : '' }}>Free</option>
                        <option value="starter" {{ old('plan', $tenant->plan) === 'starter' ? 'selected' : '' }}>Starter</option>
                        <option value="pro" {{ old('plan', $tenant->plan) === 'pro' ? 'selected' : '' }}>Pro</option>
                    </select>
                    @error('plan') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="action-bar" style="margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                    <a href="{{ route('admin.show', $tenant) }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
