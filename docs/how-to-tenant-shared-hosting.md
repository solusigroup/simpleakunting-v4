# How-To: Membuat Tenant Baru di Shared Hosting (Step-by-Step)

Panduan ini menjelaskan langkah-langkah teknis dan administratif untuk menambahkan tenant baru pada aplikasi SimpleAkunting V4 yang di-host di Shared Hosting (seperti cPanel/DirectAdmin).

---

## 1. Persiapan Infrastruktur (cPanel)

Sebelum membuat tenant di aplikasi, pastikan infrastruktur domain dan database siap.

### A. Pengaturan Wildcard Subdomain
Agar tenant dapat otomatis menggunakan subdomain (contoh: `tenant1.v4.simpleakunting.biz.id`), Anda harus mengaktifkan Wildcard Subdomain.
1. Login ke **cPanel**.
2. Buka menu **Domains** (atau Subdomains).
3. Buat subdomain baru dengan nama `*`.
4. Pastikan **Document Root** menunjuk ke folder `public_html/public` (atau folder `public` aplikasi Anda).

### B. Izin Pembuatan Database
Aplikasi ini secara otomatis mencoba membuat database baru untuk setiap tenant. Di Shared Hosting, seringkali user database tidak memiliki izin `CREATE DATABASE`.
- Pastikan User Database yang Anda gunakan di `.env` memiliki hak akses penuh.
- Jika pembuatan database otomatis gagal, Anda mungkin perlu menyesuaikan `config/tenancy.php` untuk menggunakan prefix yang sesuai dengan username cPanel Anda (contoh: `cpuser_simpleak_`).

---

## 2. Membuat Tenant Lewat Admin Panel

1. Akses halaman Admin Central di: `https://v4.simpleakunting.biz.id/admin/login`.
2. Login dengan akun **Administrator Platform**.
3. Buka menu **Tenants** dan klik **Create New Tenant**.
4. Isi data berikut:
   - **Name**: Nama Perusahaan/Tenant (contoh: PT Maju Jaya).
   - **Email**: Email penanggung jawab tenant.
   - **Subdomain**: Nama subdomain unik (contoh: `majujaya`).
   - **Plan**: Pilih paket (Free, Starter, atau Pro).
5. Klik **Create**.

> [!NOTE]
> Sistem akan otomatis:
> 1. Membuat record Tenant.
> 2. Membuat Database tenant.
> 3. Menjalankan migrasi tabel (`tenants:migrate`).
> 4. Mengisi data awal (`tenants:seed`).

---

## 3. Konfigurasi Awal Tenant (Setup User)

Setelah tenant berhasil dibuat, Anda perlu memberikan akses login pertama kali untuk pemilik tenant tersebut.

1. Di daftar Tenant, klik tombol **Show** atau **Edit** pada tenant yang baru dibuat.
2. Cari bagian **Tenant User Management**.
3. Masukkan **Username** dan **Password** untuk administrator tenant tersebut.
4. Klik **Update**.
5. Berikan URL akses (`https://majujaya.v4.simpleakunting.biz.id`) beserta kredensial tersebut kepada klien.

---

## 4. Troubleshooting (Jika Terjadi Error)

### Error: "Tenant created but database provisioning failed"
Jika muncul error ini saat klik Create:
1. Buka cPanel **MySQL Databases**.
2. Buat database secara manual dengan nama sesuai prefix di config (contoh: `cpuser_simpleak_dbv4_tenant-majujaya`).
3. Tambahkan User Database ke database tersebut dengan hak akses penuh (All Privileges).
4. Jalankan perintah migrasi via SSH (atau menu Terminal di cPanel):
   ```bash
   php artisan tenants:migrate --tenants=majujaya
   php artisan tenants:seed --tenants=majujaya
   ```

### Error: "404 Not Found" pada Subdomain
Pastikan:
1. DNS untuk wildcard (`*.v4.simpleakunting.biz.id`) sudah diarahkan ke IP server.
2. Tunggu proses propagasi DNS (biasanya 5-30 menit).
3. Pastikan domain utama (`v4.simpleakunting.biz.id`) sudah terdaftar di `CENTRAL_DOMAIN` pada file `.env`.

---

*Dokumentasi ini dibuat untuk SimpleAkunting V4*  
*Terakhir diperbarui: April 2026*
