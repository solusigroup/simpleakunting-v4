@extends('admin.layouts.app')

@section('title', 'Buat Tenant Baru')

@section('content')
    <div class="breadcrumb">
        <a href="{{ route('admin.index') }}">Dashboard</a>
        <span class="sep">›</span>
        <span>Buat Tenant Baru</span>
    </div>

    <div class="page-header">
        <h1>Buat Tenant Baru</h1>
    </div>

    <div class="card" style="max-width:640px;">
        <div class="card-body">
            <form action="{{ route('admin.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Nama Perusahaan</label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="{{ old('name') }}" required
                           placeholder="PT. Contoh Sukses Mandiri">
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Admin</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="{{ old('email') }}" required
                           placeholder="admin@perusahaan.com">
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="subdomain">Subdomain</label>
                    <input type="text" id="subdomain" name="subdomain" class="form-control"
                           value="{{ old('subdomain') }}" required
                           placeholder="contoh-sukses" oninput="updatePreview()">
                    <div class="form-hint">
                        URL: <span id="preview" style="color:#60a5fa;">___</span>.{{ env('CENTRAL_DOMAIN', 'simpleakunting4-0.test') }}
                    </div>
                    @error('subdomain') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="plan">Plan</label>
                    <select id="plan" name="plan" class="form-control">
                        <option value="free" {{ old('plan') === 'free' ? 'selected' : '' }}>Free</option>
                        <option value="starter" {{ old('plan') === 'starter' ? 'selected' : '' }}>Starter</option>
                        <option value="pro" {{ old('plan') === 'pro' ? 'selected' : '' }}>Pro</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                    🚀 Buat Tenant
                </button>
            </form>

            <p style="margin-top:1rem; font-size:0.8rem; color:#64748b; text-align:center;">
                Password default admin tenant: <code style="color:#a78bfa;">password</code>
            </p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function updatePreview() {
        const val = document.getElementById('subdomain').value.toLowerCase().replace(/[^a-z0-9-]/g, '');
        document.getElementById('preview').textContent = val || '___';
    }
</script>
@endpush
