# PSAK 69 - Aset Biologis (Biological Assets)

Dokumentasi lengkap modul Aset Biologis sesuai PSAK 69 (Agrikultur) pada aplikasi Simple Akunting V4.

## 📋 Tentang PSAK 69

**PSAK 69: Agrikultur** adalah standar akuntansi yang mengatur pengakuan, pengukuran, dan pengungkapan terkait aktivitas agrikultur. Standar ini diadopsi dari IAS 41 Agriculture.

### Ruang Lingkup
PSAK 69 diterapkan untuk:
- **Aset Biologis** - Hewan atau tanaman hidup
- **Produk Agrikultur** - Hasil panen dari aset biologis
- **Hibah Pemerintah** - Terkait aset biologis

### Prinsip Utama
1. Aset biologis diukur pada **nilai wajar dikurangi biaya untuk menjual** (Fair Value Less Costs to Sell)
2. Perubahan nilai wajar diakui dalam **laba rugi**
3. Produk agrikultur diukur pada nilai wajar dikurangi biaya untuk menjual **pada titik panen**

---

## 🌿 Kategori Aset Biologis

### Berdasarkan Jenis Usaha

| Kategori | Kode | Contoh |
|----------|------|--------|
| **Peternakan (Livestock)** | `livestock` | Sapi, kambing, ayam, bebek, babi |
| **Perkebunan (Plantation)** | `plantation` | Kelapa sawit, karet, kopi, kakao, tebu |
| **Perikanan (Aquaculture)** | `aquaculture` | Ikan lele, nila, udang, rumput laut |
| **Kehutanan (Forestry)** | `forestry` | Jati, sengon, akasia, mahoni |

### Berdasarkan Tipe Aset

| Tipe | Deskripsi | Contoh |
|------|-----------|--------|
| **Consumable (Habis Pakai)** | Dipanen sebagai produk utama atau dijual sebagai aset biologis | Ayam pedaging, ikan, sayuran |
| **Bearer (Penghasil)** | Menghasilkan produk agrikultur berulang kali | Sapi perah, pohon buah, indukan |

### Berdasarkan Kematangan

| Status | Deskripsi |
|--------|-----------|
| **Immature (Belum Dewasa)** | Belum mencapai usia produktif |
| **Mature (Dewasa/Produktif)** | Sudah mencapai usia produktif, siap panen/berbuah |

---

## 📊 Fitur Modul

### 1. Manajemen Aset Biologis (CRUD)

#### Registrasi Aset Biologis
- Kode dan nama aset
- Kategori (Peternakan/Perkebunan/Perikanan/Kehutanan)
- Tipe aset (Habis Pakai/Penghasil)
- Status kematangan (Belum Dewasa/Dewasa)
- Kuantitas dan satuan
- Tanggal perolehan
- Biaya perolehan
- Nilai wajar saat ini
- Biaya untuk menjual
- Akun buku besar terkait

#### Nilai Tercatat (Carrying Amount)
```
Nilai Tercatat = Nilai Wajar - Biaya untuk Menjual
```

### 2. Penilaian Nilai Wajar (Fair Value Valuation)

Fitur untuk mencatat perubahan nilai wajar aset biologis:

| Field | Deskripsi |
|-------|-----------|
| Tanggal Penilaian | Tanggal dilakukan penilaian |
| Nilai Wajar Lama | Nilai wajar sebelumnya |
| Nilai Wajar Baru | Nilai wajar hasil penilaian |
| Biaya untuk Menjual | Estimasi biaya penjualan |
| Metode Penilaian | Pasar/Penilai/Estimasi Internal |
| Catatan | Keterangan tambahan |

**Jurnal Otomatis:**
```
Jika nilai wajar NAIK:
  Dr. Aset Biologis          xxx
      Cr. Keuntungan Perubahan Nilai Wajar xxx

Jika nilai wajar TURUN:
  Dr. Kerugian Perubahan Nilai Wajar xxx
      Cr. Aset Biologis      xxx
```

