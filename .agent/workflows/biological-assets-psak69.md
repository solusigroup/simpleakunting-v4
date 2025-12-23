---
description: Implementation Plan for Biological Assets (PSAK 69)
---

# Implementation Plan: Biological Assets Module (PSAK 69)

## Overview
Implementasi modul Aset Biologis sesuai dengan PSAK 69 (Agrikultur) yang diterbitkan oleh IAI. Modul ini akan menangani pencatatan, pengukuran, dan pelaporan aset biologis (hewan/tanaman hidup) dan produk agrikultur.

**IMPORTANT**: Modul ini bersifat **OPT-IN** - hanya perusahaan yang mengaktifkan PSAK 69 yang akan melihat dan menggunakan fitur ini. Perusahaan non-agrikultur tidak akan terganggu dengan fitur yang tidak relevan.

## Configuration Options

### Company-Level Settings
Setiap perusahaan dapat mengkonfigurasi:

1. **Enable PSAK 69** (`enable_psak69` boolean)
   - `false` (default): Modul biologis tidak aktif, menu tidak muncul
   - `true`: Modul biologis aktif, semua fitur tersedia

2. **Business Sector** (`business_sector` enum)
   - `general` (default): Perusahaan umum/non-agrikultur
   - `livestock`: Peternakan
   - `plantation`: Perkebunan
   - `aquaculture`: Perikanan/Budidaya
   - `forestry`: Kehutanan
   - `mixed_agriculture`: Agrikultur Campuran

### UI/UX Impact
- Menu "Aset Biologis" hanya muncul jika `enable_psak69 = true`
- COA categories untuk biological assets hanya tersedia jika PSAK 69 enabled
- Reports PSAK 69 hanya muncul jika module aktif
- Setup wizard akan menanyakan apakah perusahaan bergerak di sektor agrikultur


## Key Features (PSAK 69 Compliance)

### 1. **Aset Biologis Management**
- Pencatatan aset biologis (ternak, tanaman, dll)
- Tracking transformasi biologis (pertumbuhan, degenerasi, produksi, prokreasi)
- Pengukuran nilai wajar dikurangi biaya untuk menjual
- Alternatif: Biaya perolehan dikurangi akumulasi penyusutan (jika nilai wajar tidak dapat diukur)
- Kategorisasi: Consumable vs Bearer, Mature vs Immature

### 2. **Produk Agrikultur**
- Pencatatan hasil panen
- Pengukuran pada nilai wajar saat panen
- Konversi ke inventory setelah panen

### 3. **Jurnal Otomatis**
- Perubahan nilai wajar → Laba/Rugi
- Panen produk agrikultur
- Penjualan produk agrikultur
- Kematian/kehilangan aset biologis

### 4. **Laporan Khusus PSAK 69**
- Rekonsiliasi nilai tercatat aset biologis
- Analisis perubahan nilai wajar
- Laporan produksi dan panen
- Disclosure sesuai PSAK 69

## Database Schema

### Table: `biological_assets`
```sql
- id (bigint, PK)
- company_id (bigint, FK)
- code (varchar 50, unique per company)
- name (varchar 255)
- category (enum: 'livestock', 'plantation', 'aquaculture', 'forestry', 'other')
- asset_type (enum: 'consumable', 'bearer') // Habis pakai vs Penghasil
- maturity_status (enum: 'immature', 'mature')
- quantity (decimal 10,2)
- unit (varchar 50) // ekor, pohon, kg, dll
- acquisition_date (date)
- acquisition_cost (decimal 15,2)
- current_fair_value (decimal 15,2) // Nilai wajar saat ini
- cost_to_sell (decimal 15,2) // Estimasi biaya untuk menjual
- carrying_amount (decimal 15,2) // Nilai tercatat (fair value - cost to sell)
- valuation_method (enum: 'fair_value', 'cost_model')
- valuation_date (date) // Tanggal penilaian terakhir
- location (varchar 255)
- notes (text)
- coa_id (bigint, FK) // Akun Aset Biologis
- fair_value_gain_loss_coa_id (bigint, FK) // Akun Keuntungan/Kerugian Nilai Wajar
- is_active (boolean)
- created_at, updated_at, deleted_at
```

