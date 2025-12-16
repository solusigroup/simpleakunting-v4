<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-white font-display">Panduan Pengguna SimpleAkunting</h2>
    </x-slot>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="w-full lg:w-64 flex-shrink-0">
            <div class="sticky top-24 bg-surface-dark border border-border-dark rounded-xl p-4">
                <h3 class="font-bold text-primary mb-4 uppercase text-xs tracking-wider">Daftar Isi</h3>
                <nav class="space-y-1">
                    <a href="#pendahuluan" class="block px-3 py-2 text-sm text-white hover:bg-surface-highlight rounded-lg transition">Pendahuluan</a>
                    <a href="#memulai" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Memulai Aplikasi</a>
                    <a href="#dashboard" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Dashboard</a>
                    <a href="#master-data" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Master Data</a>
                    <a href="#transaksi" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Transaksi</a>
                    <a href="#laporan" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Laporan Keuangan</a>
                    <a href="#pengaturan" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Pengaturan</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 space-y-12 pb-20">
            
            <!-- Pendahuluan -->
            <section id="pendahuluan" class="scroll-mt-24">
                <div class="bg-surface-dark border border-border-dark rounded-2xl p-8">
                    <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-3">
                        <span class="p-2 bg-primary/20 rounded-lg text-primary material-symbols-outlined">menu_book</span>
                        Pendahuluan
                    </h2>
                    <p class="text-text-muted leading-relaxed mb-4">
                        Selamat datang di <strong>SimpleAkunting V4</strong>. Aplikasi ini dirancang untuk memudahkan pencatatan dan pelaporan keuangan Anda, baik untuk UMKM, BUMDesa, maupun perusahaan jasa dan dagang lainnya.
                    </p>
                    <p class="text-text-muted leading-relaxed">
                        Panduan ini akan membantu Anda memahami cara menggunakan fitur-fitur yang tersedia, mulai dari pengaturan awal hingga menghasilkan laporan keuangan yang akurat.
                    </p>
                </div>
            </section>

            <!-- Memulai -->
            <section id="memulai" class="scroll-mt-24">
                <h2 class="text-xl font-bold text-white mb-6 border-b border-border-dark pb-2">Memulai Aplikasi</h2>
                
                <div class="space-y-6">
                    <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                        <h3 class="text-lg font-bold text-white mb-3">Login & Akses</h3>
                        <p class="text-text-muted mb-4">
                            Untuk mengakses aplikasi, masukkan email dan password yang telah didaftarkan.
                            Jika Anda pengguna baru, Administrator perusahaan Anda akan memberikan kredensial akses atau Anda dapat mendaftar perusahaan baru (jika diaktifkan).
                        </p>
                        <ul class="list-disc list-inside text-text-muted space-y-1 ml-2">
                            <li>Pastikan email valid.</li>
                            <li>Password bersifat case-sensitive (huruf besar/kecil berpengaruh).</li>
                        </ul>
                    </div>

                    <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                        <h3 class="text-lg font-bold text-white mb-3">Setup Awal (Wizard)</h3>
                        <p class="text-text-muted">
                            Saat pertama kali membuat perusahaan, Anda akan diarahkan ke <strong>Setup Wizard</strong> untuk:
                        </p>
                        <ol class="list-decimal list-inside text-text-muted space-y-2 mt-3 ml-2">
                            <li>Mengisi profil perusahaan (Nama, Alamat, Jenis Usaha).</li>
                            <li>Memilih template Bagan Akun (COA) yang sesuai (Jasa/Dagang/Manufaktur).</li>
                            <li>Menentukan periode akuntansi awal.</li>
                        </ol>
                    </div>
                </div>
            </section>

            <!-- Dashboard -->
            <section id="dashboard" class="scroll-mt-24">
                <h2 class="text-xl font-bold text-white mb-6 border-b border-border-dark pb-2">Dashboard</h2>
                <div class="bg-surface-dark border border-border-dark rounded-2xl p-6">
                    <p class="text-text-muted mb-4">
                        Halaman Dashboard memberikan ringkasan cepat kondisi keuangan perusahaan Anda secara real-time.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-surface-highlight/10 rounded-lg border border-border-dark">
                            <h4 class="font-bold text-white mb-2">Statistik Utama</h4>
                            <p class="text-sm text-text-muted">Menampilkan Total Aset, Pendapatan Bulan Ini, dan Laba Bersih secara sekilas.</p>
                        </div>
                        <div class="p-4 bg-surface-highlight/10 rounded-lg border border-border-dark">
                            <h4 class="font-bold text-white mb-2">Grafik Tren</h4>
                            <p class="text-sm text-text-muted">Visualisasi pendapatan vs beban dan arus kas selama periode tertentu.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Master Data -->
            <section id="master-data" class="scroll-mt-24">
                <h2 class="text-xl font-bold text-white mb-6 border-b border-border-dark pb-2">Master Data</h2>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="flex items-center gap-2 font-bold text-white mb-3">
                                <span class="material-symbols-outlined text-primary">account_tree</span>
                                Chart of Accounts (COA)
                            </h3>
                            <p class="text-text-muted text-sm mb-3">
                                Daftar akun perkiraan yang digunakan untuk menjurnal transaksi. 
                            </p>
                            <ul class="text-sm text-text-muted space-y-1 list-disc list-inside">
                                <li>Anda dapat menambah, mengedit, atau menonaktifkan akun.</li>
                                <li>Gunakan fitur <strong>Import</strong> untuk upload massal via Excel.</li>
                                <li>Akun Induk (Parent) tidak dapat digunakan untuk transaksi.</li>
                            </ul>
                        </div>

                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="flex items-center gap-2 font-bold text-white mb-3">
                                <span class="material-symbols-outlined text-primary">contacts</span>
                                Kontak
                            </h3>
                            <p class="text-text-muted text-sm mb-3">
                                Database Pelanggan (Customer) dan Pemasok (Supplier).
                            </p>
                            <ul class="text-sm text-text-muted space-y-1 list-disc list-inside">
                                <li>Wajib diisi untuk transaksi Penjualan dan Pembelian kredit.</li>
                                <li>Menyimpan informasi alamat, telepon, dan NPWP.</li>
                            </ul>
                        </div>

                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="flex items-center gap-2 font-bold text-white mb-3">
                                <span class="material-symbols-outlined text-primary">inventory_2</span>
                                Persediaan (Inventory)
                            </h3>
                            <p class="text-text-muted text-sm mb-3">
                                Daftar barang dagang atau bahan baku.
                            </p>
                            <ul class="text-sm text-text-muted space-y-1 list-disc list-inside">
                                <li><strong>FIFO/Average</strong>: Metode penilaian stok otomatis.</li>
                                <li>Stok bertambah via Pembelian, berkurang via Penjualan.</li>
                            </ul>
                        </div>

                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="flex items-center gap-2 font-bold text-white mb-3">
                                <span class="material-symbols-outlined text-primary">precision_manufacturing</span>
                                Aset Tetap
                            </h3>
                            <p class="text-text-muted text-sm mb-3">
                                Pencatatan aset jangka panjang (Gedung, Kendaraan, Peralatan).
                            </p>
                            <ul class="text-sm text-text-muted space-y-1 list-disc list-inside">
                                <li>Mencatat Tanggal Perolehan dan Harga Perolehan.</li>
                                <li>Memudahkan perhitungan penyusutan (depresiasi).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Transaksi -->
            <section id="transaksi" class="scroll-mt-24">
                <h2 class="text-xl font-bold text-white mb-6 border-b border-border-dark pb-2">Transaksi</h2>
                
                <div class="space-y-8">
                    <!-- Penjualan & Pembelian -->
                    <div>
                        <h3 class="text-lg font-bold text-primary mb-4">Penjualan & Pembelian</h3>
                        <div class="bg-surface-dark border border-border-dark rounded-xl overflow-hidden">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-surface-highlight/20 border-b border-border-dark">
                                    <tr>
                                        <th class="p-4 text-white font-bold">Fitur</th>
                                        <th class="p-4 text-white font-bold">Fungsi</th>
                                        <th class="p-4 text-white font-bold">Dampak Otomatis</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border-dark/50">
                                    <tr>
                                        <td class="p-4 text-white font-medium">Penjualan</td>
                                        <td class="p-4 text-text-muted">Mencatat invoice ke pelanggan</td>
                                        <td class="p-4 text-text-muted">Piutang (+), Penjualan (+), Stok (-)</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 text-white font-medium">Pembelian</td>
                                        <td class="p-4 text-text-muted">Mencatat tagihan dari supplier</td>
                                        <td class="p-4 text-text-muted">Hutang (+), Pembelian/Stok (+), PPN</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Kas & Bank -->
                    <div>
                        <h3 class="text-lg font-bold text-primary mb-4">Kas & Bank</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-surface-dark p-5 rounded-xl border border-border-dark">
                                <h4 class="font-bold text-white mb-2">Penerimaan Kas</h4>
                                <p class="text-sm text-text-muted">Gunakan untuk mencatat pendapatan lain-lain, setoran modal, atau penerimaan non-invoice.</p>
                            </div>
                            <div class="bg-surface-dark p-5 rounded-xl border border-border-dark">
                                <h4 class="font-bold text-white mb-2">Pengeluaran Kas</h4>
                                <p class="text-sm text-text-muted">Gunakan untuk membayar beban operasional (Listrik, Gaji, Air) atau pembelian tunai non-stok.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Jurnal Umum -->
                    <div>
                        <h3 class="text-lg font-bold text-primary mb-4">Jurnal Umum</h3>
                        <p class="text-text-muted mb-3">
                            Fitur ini digunakan untuk mencatat transaksi yang tidak tercover oleh modul Penjualan, Pembelian, atau Kas. Biasanya digunakan untuk:
                        </p>
                        <ul class="list-disc list-inside text-text-muted text-sm space-y-1 ml-4 bg-surface-dark p-4 rounded-xl border border-border-dark">
                            <li>Jurnal Penyesuaian (Adjustment) di akhir bulan.</li>
                            <li>Jurnal Penyusutan Aset.</li>
                            <li>Koreksi kesalahan pencatatan.</li>
                            <li>Jurnal Penutup (Closing Entries) - <em>sebagian otomatis</em>.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Laporan -->
            <section id="laporan" class="scroll-mt-24">
                <h2 class="text-xl font-bold text-white mb-6 border-b border-border-dark pb-2">Laporan Keuangan</h2>
                <div class="grid grid-cols-1 gap-4">
                    
                    <div class="flex gap-4 p-4 bg-surface-dark rounded-xl border border-border-dark">
                        <div class="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-blue-400">balance</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-lg">Neraca (Balance Sheet)</h4>
                            <p class="text-text-muted text-sm mt-1">
                                Menunjukkan posisi keuangan perusahaan (Aset, Kewajiban, Modal) pada tanggal tertentu. Pastikan Aset = Kewajiban + Modal (Balance).
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 p-4 bg-surface-dark rounded-xl border border-border-dark">
                        <div class="w-12 h-12 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-emerald-400">trending_up</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-lg">Laba Rugi (Profit & Loss)</h4>
                            <p class="text-text-muted text-sm mt-1">
                                Menampilkan kinerja perusahaan selama periode tertentu. Pendapatan dikurangi Beban menghasilkan Laba/Rugi Bersih.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 p-4 bg-surface-dark rounded-xl border border-border-dark">
                        <div class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-purple-400">trending_flat</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-lg">Arus Kas (Cash Flow)</h4>
                            <p class="text-text-muted text-sm mt-1">
                                Laporan pergerakan uang tunai masuk dan keluar, dikategorikan menjadi Operasional, Investasi, dan Pendanaan.
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 p-4 bg-surface-dark rounded-xl border border-border-dark">
                        <div class="w-12 h-12 rounded-lg bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-amber-400">account_balance</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-lg">Perubahan Ekuitas</h4>
                            <p class="text-text-muted text-sm mt-1">
                                Menunjukkan perubahan modal pemilik selama periode tertentu, termasuk Laba Ditahan dan Prive/Dividen.
                            </p>
                        </div>
                    </div>

                </div>
                <div class="mt-6 p-4 bg-primary/10 border border-primary/20 rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">info</span>
                    <p class="text-sm text-white">
                        Semua laporan dapat diekspor ke format <strong>PDF</strong> untuk keperluan arsip atau pelaporan (Pajak/Bank).
                    </p>
                </div>
            </section>

            <!-- Pengaturan -->
            <section id="pengaturan" class="scroll-mt-24">
                <h2 class="text-xl font-bold text-white mb-6 border-b border-border-dark pb-2">Pengaturan</h2>
                
                <div class="space-y-4">
                    <div class="bg-surface-dark border border-border-dark rounded-xl p-5">
                        <h4 class="font-bold text-white">Profil Perusahaan</h4>
                        <p class="text-text-muted text-sm mt-2">
                            Ubah logo, alamat, dan informasi dasar perusahaan yang akan tampil di kop surat laporan dan invoice.
                        </p>
                    </div>
                    <div class="bg-surface-dark border border-border-dark rounded-xl p-5">
                        <h4 class="font-bold text-white">Manajemen Pengguna</h4>
                        <p class="text-text-muted text-sm mt-2">
                            Tambah pengguna baru dan atur hak akses (Role):
                        </p>
                        <ul class="list-disc list-inside text-text-muted text-sm mt-2 space-y-1">
                            <li><strong>Administrator</strong>: Akses penuh ke semua fitur dan pengaturan.</li>
                            <li><strong>Manager</strong>: Akses laporam dan transaksi, tapi terbatas pada pengaturan.</li>
                            <li><strong>Staff</strong>: Input transaksi saja.</li>
                        </ul>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <!-- Active Link Handling Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('nav a');

            window.addEventListener('scroll', () => {
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.clientHeight;
                    if (pageYOffset >= (sectionTop - 150)) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.classList.remove('text-white', 'bg-surface-highlight', 'font-bold');
                    link.classList.add('text-text-muted');
                    if (link.getAttribute('href').includes(current)) {
                        link.classList.remove('text-text-muted');
                        link.classList.add('text-white', 'bg-surface-highlight', 'font-bold');
                    }
                });
            });
        });
    </script>
</x-app-layout>