### 3. Transformasi Biologis

Pencatatan perubahan biologis:

| Tipe Transformasi | Deskripsi |
|-------------------|-----------|
| **Growth (Pertumbuhan)** | Penambahan kuantitas/berat |
| **Birth (Kelahiran)** | Penambahan populasi dari kelahiran |
| **Degrowth (Penyusutan)** | Pengurangan karena pertumbuhan negatif |
| **Death (Kematian)** | Pengurangan karena kematian |
| **Harvest (Panen)** | Pengurangan karena panen |

### 4. Panen (Harvest)

Pencatatan hasil panen dari aset biologis:

| Field | Deskripsi |
|-------|-----------|
| Nama Produk | Nama produk hasil panen |
| Kuantitas | Jumlah hasil panen |
| Satuan | Satuan produk (kg, liter, dll) |
| Nilai Wajar per Unit | Harga pasar per unit |
| Akun Produk Agrikultur | Akun persediaan hasil panen |

**Jurnal Otomatis:**
```
Dr. Produk Agrikultur (Persediaan) xxx
    Cr. Aset Biologis              xxx
```

---

## 📈 Laporan PSAK 69

### 1. Rekonsiliasi Aset Biologis

Laporan yang menunjukkan pergerakan nilai tercatat aset biologis:

```
Saldo Awal
  (+) Penambahan (pembelian, kelahiran, transfer masuk)
  (+) Keuntungan perubahan nilai wajar
  (-) Kerugian perubahan nilai wajar
  (-) Pengurangan (penjualan, kematian, panen)
  (-) Transfer ke produk agrikultur
= Saldo Akhir
```

Sesuai dengan **PSAK 69 Paragraf 50**.

### 2. Laporan Perubahan Nilai Wajar

Detail perubahan nilai wajar per periode:
- Daftar penilaian yang dilakukan
- Perubahan nilai per aset
- Total keuntungan/kerugian nilai wajar

### 3. Laporan Produksi & Panen

Ringkasan aktivitas produksi:
- Volume panen per aset
- Nilai panen
- Perbandingan antar periode

### 4. Laporan Pengungkapan PSAK 69

Pengungkapan sesuai standar:
- Deskripsi aset biologis per kategori
- Metode penilaian yang digunakan
- Asumsi signifikan
- Rekonsiliasi nilai tercatat

---

## 🔗 Integrasi dengan Laporan Keuangan

### Neraca (Balance Sheet)

Aset biologis ditampilkan di bagian **Aset**:

```
ASET
├── Aset Lancar
│   ├── ...
│   └── Produk Agrikultur (hasil panen)
├── Aset Biologis
│   ├── Aset Biologis - Dewasa
│   └── Aset Biologis - Belum Dewasa
└── Aset Tetap
    └── ...
```

### Laba Rugi (Income Statement)

Keuntungan/kerugian nilai wajar ditampilkan di:

```
PENDAPATAN LAIN-LAIN
├── Keuntungan Perubahan Nilai Wajar Aset Biologis
└── ...

BEBAN LAIN-LAIN
├── Kerugian Perubahan Nilai Wajar Aset Biologis
└── ...
```

### Arus Kas (Cash Flow)

Transaksi kas terkait aset biologis masuk ke:
- **Aktivitas Operasi**: Penerimaan dari penjualan hasil panen
- **Aktivitas Investasi**: Pembelian/penjualan aset biologis

### Laporan Perubahan Ekuitas

Laba/rugi dari perubahan nilai wajar mempengaruhi **Laba Ditahan**.

---

## 🛠️ Panduan Teknis

### Kategori Akun (Chart of Accounts)

| Kategori | Tipe | Laporan |
|----------|------|---------|
| `biological_asset` | Asset | NERACA |
| `biological_asset_immature` | Asset | NERACA |
| `biological_asset_mature` | Asset | NERACA |
| `agricultural_produce` | Asset | NERACA |
| `fair_value_gain_loss` | Revenue/Expense | LABARUGI |