### Table: `biological_transformations`
```sql
- id (bigint, PK)
- biological_asset_id (bigint, FK)
- transformation_type (enum: 'growth', 'degeneration', 'production', 'procreation', 'death', 'harvest')
- transaction_date (date)
- quantity_change (decimal 10,2) // + atau -
- description (text)
- journal_id (bigint, FK, nullable) // Link ke jurnal jika ada
- created_at, updated_at
```

### Table: `biological_valuations`
```sql
- id (bigint, PK)
- biological_asset_id (bigint, FK)
- valuation_date (date)
- previous_fair_value (decimal 15,2)
- current_fair_value (decimal 15,2)
- cost_to_sell (decimal 15,2)
- fair_value_change (decimal 15,2) // Perubahan nilai wajar
- valuation_method (varchar 100) // Market price, DCF, dll
- valuation_notes (text)
- journal_id (bigint, FK, nullable)
- created_by (bigint, FK)
- created_at, updated_at
```

### Table: `agricultural_produce`
```sql
- id (bigint, PK)
- company_id (bigint, FK)
- biological_asset_id (bigint, FK)
- harvest_date (date)
- product_name (varchar 255)
- quantity (decimal 10,2)
- unit (varchar 50)
- fair_value_at_harvest (decimal 15,2)
- cost_to_sell (decimal 15,2)
- carrying_amount (decimal 15,2)
- inventory_id (bigint, FK, nullable) // Link ke inventory setelah panen
- coa_id (bigint, FK) // Akun Inventory/Produk
- journal_id (bigint, FK, nullable)
- notes (text)
- created_at, updated_at
```

## Implementation Steps

### Phase 1: Database & Models (Day 1-2)

1. **Update Company Configuration** ✅ COMPLETED
   - Migration: `add_psak69_settings_to_companies_table`
   - Add `enable_psak69` (boolean, default false)
   - Add `business_sector` (enum: general, livestock, plantation, aquaculture, forestry, mixed_agriculture)
   - Update Company model with helper methods: `usesPsak69()`, `isAgricultureSector()`

2. **Create Migrations**
   - `create_biological_assets_table`
   - `create_biological_transformations_table`
   - `create_biological_valuations_table`
   - `create_agricultural_produce_table`
   - `add_biological_asset_categories_to_chart_of_accounts`

3. **Create Models**
   - `BiologicalAsset.php` with relationships and business logic
   - `BiologicalTransformation.php`
   - `BiologicalValuation.php`
   - `AgriculturalProduce.php`

4. **Update ChartOfAccount Model**
   - Add biological asset categories to account_category enum
   - Add scope methods for biological asset accounts


### Phase 2: Controllers & Business Logic (Day 3-4)

4. **Create BiologicalAssetController**
   - CRUD operations
   - Valuation updates
   - Transformation recording
   - Harvest processing

5. **Create BiologicalReportController**
   - Reconciliation report
   - Fair value changes report
   - Production/harvest report
   - PSAK 69 disclosure report

6. **Implement Automatic Journal Entries**
   - Fair value adjustment journals
   - Harvest journals
   - Death/loss journals

### Phase 3: Views & UI (Day 5-6)

7. **Create Blade Views**
   - `biological-assets/index.blade.php` (List)
   - `biological-assets/create.blade.php` (Form)
   - `biological-assets/edit.blade.php` (Form)
   - `biological-assets/show.blade.php` (Detail)
   - `biological-assets/valuation.blade.php` (Valuation form)
   - `biological-assets/transformation.blade.php` (Transformation form)
   - `biological-assets/harvest.blade.php` (Harvest form)

8. **Create Report Views**
   - `reports/biological-reconciliation.blade.php`
   - `reports/biological-fair-value-changes.blade.php`
   - `reports/biological-production.blade.php`

9. **Update Navigation**
   - Add "Aset Biologis" menu
   - Add to reports menu

### Phase 4: Testing & Documentation (Day 7)

10. **Create Seeders**
    - Sample biological assets
    - Sample COA for biological assets

11. **Testing**
    - Unit tests for models
    - Feature tests for controllers
    - Manual testing of complete flow

12. **Documentation**
    - User guide for biological assets
    - PSAK 69 compliance checklist
    - API documentation

## Technical Specifications

### Automatic Journal Entries

