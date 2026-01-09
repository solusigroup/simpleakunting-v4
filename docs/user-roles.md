# User Roles & Permissions

Dokumentasi ini menjelaskan sistem role dan hak akses pengguna di aplikasi Simple Akunting V4.

## 📋 Daftar Role

Aplikasi Simple Akunting V4 memiliki 4 (empat) role pengguna:

| Role | Deskripsi |
|------|-----------|
| **Administrator** | Administrator sistem dengan akses penuh ke semua fitur aplikasi |
| **Manajer** | Manajer/Supervisor yang dapat melakukan supervisi dan approval |
| **Operator** | Staff/Operator untuk operasional harian (role default) |
| **Peninjau** | Peninjau/Viewer yang hanya dapat melihat data |

## 🔐 Matriks Hak Akses

Berikut adalah matriks hak akses untuk setiap role:

| Kemampuan | Administrator | Manajer | Operator | Peninjau |
|-----------|:-------------:|:-------:|:--------:|:--------:|
| Create/Edit Data | ✅ | ✅ | ✅ | ❌ |
| Delete Data | ✅ | ✅ | ❌ | ❌ |
| Approve/Supervise | ✅ | ✅ | ❌ | ❌ |
| Manage Company Settings | ✅ | ❌ | ❌ | ❌ |
| Manage Users | ✅ | ❌ | ❌ | ❌ |
| View Reports | ✅ | ✅ | ✅ | ✅ |

## 📝 Penjelasan Detail

### Administrator
- Memiliki akses penuh ke seluruh fitur aplikasi
- Dapat mengelola pengaturan perusahaan
- Dapat mengelola pengguna (tambah, edit, hapus)
- Dapat melakukan semua operasi CRUD pada data
- User pertama yang mendaftar otomatis menjadi Administrator

### Manajer
- Dapat melakukan approval/supervisi transaksi
- Dapat menghapus data
- Dapat membuat dan mengedit data
- Dapat melihat semua laporan
- Tidak dapat mengakses pengaturan perusahaan dan manajemen user

### Operator
- Role default untuk pengguna baru
- Dapat membuat dan mengedit data transaksi
- Dapat melihat semua laporan
- Tidak dapat menghapus data
- Tidak dapat melakukan approval

### Peninjau
- Hanya memiliki akses baca (read-only)
- Dapat melihat semua data dan laporan
- Tidak dapat membuat, mengedit, atau menghapus data
- Cocok untuk auditor atau pihak yang hanya perlu memantau

## 🛠️ Implementasi Teknis

### Model User

Role didefinisikan sebagai enum di database dengan nilai:
```php
['Administrator', 'Manajer', 'Operator', 'Peninjau']
```

Helper methods tersedia di model `User`:
```php
$user->isAdmin();       // Check if Administrator
$user->isManajer();     // Check if Manajer
$user->isOperator();    // Check if Operator
$user->isPeninjau();    // Check if Peninjau

$user->canEdit();       // Can create/edit data
$user->canDelete();     // Can delete data
$user->canApprove();    // Can approve/supervise
$user->canManageCompany();  // Can manage company settings
$user->canManageUsers();    // Can manage users
$user->canViewReports();    // Can view reports
```

### Middleware

Gunakan middleware `role` untuk membatasi akses route:
```php
Route::middleware(['role:Administrator'])->group(function () {
    // Routes hanya untuk Administrator
});

Route::middleware(['role:Administrator,Manajer'])->group(function () {
    // Routes untuk Administrator dan Manajer
});
```

### Blade Directives

Gunakan kondisi berikut di Blade templates:
```blade
@if(auth()->user()->isAdmin())
    {{-- Konten khusus Administrator --}}
@endif

@if(auth()->user()->canEdit())
    {{-- Tombol edit/create --}}
@endif

@if(auth()->user()->canDelete())
    {{-- Tombol delete --}}
@endif
```

## 📌 Catatan Penting

1. **User Pertama**: User pertama yang mendaftar di sistem akan otomatis mendapat role `Administrator`
2. **Default Role**: User baru yang didaftarkan oleh Administrator akan mendapat role `Operator` secara default
3. **Perubahan Role**: Hanya Administrator yang dapat mengubah role pengguna lain
4. **Self-Protection**: Administrator tidak dapat menghapus atau mengubah role dirinya sendiri (untuk mencegah lockout)