### Model Database

#### Tabel `biological_assets`
```sql
- id
- company_id
- code
- name
- category ('livestock', 'plantation', 'aquaculture', 'forestry')
- asset_type ('consumable', 'bearer')
- maturity_status ('mature', 'immature')
- quantity
- unit
- acquisition_date
- acquisition_cost
- current_fair_value
- cost_to_sell
- carrying_amount
- valuation_method ('fair_value', 'cost_model')
- valuation_date
- location
- notes
- coa_id
- fair_value_gain_loss_coa_id
- is_active
- timestamps
- soft_deletes
```

#### Tabel `biological_transformations`
```sql
- id
- biological_asset_id
- transformation_date
- transformation_type ('growth', 'birth', 'degrowth', 'death', 'harvest')
- quantity_change
- value_change
- description
- timestamps
```

#### Tabel `biological_valuations`
```sql
- id
- biological_asset_id
- valuation_date
- previous_fair_value
- new_fair_value
- cost_to_sell
- fair_value_change
- valuation_method
- appraiser
- notes
- journal_id
- timestamps
```

#### Tabel `agricultural_produce`
```sql
- id
- biological_asset_id
- company_id
- product_name
- harvest_date
- quantity
- unit
- fair_value_per_unit
- carrying_amount
- coa_id
- journal_id
- notes
- timestamps
```

### API Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/biological-assets` | Daftar aset biologis |
| POST | `/biological-assets` | Tambah aset biologis |
| GET | `/biological-assets/{id}` | Detail aset |
| PUT | `/biological-assets/{id}` | Update aset |
| DELETE | `/biological-assets/{id}` | Hapus aset |
| POST | `/biological-assets/{id}/valuate` | Penilaian nilai wajar |
| POST | `/biological-assets/{id}/transform` | Catat transformasi |
| POST | `/biological-assets/{id}/harvest` | Catat panen |

### Report Endpoints

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/reports/biological-reconciliation` | Rekonsiliasi aset |
| GET | `/reports/biological-fair-value` | Perubahan nilai wajar |
| GET | `/reports/biological-production` | Laporan produksi |
| GET | `/reports/biological-disclosure` | Pengungkapan PSAK 69 |

---

## ⚙️ Cara Mengaktifkan

### 1. Via Setup Wizard
Saat pertama kali setup, pilih sektor bisnis **Agrikultur/Pertanian**. Modul PSAK 69 akan otomatis aktif.

### 2. Via Pengaturan Perusahaan
1. Buka menu **Pengaturan Perusahaan**
2. Scroll ke bagian **Fitur Aset Biologis (PSAK 69)**
3. Aktifkan toggle **Aktifkan PSAK 69**
4. Klik **Simpan**

---

## 📌 Catatan Penting

### Pengukuran
1. **Prioritas Nilai Wajar**: Gunakan harga pasar aktif jika tersedia
2. **Alternatif**: Jika tidak ada pasar aktif, gunakan:
   - Harga transaksi terkini
   - Harga pasar untuk aset serupa
   - Benchmark industri
3. **Cost Model**: Hanya digunakan jika nilai wajar tidak dapat diukur secara andal

### Audit Trail
Semua transaksi aset biologis tercatat dalam audit log:
- Penambahan aset
- Perubahan nilai wajar
- Transformasi biologis
- Panen

### Periode Pelaporan
- Penilaian nilai wajar minimal dilakukan **setiap akhir periode pelaporan**
- Transformasi biologis dicatat **saat terjadi**
- Panen dicatat **pada titik panen**

---

## 📚 Referensi

- [PSAK 69: Agrikultur](https://www.iaiglobal.or.id) - Ikatan Akuntan Indonesia
- [IAS 41: Agriculture](https://www.ifrs.org) - IFRS Foundation
- [Panduan Penerapan PSAK 69](https://www.iaiglobal.or.id) - IAI

---

*Dokumentasi ini dibuat untuk Simple Akunting V4 - Modul PSAK 69 Aset Biologis*
