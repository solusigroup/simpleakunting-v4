<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoaCustomSeeder extends Seeder
{
    /**
     * Seed Chart of Accounts based on user provided structure.
     */
    public function run(Company $company): void
    {
        // Define accounts array directly for reliability rather than parsing text at runtime
        // Format: [Code, Name, H/D (H=Header/D=Detail), Level, Normal Balance, Report Type, Type (inferred)]
        $rawAccounts = [
            ['1.0.01.00', 'ASET', 'H', 1, 'Debit', 'Neraca', 'Asset'],
            ['1.1.01.00', 'Aset Lancar', 'H', 2, 'Debit', 'Neraca', 'Asset'],
            ['1.1.01.00', 'Kas', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.1.01.01', 'Kas Tunai', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.01.02', 'Kas di Bank BSI', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.01.03', 'Kas di Bank Mandiri', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.01.04', 'Kas di Bank BRI', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.01.05', 'Kas di Bank BCA', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.01.98', 'Kas Kecil (Petty Cash)', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.02.00', 'Setara Kas', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.1.02.01', 'Deposito <= 3 bulan', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.02.99', 'Setara Kas Lainnya', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.03.00', 'Piutang', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.1.03.01', 'Piutang Usaha', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.03.02', 'Piutang kepada Pegawai', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.03.99', 'Piutang Lainnya', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.04.00', 'Penyisihan Piutang', 'H', 3, 'Kredit', 'Neraca', 'Asset'], // Contra-asset
            ['1.1.04.01', 'Penyisihan Piutang Usaha Tak Tertagih', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.1.04.02', 'Penyisihan Piutang kepada Pegawai Tak Tertagih', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.1.04.99', 'Penyisihan Piutang Lainnya Tak Tertagih', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.1.05.00', 'Persediaan', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.1.05.01', 'Persediaan Barang Dagangan', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.05.02', 'Persediaan Bahan Baku', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.05.03', 'Persediaan Barang Dalam Proses', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.05.04', 'Persediaan Barang Jadi', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.05.05', 'Persediaan Produk Agrikultur - Ayam Potong', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.05.06', 'Persediaan Produk Agrikultur - Buah Naga', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.05.07', 'Persediaan Produk Agrikultur - Ikan Mas', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.05.08', 'Persediaan Produk Agrikultur - Susu', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.06.00', 'Perlengkapan', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.1.06.01', 'Alat Tulis Kantor (ATK)', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.07.00', 'Pembayaran Dimuka', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.1.07.01', 'Sewa Dibayar Dimuka', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.07.02', 'Asuransi Dibayar Dimuka', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.07.03', 'PPh 25', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.07.04', 'PPN Masukan', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.08.00', 'Aset Biologis', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.1.08.01', 'Aset Biologis - Ayam', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.08.02', 'Aset Biologis - Ikan Mas', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.98.00', 'Aset Lancar Lainnya', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.1.98.99', 'Aset Lancar Lainnya', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.99.00', 'RK Unit Usaha', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.1.99.01', 'RK Unit Wisata', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.99.02', 'RK Unit Restoran', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.99.03', 'RK Unit Minimart Desa', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.99.04', 'RK Unit Gedung Serbaguna', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.99.05', 'RK Unit Simpan Pinjam', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.99.06', 'RK Unit Pengelolaan Air Bersih', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.99.07', 'RK Unit Pengelolaan Sampah', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.1.99.08', 'RK Unit Ketahanan Pangan', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.2.00.00', 'Investasi', 'H', 2, 'Debit', 'Neraca', 'Asset'],
            ['1.2.01.00', 'Investasi', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.2.01.01', 'Deposito > 3 bulan', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.2.01.99', 'Investasi Lainnya', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.3.00.00', 'Aset Tetap', 'H', 2, 'Debit', 'Neraca', 'Asset'],
            ['1.3.01.00', 'Tanah', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.3.01.01', 'Tanah', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.3.02.00', 'Kendaraan', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.3.02.01', 'Kendaraan', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.3.03.00', 'Peralatan dan Mesin', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.3.03.01', 'Peralatan dan Mesin', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.3.04.00', 'Meubelair', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.3.04.01', 'Meubelair', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.3.05.00', 'Gedung dan Bangunan', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.3.05.01', 'Gedung dan Bangunan', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.3.06.00', 'Konstruksi Dalam Pengerjaan', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.3.06.01', 'Konstruksi Dalam Pengerjaan', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.3.07.00', 'Akumulasi Penyusutan Aset Tetap', 'H', 3, 'Kredit', 'Neraca', 'Asset'], // Contra-asset
            ['1.3.07.01', 'Akumulasi Penyusutan Kendaraan', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.3.07.02', 'Akumulasi Penyusutan Peralatan dan Mesin', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.3.07.03', 'Akumulasi Penyusutan Meubelair', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.3.07.04', 'Akumulasi Penyusutan Gedung dan Bangunan', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.3.07.05', 'Akumulasi Penyusutan - Sapi Perah', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.3.07.06', 'Akumulasi Penyusutan - Tanaman Buah Naga', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.3.07.07', 'Akumulasi Penyusutan - Tanaman Kelapa Sawit', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.3.08.00', 'Aset Tetap Biologis', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.3.08.01', 'Aset Tetap - Sapi Perah', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.3.08.02', 'Aset Tetap - Tanaman Buah Naga', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.3.08.03', 'Aset Tetap - Tanaman Kelapa Sawit', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.3.99.00', 'Aset Tetap Lainnya', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.3.99.99', 'Aset Tetap Lainnya', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.4.00.00', 'Aset takberwujud', 'H', 2, 'Debit', 'Neraca', 'Asset'],
            ['1.4.01.00', 'Aset takberwujud', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.4.01.01', 'Software', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.4.01.02', 'Patent', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.4.01.03', 'Trademark', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.4.02.00', 'Amortisasi Aset takberwujud', 'H', 3, 'Kredit', 'Neraca', 'Asset'],
            ['1.4.02.01', 'Amortisasi Aset takberwujud', 'D', 4, 'Kredit', 'Neraca', 'Asset'],
            ['1.9.00.00', 'Aset Lain-lain', 'H', 2, 'Debit', 'Neraca', 'Asset'],
            ['1.90.1.00', 'Aset Lain-lain', 'H', 3, 'Debit', 'Neraca', 'Asset'],
            ['1.9.01.01', 'Aset Lain-lain', 'D', 4, 'Debit', 'Neraca', 'Asset'],
            ['1.9.02.00', 'Akumulasi Penyusutan Aset Lain-lain', 'H', 3, 'Kredit', 'Neraca', 'Asset'],
            ['1.9.01.02', 'Akumulasi Penyusutan Aset Lain-lain', 'D', 4, 'Kredit', 'Neraca', 'Asset'],

            // KEWAJIBAN
            ['2.0.00.00', 'KEWAJIBAN', 'H', 1, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.00.00', 'Kewajiban Jangka Pendek', 'H', 2, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.01.00', 'Utang Usaha', 'H', 3, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.01.01', 'Utang Usaha', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.02.00', 'Utang Pajak', 'H', 3, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.02.01', 'PPN Keluaran', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.02.02', 'PPh 21', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.02.03', 'PPh 23', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.02.04', 'PPh 29', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.03.00', 'Utang Gaji/Upah dan Tunjangan', 'H', 3, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.03.01', 'Utang Gaji dan Tunjangan', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.03.02', 'Utang Gaji/Upah Karyawan', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.04.00', 'Utang Utilitas', 'H', 3, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.04.01', 'Utang Listrik', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.04.02', 'Utang Telepon/Internet', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.04.93', 'Utang Utilitas Lainnya', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.05.00', 'Utang kepada Pihak Ketiga Jk. Pendek', 'H', 3, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.05.01', 'Utang kepada Pihak Ketiga Jk. Pendek', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.05.99', 'Utang kepada Pihak Ketiga Jk. Pendek Lainnya', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.99.00', 'Utang Jangka Pendek Lainnya', 'H', 3, 'Kredit', 'Neraca', 'Liability'],
            ['2.1.09.99', 'Utang Jangka Pendek Lainnya', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.2.00.00', 'Kewajiban Jangka Panjang', 'H', 2, 'Kredit', 'Neraca', 'Liability'],
            ['2.2.01.00', 'Utang Ke Bank', 'H', 3, 'Kredit', 'Neraca', 'Liability'],
            ['2.2.01.01', 'Utang Ke Bank', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.2.02.00', 'Utang kepada Pihak Ketiga Jk. Panjang', 'H', 3, 'Kredit', 'Neraca', 'Liability'],
            ['2.2.02.01', 'Utang kepada Pihak Ketiga Jk. Panjang', 'D', 4, 'Kredit', 'Neraca', 'Liability'],
            ['2.2.99.00', 'Utang Jangka Panjang Lainnya', 'H', 3, 'Kredit', 'Neraca', 'Liability'],
            ['2.2.99.99', 'Utang Jangka Panjang Lainnya', 'D', 4, 'Kredit', 'Neraca', 'Liability'],

            // EKUITAS
            ['3.0.00.00', 'EKUITAS', 'H', 1, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.00.00', 'Modal Pemilik', 'H', 2, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.01.00', 'Penyertaan Modal Desa', 'H', 3, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.01.01', 'Penyertaan Modal Desa', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.01.02', 'Penyertaan Modal Desa A', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.01.03', 'Penyertaan Modal Desa B', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.01.04', 'Penyertaan Modal Desa C', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.02.00', 'Penyertaan Modal Masyarakat', 'H', 3, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.02.01', 'Penyertaan Modal Masyarakat', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.02.02', 'Penyertaan Modal Masyarakat Desa A', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.02.03', 'Penyertaan Modal Masyarakat Desa B', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.1.02.04', 'Penyertaan Modal Masyarakat Desa C', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.2.00.00', 'Pengambilan oleh Pemilik', 'H', 2, 'Debit', 'Neraca', 'Equity'],
            ['3.2.01.00', 'Pengambilan oleh Desa', 'H', 3, 'Debit', 'Neraca', 'Equity'],
            ['3.2.01.01', 'Bagi Hasil Penyertaan Modal Desa', 'D', 4, 'Debit', 'Neraca', 'Equity'],
            ['3.2.01.02', 'Bagi Hasil Penyertaan Modal Desa A', 'D', 4, 'Debit', 'Neraca', 'Equity'],
            ['3.2.01.03', 'Bagi Hasil Penyertaan Modal Desa B', 'D', 4, 'Debit', 'Neraca', 'Equity'],
            ['3.2.01.04', 'Bagi Hasil Penyertaan Modal Desa C', 'D', 4, 'Debit', 'Neraca', 'Equity'],
            ['3.2.02.00', 'Pengambilan oleh Masyarakat', 'H', 3, 'Debit', 'Neraca', 'Equity'],
            ['3.2.02.01', 'Bagi Hasil Penyertaan Modal Masyarakat', 'D', 4, 'Debit', 'Neraca', 'Equity'],
            ['3.2.02.02', 'Bagi Hasil Penyertaan Modal Masyarakat Desa A', 'D', 4, 'Debit', 'Neraca', 'Equity'],
            ['3.2.02.03', 'Bagi Hasil Penyertaan Modal Masyarakat Desa B', 'D', 4, 'Debit', 'Neraca', 'Equity'],
            ['3.2.02.04', 'Bagi Hasil Penyertaan Modal Masyarakat Desa C', 'D', 4, 'Debit', 'Neraca', 'Equity'],
            ['3.3.00.00', 'Saldo Laba', 'H', 2, 'Kredit', 'Neraca', 'Equity'],
            ['3.3.01.00', 'Saldo Laba Tidak Dicadangkan', 'H', 3, 'Kredit', 'Neraca', 'Equity'],
            ['3.3.01.01', 'Saldo Laba Tidak Dicadangkan', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.3.02.00', 'Saldo Laba Dicadangkan', 'H', 3, 'Kredit', 'Neraca', 'Equity'],
            ['3.3.02.01', 'Saldo Laba Dicadangkan untuk Pembelian Aset Tetap', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.3.01.02', 'Saldo Laba Dicadangkan untuk Pembayaran Utang Jangka Panjang', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.4.00.00', 'Modal Donasi/Sumbangan', 'H', 2, 'Kredit', 'Neraca', 'Equity'],
            ['3.4.01.00', 'Modal Donasi/Sumbangan', 'H', 3, 'Kredit', 'Neraca', 'Equity'],
            ['3.4.01.01', 'Modal Donasi/Sumbangan', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.8.00.00', 'RK Pusat', 'H', 2, 'Kredit', 'Neraca', 'Equity'],
            ['3.8.01.00', 'RK Pusat', 'H', 3, 'Kredit', 'Neraca', 'Equity'],
            ['3.8.01.01', 'RK Pusat', 'D', 4, 'Kredit', 'Neraca', 'Equity'],
            ['3.9.00.00', 'Ikhtisar Laba Rugi', 'H', 2, 'Kredit', 'Neraca', 'Equity'],
            ['3.9.01.00', 'Ikhtisar Laba Rugi', 'H', 3, 'Kredit', 'Neraca', 'Equity'],
            ['3.9.01.01', 'Ikhtisar Laba Rugi', 'D', 4, 'Kredit', 'Neraca', 'Equity'],

            // PENDAPATAN
            ['4.0.00.00', 'PENDAPATAN USAHA', 'H', 1, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.00.00', 'Pendapatan Jasa', 'H', 2, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.01.00', 'Pendapatan Wisata', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.01.01', 'Pendapatan Tiket', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.01.02', 'Pendapatan Wahana', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.01.03', 'Pendapatan Paket Wisata', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.02.00', 'Pendapatan Pengelolaan Air Bersih', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.02.01', 'Pendapatan Pengelolaan Air Bersih', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.03.00', 'Pendapatan Pengelolaan Sampah', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.03.01', 'Pendapatan Pengelolaan Sampah', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.04.00', 'Pendapatan Sewa', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.04.01', 'Pendapatan Sewa Tempat Outbound', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.04.02', 'Pendapatan Sewa Tempat untuk Toko/Kios', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.04.03', 'Pendapatan Sewa Gedung', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.04.04', 'Pendapatan Sewa Mobil', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.04.05', 'Pendapatan Sewa Peralatan Gedung', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.04.99', 'Pendapatan Sewa Lainnya', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.05.00', 'Pendapatan Jasa Pelayanan', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.05.01', 'Pendapatan Jasa Pembayaran Listrik', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.05.99', 'Pendapatan Jasa Pelayanan lainnya', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.06.00', 'Pendapatan Transportasi', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.06.01', 'Pendapatan Transportasi', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.07.00', 'Pendapatan Parkir', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.07.01', 'Pendapatan Parkir Mobil', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.07.02', 'Pendapatan Parkir Motor', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.08.00', 'Pendapatan Simpan Pinjam', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.08.01', 'Pendapatan Simpan Pinjam', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.09.00', 'Pendapatan Pelatihan', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.09.01', 'Pendapatan Pelatihan', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.10.00', 'Pendapatan Penginapan/Homestay', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.10.01', 'Pendapatan Homestay', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.11.00', 'Pendapatan Komisi', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.1.11.01', 'Pendapata Komisi', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.00.00', 'Pendapatan Penjualan Barang Dagangan', 'H', 2, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.01.00', 'Pendapatan Penjualan Barang Dagangan', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.01.01', 'Pendapatan Penjualan Makanan/Minuman', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.01.02', 'Pendapatan Penjualan Pakaian/Kaos/Jaket', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.01.03', 'Pendapatan Penjualan Hasil Kerajinan/Suvenir', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.01.04', 'Pendapatan Penjualan Buku', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.01.05', 'Pendapatan Penjualan Biji Kopi', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.01.06', 'Pendapatan Penjualan Bensin', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.02.00', 'Pendapatan Penjualan Produk Ketahanan Pangan', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.02.01', 'Pendapatan Penjualan Susu', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.02.02', 'Pendapatan Penjualan Buah Kelapa Sawit', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.02.03', 'Pendapatan Penjualan Ayam', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.02.04', 'Pendapatan Penjualan Ikan Mas', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.2.98.00', 'Diskon Penjualan Barang Dagangan', 'H', 3, 'Debit', 'LABARUGI', 'Revenue'], // Contra-revenue
            ['4.2.98.01', 'Diskon Penjualan Barang Dagangan', 'D', 4, 'Debit', 'LABARUGI', 'Revenue'],
            ['4.2.99.00', 'Retur Penjualan Barang Dagangan', 'H', 3, 'Debit', 'LABARUGI', 'Revenue'], // Contra-revenue
            ['4.2.99.01', 'Retur Penjualan Barang Dagangan', 'D', 4, 'Debit', 'LABARUGI', 'Revenue'],
            ['4.3.00.00', 'Pendapatan Penjualan Barang Jadi', 'H', 2, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.3.01.00', 'Pendapatan Penjualan Barang Jadi', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.3.01.01', 'Pendapatan Katering', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.3.01.02', 'Pendapatan Restoran', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.3.01.03', 'Pendapatan Kopi', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['4.3.98.00', 'Diskon Penjualan Barang Jadi', 'H', 3, 'Debit', 'LABARUGI', 'Revenue'], // Contra-revenue
            ['4.3.98.01', 'Diskon Penjualan Barang Jadi', 'D', 4, 'Debit', 'LABARUGI', 'Revenue'],
            ['4.3.99.00', 'Retur Penjualan Barang Jadi', 'H', 3, 'Debit', 'LABARUGI', 'Revenue'], // Contra-revenue
            ['4.3.99.01', 'Retur Penjualan Barang Jadi', 'D', 4, 'Debit', 'LABARUGI', 'Revenue'],

            // HARGA POKOK
            ['5.0.00.00', 'HARGA POKOK PRODUKSI DAN PENJUALAN', 'H', 1, 'Debit', 'LABARUGI', 'Expense'],
            ['5.1.00.00', 'Harga Pokok Penjualan Barang Dagangan', 'H', 2, 'Debit', 'LABARUGI', 'Expense'],
            ['5.1.01.00', 'Harga Pokok Penjualan Barang Dagangan', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['5.1.01.01', 'Harga Pokok Penjualan Barang Dagangan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['5.2.00.00', 'Harga Pokok Penjualan Barang Jadi', 'H', 2, 'Debit', 'LABARUGI', 'Expense'],
            ['5.2.01.00', 'Harga Pokok Penjualan Barang Jadi', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['5.2.01.01', 'Harga Pokok Penjualan Barang Jadi', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['5.3.00.00', 'Harga Pokok Produksi', 'H', 2, 'Debit', 'LABARUGI', 'Expense'],
            ['5.3.01.00', 'Harga Pokok Produksi', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['5.3.01.01', 'Harga Pokok Produksi', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],

            // BEBAN USAHA
            ['6.0.00.00', 'BEBAN-BEBAN USAHA', 'H', 1, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.00.00', 'Beban Administrasi dan Umum', 'H', 2, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.01.00', 'Beban Pegawai Bagian Administrasi Umum', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.01.01', 'Beban Gaji dan Tunjangan Bag. Adum', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.01.02', 'Beban Honor Lembur Bag. Adum', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.01.03', 'Beban Honor Narasumber', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.01.04', 'Beban Insentif (Bonus) Bag. Adum', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.01.05', 'Beban Komisi Bag. Adum', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.01.06', 'Beban Seragam Pegawai Bag. Adum', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.01.07', 'Beban Penguatan SDM', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.01.99', 'Beban Pegawai Bag. Adum Lainnya', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.02.00', 'Beban Perlengkapan', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.02.01', 'Beban Alat Tulis Kantor (ATK)', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.02.02', 'Beban Foto Copy', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.02.03', 'Beban Konsumsi Rapat', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.02.04', 'Beban Cetak dan Dekorasi', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.02.99', 'Beban Perlengkapan Lainnya', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.03.00', 'Beban Pemeliharaan dan Perbaikan', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.03.01', 'Beban Pemeliharaan dan Perbaikan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.04.00', 'Beban Utilitas', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.04.01', 'Beban Listrik', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.04.02', 'Beban Telepon/Internet', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.04.99', 'Beban Utilitas Lainnya', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.05.00', 'Beban Sewa dan Asuransi', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.05.01', 'Beban Sewa', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.05.02', 'Beban Asuransi', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.06.00', 'Beban Kebersihan dan Keamanan', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.06.01', 'Beban Kebersihan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.06.02', 'Beban Keamanan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.07.00', 'Beban Penyisihan dan Penyusutan/Amortisasi', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.07.01', 'Beban Penyisihan Piutang Tak Tertagih', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.07.02', 'Beban Penyusutan Kendaraan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.07.03', 'Beban Penyusutan Peralatan dan Mesin', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.07.04', 'Beban Penyusutan Meubelair', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.07.05', 'Beban Penyusutan Gedung dan Bangunan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.07.06', 'Beban Amortisasi Aset takberwujud', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.07.07', 'Beban Penyusutan - Sapi Perah', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.07.08', 'Beban Penyusutan - Tanaman Buah Naga', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.07.09', 'Beban Penyusutan - Tanaman Kelapa Sawit', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.99.00', 'Beban Administrasi dan Umum Lainnya', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.99.01', 'Beban Parkir', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.99.02', 'Beban Audit', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.99.03', 'Beban Perjalanan Dinas', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.99.04', 'Beban Transportasi', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.99.05', 'Beban Jamuan Tamu', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.1.99.99', 'Beban Administrasi dan Umum Lainnya', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.00.00', 'Beban Operasional', 'H', 2, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.01.00', 'Beban Pegawa Bagian Operasional', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.01.01', 'Beban Gaji/Upah Bag. Operasional', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.01.02', 'Beban Uang Makan Bag. Operasional', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.02.00', 'Beban Pemeliharaan dan Perbaikan', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.02.01', 'Beban Pemeliharaan Wahana', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.02.02', 'Beban Perbaikan dan Renovasi', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.03.00', 'Beban Keamanan', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.03.01', 'Beban Tim SAR', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.03.02', 'Beban P3K', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.99.00', 'Beban Operasional Lainnya', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.99.01', 'Beban Komunikasi', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.99.02', 'Beban Sewa Lokasi', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.99.03', 'Beban Pakan Ikan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.2.99.99', 'Beban Operasional Lainnya', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.00.00', 'Beban Pemasaran', 'H', 2, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.01.00', 'Beban Pegawai Bagian Pemasaran', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.01.01', 'Beban Gaji/Upah Bag. Pemasaran', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.01.02', 'Beban Insentif (Bonus) Bag. Pemasaran', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.01.03', 'Beban Seragam Pegawai Bag. Pemasaran', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.02.00', 'Beban Iklan dan Promosi', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.02.01', 'Beban Iklan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.02.02', 'Beban Promosi wartawan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.02.03', 'Beban Dana Sosial', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.99.00', 'Beban Pemasaran Lainnya', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['6.3.99.99', 'Beban Pemasaran Lainnya', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],

            // PENDAPATAN/BEBAN LAIN-LAIN
            ['7.0.00.00', 'PENDAPATAN DAN BEBAN LAIN-LAIN', 'H', 1, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.00.00', 'Pendapatan Lain-lain', 'H', 2, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.01.00', 'Pendapatan dari Bank', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.01.01', 'Pendapatan Bunga Bank', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.01.02', 'Pendapatan Fee Agen BNI 46', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.02.00', 'Pendapatan Dividen', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.02.01', 'Pendapatan Dividen', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.03.00', 'Pendapatan Denda', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.03.01', 'Pendapatan Denda', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.04.00', 'Pendapatan Iklan', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.04.01', 'Pendapatan Iklan', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.05.00', 'Pendapatan Penjualan Aset Tetap', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.05.01', 'Keuntungan Penjualan Aset Tetap', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.06.00', 'Keuntungan Perubahan Nilai Wajar Aset Biologis', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.06.01', 'Keuntungan dari Perubahan Nilai Wajar Aset Biologis', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.99.00', 'Pendapatan Lain-lain lainnya', 'H', 3, 'Kredit', 'LABARUGI', 'Revenue'],
            ['7.1.99.99', 'Pendapatan Lain-lain lainnya', 'D', 4, 'Kredit', 'LABARUGI', 'Revenue'],

            ['7.2.00.00', 'Beban Lain-lain', 'H', 2, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.01.00', 'Beban Bank', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.01.01', 'Beban Administrasi Bank', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.02.00', 'Beban Bunga', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.02.01', 'Beban Bunga', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.03.00', 'Beban Denda', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.03.01', 'Beban Denda', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.04.00', 'Beban Penjualan Aset Tetap', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.04.01', 'Kerugian Penjualan Aset Tetap', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.05.00', 'Kerugian Perubahan Nilai Wajar Aset Biologis', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.05.01', 'Kerugian dari Perubahan Nilai Wajar Aset Biologis', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.99.00', 'Beban Lain-lain Lainnya', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['7.2.99.99', 'Beban Lain-lain lainnya', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],

            ['7.3.00.00', 'Beban Pajak', 'H', 2, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.00', 'Beban Pajak', 'H', 3, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.01', 'Beban Pajak Air Permukaan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.02', 'Beban Pajak Bunga Bank', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.03', 'Beban Pajak Daerah', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.04', 'Beban Pajak Hiburan', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.05', 'Beban Pajak Reklame', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.06', 'Beban PPh 21', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.07', 'Beban PPh 23', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.08', 'Beban PPh 25', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.09', 'Beban PPh 29', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.10', 'Beban PPh Final', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
            ['7.3.01.99', 'Beban Pajak Lainnya', 'D', 4, 'Debit', 'LABARUGI', 'Expense'],
        ];

        DB::beginTransaction();
        try {
            $codeToId = [];
            
            foreach ($rawAccounts as $acc) {
                // Ensure UPPERCASE for ENUM compatibility
                $normalBalance = strtoupper($acc[4]); // Debit -> DEBIT
                if ($normalBalance === 'K') $normalBalance = 'KREDIT'; // Sometimes user writes K even if text says Kredit
                if ($normalBalance === 'D') $normalBalance = 'DEBIT';

                $reportType = strtoupper($acc[5]); // Neraca -> NERACA

                $parentId = null;
                // Find parent: assume hierarchical format X.X.XX.XX
                // Parent should be the substring before the last part
                // e.g., 1.1.01.99 -> parent 1.1.01.00 ? No, typical structure is level based.
                // Let's use the explicit Level column 3.
                // But simplified: parent is 'previous account with level - 1'
                
                // Better approach for dot notation X.X.X.X
                // Try removing the last segment. 
                // 1.1.01.01 -> 1.1.01.00 (based on the sample)
                
                // Let's stick to simple parent finding: code - last digits
                // But the user's COA has weird parent codes like 1.1.01.00 is parent of 1.1.01.01
                
                // Custom logic for this specific COA format:
                // Level 1: 1.0.01.00
                // Level 2: 1.1.01.00
                // Level 3: 1.1.01.00 (Wait, duplicate code for level 2 and 3?)
                // Ah, the list shows 1.1.01.00 twice! One as Level 2 (Aset Lancar), one as Level 3 (Kas).
                // This is problematic for unique code constraint.
                // We MUST modify codes to be unique.
                
                // Let's use a simpler heuristic for parent finding used in previous seeders:
                // Just relying on the order is risky.
                
                // Let's create account
                // Handle duplicate codes: if code exists, append _1, _2 etc?? No, DB constraint.
                
                // Note on duplicate 1.1.01.00:
                // Level 2: 1.1.01.00 Aset Lancar
                // Level 3: 1.1.01.00 Kas
                // This is definitely an error in the source COA or my reading.
                // Usually Level 2 is 1.1.00.00
                
                // Let's AUTO-CORRECT codes to standard convention if possible, OR just skip duplicates?
                // skipping duplicates might lose hierarchy.
                
                // Let's assume the provided codes are strict. If there's duplicate, it will fail.
                // I will try to auto-fix the "Header" codes which often end in 00.
                // 1.1.01.00 Aset Lancar -> Maybe 1.1.00.00?
                
                $code = $acc[0];
                
                // Basic parent lookup:
                // If level > 1, find the most recent account with level == current_level - 1
                $parent = null;
                // ... actually finding parent by code string manipulation is safer for seeders
                
                // Let's assume the list is SORTED.
                // We can keep track of "current parent at level X"
                
                $level = $acc[3];
                // Store this account as the "latest account at this level"
                $parentsByLevel[$level] = $code;
                
                $parentId = null;
                if ($level > 1) {
                    // Parent is the latest account at level-1
                    // But we actually need the ID, which we may not have if we create strictly by code.
                    // Wait, we need to insert to get ID.
                    
                    // Actually, let's fix the Code collision first.
                    // If 1.1.01.00 exists, we can't insert again.
                    // Implementation: Check exist, if exists and name is different, that's a problem.
                    
                    $existing = ChartOfAccount::where('company_id', $company->id)->where('code', $code)->first();
                    if ($existing) {
                        // If it's the exact same account, skip
                        if ($existing->name === $acc[1]) {
                             $codeToId[$code] = $existing->id;
                             continue;
                        }
                        // If different name, we have a collision.
                        // Append 'H' for header?
                        // 1.1.01.00 Aset Lancar (H) VS 1.1.01.00 Kas (H) is weird. 
                        // Let's suffix the code.
                        $code = $code . '.' . $level; 
                    }
                }
                
                // Now resolve parent ID
                if ($level > 1) {
                    // Try to construct parent code using dot notation logic first? 
                    // No, the provided structure is 1.1.01.01 -> parent 1.1.01.00
                    // So we can try to find 1.1.01.00 in our map.
                    
                    // Logic: parent is usually the code with last non-zero digit replaced by 0? 
                    // 1.1.01.01 -> 1.1.01.00
                    // 1.1.01.00 -> 1.1.00.00 ??
                    
                    // Let's use the explicit level-based stack approach which matches the visual tree structure
                    // The parent of a Level N item is the last seen Level N-1 item.
                    $parentCode = $parentsByLevel[$level - 1] ?? null;
                    if ($parentCode) {
                        $parentId = $codeToId[$parentCode] ?? null;
                    }
                }

                $account = ChartOfAccount::create([
                    'company_id' => $company->id,
                    'code' => $code,
                    'name' => $acc[1],
                    'type' => $acc[6],
                    'report_type' => $reportType,
                    'normal_balance' => $normalBalance,
                    'level' => $level,
                    'is_parent' => ($acc[2] === 'H'),
                    'parent_id' => $parentId,
                    'is_active' => true,
                    'is_system' => true,
                ]);
                
                $codeToId[$code] = $account->id;
                // Update latest parent for this level
                $parentsByLevel[$level] = $code;
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