#### 1. Fair Value Adjustment
```
When: Valuation update
Dr. Aset Biologis (if increase) / Cr. Keuntungan Perubahan Nilai Wajar
Cr. Aset Biologis (if decrease) / Dr. Kerugian Perubahan Nilai Wajar
```

#### 2. Harvest
```
When: Agricultural produce harvested
Dr. Persediaan Produk Agrikultur (at fair value)
Cr. Aset Biologis (reduce carrying amount)
Cr/Dr. Keuntungan/Kerugian Panen (difference)
```

#### 3. Death/Loss
```
When: Biological asset dies or lost
Dr. Kerugian Aset Biologis
Cr. Aset Biologis
```

### Validation Rules

1. **Fair Value Method**
   - Fair value must be > 0
   - Cost to sell must be >= 0
   - Carrying amount = Fair value - Cost to sell

2. **Cost Model**
   - Only if fair value cannot be reliably measured
   - Must have acquisition cost
   - Can have accumulated depreciation

3. **Transformation**
   - Quantity cannot go negative
   - Death/harvest cannot exceed current quantity

4. **Harvest**
   - Only mature assets can be harvested (for bearer)
   - Fair value at harvest is required

## Routes Structure

```php
// Biological Assets
Route::prefix('biological-assets')->group(function () {
    Route::get('/', [BiologicalAssetController::class, 'index'])->name('biological-assets.index');
    Route::get('/create', [BiologicalAssetController::class, 'create'])->name('biological-assets.create');
    Route::post('/', [BiologicalAssetController::class, 'store'])->name('biological-assets.store');
    Route::get('/{id}', [BiologicalAssetController::class, 'show'])->name('biological-assets.show');
    Route::get('/{id}/edit', [BiologicalAssetController::class, 'edit'])->name('biological-assets.edit');
    Route::put('/{id}', [BiologicalAssetController::class, 'update'])->name('biological-assets.update');
    Route::delete('/{id}', [BiologicalAssetController::class, 'destroy'])->name('biological-assets.destroy');
    
    // Valuation
    Route::post('/{id}/valuate', [BiologicalAssetController::class, 'valuate'])->name('biological-assets.valuate');
    
    // Transformation
    Route::post('/{id}/transform', [BiologicalAssetController::class, 'transform'])->name('biological-assets.transform');
    
    // Harvest
    Route::post('/{id}/harvest', [BiologicalAssetController::class, 'harvest'])->name('biological-assets.harvest');
});

// Reports
Route::prefix('reports')->group(function () {
    Route::get('/biological-reconciliation', [BiologicalReportController::class, 'reconciliation']);
    Route::get('/biological-fair-value', [BiologicalReportController::class, 'fairValueChanges']);
    Route::get('/biological-production', [BiologicalReportController::class, 'production']);
});
```

## PSAK 69 Compliance Checklist

### Pengakuan
- [x] Aset biologis diakui jika entitas mengendalikan aset
- [x] Kemungkinan besar manfaat ekonomi masa depan akan mengalir
- [x] Nilai wajar atau biaya perolehan dapat diukur secara andal

### Pengukuran
- [x] Pengakuan awal: Nilai wajar dikurangi biaya untuk menjual
- [x] Setiap tanggal pelaporan: Nilai wajar dikurangi biaya untuk menjual
- [x] Alternatif: Biaya perolehan jika nilai wajar tidak dapat diukur andal
- [x] Produk agrikultur: Nilai wajar dikurangi biaya untuk menjual pada saat panen

### Keuntungan dan Kerugian
- [x] Perubahan nilai wajar diakui dalam laba rugi periode berjalan
- [x] Keuntungan/kerugian dari pengakuan awal
- [x] Keuntungan/kerugian dari perubahan nilai wajar

### Pengungkapan
- [x] Deskripsi kelompok aset biologis
- [x] Metode dan asumsi pengukuran nilai wajar
- [x] Rekonsiliasi perubahan nilai tercatat
- [x] Keberadaan pembatasan kepemilikan
- [x] Komitmen pengembangan aset biologis
- [x] Strategi manajemen risiko keuangan

## Next Steps

1. Review and approve this implementation plan
2. Start Phase 1: Database & Models
3. Create detailed task checklist for each phase
4. Begin implementation with migrations

---

**Estimated Timeline**: 7 working days
**Complexity**: High (8/10)
**PSAK 69 Compliance**: Full compliance with disclosure requirements
