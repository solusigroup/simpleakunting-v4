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

                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(148, 163, 184, 0.1);">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; color: #60a5fa;">👤 Buat/Reset Akun Administrator Tenant</h3>
                    <p class="form-hint" style="margin-bottom: 1.25rem;">Isi bagian ini jika Anda ingin membuat akun administrator baru atau mereset kata sandi untuk tenant tersebut dalam database tenant mereka sendiri.</p>

                    <div class="form-group">
                        <label for="tenant_user_name">Nama Administrator</label>
                        <input type="text" id="tenant_user_name" name="tenant_user_name" class="form-control"
                               placeholder="Contoh: Admin {{ $tenant->name }}" value="{{ old('tenant_user_name') }}">
                        @error('tenant_user_name') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="tenant_user_email">Email Administrator</label>
                        <input type="email" id="tenant_user_email" name="tenant_user_email" class="form-control"
                               placeholder="{{ $tenant->email }}" value="{{ old('tenant_user_email') }}">
                        @error('tenant_user_email') <div class="form-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="tenant_user_password">Password</label>
                        <div style="position: relative;">
                            <input type="password" id="tenant_user_password" name="tenant_user_password" class="form-control"
                                   placeholder="Min. 8 karakter">
                            <button type="button" id="toggleTenantPassword" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        @error('tenant_user_password') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="action-bar" style="margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                    <a href="{{ route('admin.show', $tenant) }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@push('scripts')
<script>
    document.getElementById('toggleTenantPassword').addEventListener('click', function() {
        const input = document.getElementById('tenant_user_password');
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        
        if (type === 'text') {
            this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
        } else {
            this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        }
    });
</script>
@endpush
@endsection
