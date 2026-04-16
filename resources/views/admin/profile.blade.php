@extends('admin.layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="page-header">
    <h1>👤 Profil & Pengaturan</h1>
    <div class="breadcrumb">
        <a href="{{ route('admin.index') }}">Dashboard</a>
        <span class="sep">/</span>
        <span>Profil Saya</span>
    </div>
</div>

<div class="detail-grid">
    <!-- Profile Information -->
    <div class="card">
        <div class="card-header">
            <h2>Informasi Profil</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div style="margin-top: 2rem;">
                    <hr style="border: none; border-top: 1px solid rgba(148, 163, 184, 0.1); margin-bottom: 2rem;">
                    <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1.25rem;">Ubah Kata Sandi</h2>
                    <p class="form-hint" style="margin-bottom: 1.5rem;">Biarkan kosong jika tidak ingin mengubah kata sandi.</p>
                </div>

                <div class="form-group">
                    <label for="current_password">Kata Sandi Saat Ini</label>
                    <div style="position: relative;">
                        <input type="password" name="current_password" id="current_password" class="form-control" placeholder="••••••••">
                        <button type="button" class="toggle-password" data-target="current_password" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    @error('current_password') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="new_password">Kata Sandi Baru</label>
                    <div style="position: relative;">
                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="••••••••">
                        <button type="button" class="toggle-password" data-target="new_password" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    @error('new_password') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation">Konfirmasi Kata Sandi Baru</label>
                    <div style="position: relative;">
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="••••••••">
                    </div>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            if (type === 'text') {
                this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            } else {
                this.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            }
        });
    });
</script>
@endpush
@endsection
