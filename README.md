# Simple Akunting V4

<p align="center">
  <img src="public/images/logo_baru.jpg" alt="Simple Akunting" width="300">
</p>

<p align="center">
  <strong>Sistem Akuntansi Modern untuk UMKM & BUMDesa</strong>
</p>

<p align="center">
  <a href="#fitur">Fitur</a> •
  <a href="#instalasi">Instalasi</a> •
  <a href="#dokumentasi">Dokumentasi</a> •
  <a href="#teknologi">Teknologi</a>
</p>

---

## 📋 Tentang Aplikasi

**Simple Akunting V4** adalah aplikasi akuntansi berbasis web yang dirancang khusus untuk memenuhi kebutuhan pencatatan keuangan **UMKM** dan **BUMDesa** di Indonesia. Aplikasi ini mendukung standar akuntansi Indonesia termasuk **PSAK 69** untuk aset biologis.

### Keunggulan
- 🎯 **Sederhana & Mudah Digunakan** - Antarmuka modern dan intuitif
- 🏢 **Multi-Entity** - Mendukung UMKM dan BUMDesa dengan multi unit usaha
- 📊 **Laporan Lengkap** - Neraca, Laba Rugi, Arus Kas, LPE, dan lainnya
- 🌿 **PSAK 69** - Modul Aset Biologis untuk sektor agrikultur
- 🏭 **Manufacturing** - Assembly/BOM dan produksi
- 🔐 **Multi-Role** - Administrator, Manajer, Operator, Peninjau

---

## ✨ Fitur

### Master Data
- ✅ Chart of Accounts (Bagan Akun) dengan template UMKM/BUMDesa
- ✅ Kontak (Pelanggan & Pemasok)
- ✅ Persediaan / Inventory
- ✅ Aset Tetap dengan depresiasi
- ✅ Unit Usaha (khusus BUMDesa)

### Transaksi
- ✅ Penjualan (Invoice) dengan update stok otomatis
- ✅ Pembelian dengan update stok otomatis
- ✅ Penerimaan Kas
- ✅ Pengeluaran Kas
- ✅ Jurnal Umum (Manual Entry)
- ✅ Jurnal Penyesuaian
- ✅ Jurnal Penutup

### Laporan Keuangan
- 📊 Neraca (Balance Sheet) - Single & Komparatif
- 📊 Laba Rugi (Income Statement) - Single & Komparatif
- 📊 Arus Kas (Cash Flow Statement)
- 📊 Laporan Perubahan Ekuitas (LPE)
- 📊 Neraca Saldo (Trial Balance)
- 📊 Buku Besar (Ledger)
- 📊 Daftar Jurnal
- 📊 Laporan Penjualan & Pembelian
- 📊 Analisis Rasio Keuangan
- 📄 Export PDF untuk semua laporan

### PSAK 69 - Aset Biologis
- 🌿 Manajemen Aset Biologis (Peternakan, Perkebunan, Perikanan, Kehutanan)
- 🌿 Penilaian Nilai Wajar (Fair Value Valuation)
- 🌿 Transformasi Biologis (Pertumbuhan, Kelahiran, Kematian)
- 🌿 Pencatatan Panen (Harvest)
- 🌿 Laporan Rekonsiliasi Aset Biologis
- 🌿 Laporan Pengungkapan PSAK 69

### Manufacturing
- 🏭 Bill of Materials (BOM) / Assembly
- 🏭 Produksi dengan perhitungan biaya
- 🏭 Laporan Biaya Produksi
- 🏭 Laporan Penggunaan Material
- 🏭 Work in Progress (WIP) Valuation

### Fitur Lainnya
- 👥 Manajemen User dengan Role-based Access Control
- 📋 Audit Trail / Log Aktivitas
- 💰 Anggaran (Budget) dengan perbandingan realisasi
- 📥 Import Data via Excel (Akun, Kontak, Persediaan, Aset Tetap)
- 📤 Export Data ke Excel

---

## 🔐 Role & Hak Akses

| Role | Deskripsi |
|------|-----------|
| **Administrator** | Akses penuh ke semua fitur |
| **Manajer** | Dapat approve, delete, dan edit data |
| **Operator** | Dapat create dan edit data transaksi |
| **Peninjau** | Hanya dapat melihat data (read-only) |

---

## 💻 Teknologi

- **Backend**: Laravel 12
- **Frontend**: Blade + Alpine.js + TailwindCSS
- **Database**: MySQL / SQLite
- **Build Tool**: Vite
- **PDF Export**: DomPDF
- **Excel**: Maatwebsite Excel

---

## 🚀 Instalasi

### Persyaratan
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+ atau SQLite

### Langkah Instalasi

```bash
# Clone repository
git clone https://github.com/solusigroup/simpleakunting-v4.git
cd simpleakunting-v4

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Konfigurasi database di .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=simpleakunting
# DB_USERNAME=root
# DB_PASSWORD=

# Jalankan migration
php artisan migrate

# Buat storage link
php artisan storage:link

# Build assets
npm run build

# Jalankan server development
php artisan serve
```

### Development Mode
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev
```

---

## 🌐 Deployment Production

```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Build assets
npm run build

# Jalankan migration
php artisan migrate --force

# Cache config & routes
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Konfigurasi Production (.env)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

---

## 📚 Dokumentasi

| Dokumen | Deskripsi |
|---------|-----------|
| [User Roles](docs/user-roles.md) | Panduan role dan hak akses |
| [PSAK 69](docs/psak69-biological-assets.md) | Dokumentasi modul Aset Biologis |
| [Production Readiness](docs/production-readiness-report.md) | Laporan kesiapan production |
| [Panduan Aplikasi](docs/panduan-aplikasi.md) | Panduan penggunaan |

---

## 📁 Struktur Direktori

```
simpleakunting-v4/
├── app/
│   ├── Http/Controllers/     # 27 Controllers
│   ├── Models/               # 20 Models
│   ├── Traits/               # Reusable traits
│   └── Helpers/              # Helper functions
├── database/
│   ├── migrations/           # 27 Migrations
│   ├── seeders/              # COA Seeders (UMKM/BUMDesa)
│   └── factories/            # Model factories
├── resources/
│   ├── views/                # Blade templates
│   └── css/                  # TailwindCSS
├── routes/
│   └── web.php               # 80+ Routes
├── tests/
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
└── docs/                     # Dokumentasi
```

---

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test dengan coverage
php artisan test --coverage
```

**Status Test**: ✅ 31 tests passed (79 assertions)

---

## 📄 Lisensi

Hak Cipta © 2025-2026 **SimpleAkunting by Solusi Consult**. All rights reserved.

---

## 🤝 Kontak

- **Website**: [simpleakunting.my.id](https://simpleakunting.my.id)
- **Email**: kurniawan@petalmail.com
