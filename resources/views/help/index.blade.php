<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-white font-display">Panduan Pengguna SimpleAkunting</h2>
    </x-slot>

    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="w-full md:w-64 flex-shrink-0 md:order-last">
            <div class="sticky top-24 bg-surface-dark border border-border-dark rounded-xl p-4">
                <h3 class="font-bold text-primary mb-4 uppercase text-xs tracking-wider">Daftar Isi</h3>
                <nav class="space-y-1">
                    <a href="#pendahuluan" class="block px-3 py-2 text-sm text-white hover:bg-surface-highlight rounded-lg transition">Pendahuluan</a>
                    <a href="#memulai" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Memulai Aplikasi</a>
                    <a href="#dashboard" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Dashboard</a>
                    <a href="#master-data" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Master Data</a>
                    <a href="#transaksi" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Transaksi</a>
                    <a href="#psak69" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Aset Biologis (PSAK 69)</a>
                    <a href="#manufacturing" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Manufaktur</a>
                    <a href="#laporan" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Laporan Keuangan</a>
                    <a href="#pengaturan" class="block px-3 py-2 text-sm text-text-muted hover:text-white hover:bg-surface-highlight rounded-lg transition">Pengaturan</a>
                </nav>
                
                <!-- Link to Full Guide -->
                <div class="mt-4 pt-4 border-t border-border-dark">
                    <a href="/docs/panduan-aplikasi.md" target="_blank" class="flex items-center gap-2 text-sm text-primary hover:text-primary-light transition">
                        <span class="material-symbols-outlined text-base">open_in_new</span>
                        Panduan Lengkap (PDF)
                    </a>
                </div>
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
                        Selamat datang di <strong>SimpleAkunting V4</strong>. Aplikasi ini dirancang untuk memudahkan pencatatan dan pelaporan keuangan Anda, baik untuk UMKM, Koperasi, BUMDesa, maupun Yayasan.
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                        <div class="text-center p-4 bg-surface-highlight/10 rounded-lg">
                            <span class="text-2xl">🏪</span>
                            <p class="text-sm text-text-muted mt-2">UMKM</p>
                        </div>
                        <div class="text-center p-4 bg-surface-highlight/10 rounded-lg">
                            <span class="text-2xl">🤝</span>
                            <p class="text-sm text-text-muted mt-2">Koperasi</p>
                        </div>
                        <div class="text-center p-4 bg-surface-highlight/10 rounded-lg">
                            <span class="text-2xl">🏘️</span>
                            <p class="text-sm text-text-muted mt-2">BUMDesa</p>
                        </div>
                        <div class="text-center p-4 bg-surface-highlight/10 rounded-lg">
                            <span class="text-2xl">🏛️</span>
                            <p class="text-sm text-text-muted mt-2">Yayasan</p>
                        </div>
                    </div>
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
                        </p>
                        <ul class="list-disc list-inside text-text-muted space-y-1 ml-2">
                            <li>Pastikan email valid.</li>
                            <li>Password bersifat case-sensitive.</li>
                        </ul>
                    </div>

                    <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                        <h3 class="text-lg font-bold text-white mb-3">Setup Awal (Wizard)</h3>
                        <p class="text-text-muted">
                            Saat pertama kali membuat perusahaan, Anda akan diarahkan ke <strong>Setup Wizard</strong> untuk:
                        </p>
                        <ol class="list-decimal list-inside text-text-muted space-y-2 mt-3 ml-2">
                            <li>Mengisi profil perusahaan (Nama, Alamat, Jenis Usaha).</li>
                            <li>Memilih template Bagan Akun (COA) yang sesuai.</li>
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
                        Halaman Dashboard memberikan ringkasan kondisi keuangan perusahaan secara real-time.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-surface-highlight/10 rounded-lg border border-border-dark">
                            <h4 class="font-bold text-white mb-2">Statistik Utama</h4>
                            <p class="text-sm text-text-muted">Total Pendapatan, Total Beban, Laba Bersih, Saldo Kas</p>
                        </div>
                        <div class="p-4 bg-surface-highlight/10 rounded-lg border border-border-dark">
                            <h4 class="font-bold text-white mb-2">Quick Stats</h4>
                            <p class="text-sm text-text-muted">Pelanggan, Supplier, Invoice tertunda</p>
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
                                Daftar akun perkiraan untuk menjurnal transaksi.
                            </p>
                            <ul class="text-sm text-text-muted space-y-1 list-disc list-inside">
                                <li>Tambah, edit, atau nonaktifkan akun.</li>
                                <li><strong>Import Excel</strong> untuk upload massal.</li>
                            </ul>
                        </div>

                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="flex items-center gap-2 font-bold text-white mb-3">
                                <span class="material-symbols-outlined text-primary">contacts</span>
                                Kontak
                            </h3>
                            <p class="text-text-muted text-sm mb-3">
                                Database Pelanggan dan Supplier.
                            </p>
                            <ul class="text-sm text-text-muted space-y-1 list-disc list-inside">
                                <li>Wajib untuk transaksi kredit.</li>
                                <li><strong>Import Excel</strong> tersedia.</li>
                            </ul>
                        </div>

                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="flex items-center gap-2 font-bold text-white mb-3">
                                <span class="material-symbols-outlined text-primary">inventory_2</span>
                                Persediaan
                            </h3>
                            <p class="text-text-muted text-sm mb-3">
                                Daftar barang dagang atau bahan baku.
                            </p>
                            <ul class="text-sm text-text-muted space-y-1 list-disc list-inside">
                                <li>Tracking stok real-time.</li>
                                <li><strong>Import Excel</strong> tersedia.</li>
                            </ul>
                        </div>

                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="flex items-center gap-2 font-bold text-white mb-3">
                                <span class="material-symbols-outlined text-primary">precision_manufacturing</span>
                                Aset Tetap
                            </h3>
                            <p class="text-text-muted text-sm mb-3">
                                Pencatatan aset jangka panjang.
                            </p>
                            <ul class="text-sm text-text-muted space-y-1 list-disc list-inside">
                                <li>Tanggal & Harga Perolehan.</li>
                                <li>Perhitungan penyusutan.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Transaksi -->
            <section id="transaksi" class="scroll-mt-24">
                <h2 class="text-xl font-bold text-white mb-6 border-b border-border-dark pb-2">Transaksi</h2>
                
                <div class="space-y-8">
                    <div class="bg-surface-dark border border-border-dark rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-surface-highlight/20 border-b border-border-dark">
                                <tr>
                                    <th class="p-4 text-white font-bold">Modul</th>
                                    <th class="p-4 text-white font-bold">Fungsi</th>
                                    <th class="p-4 text-white font-bold">Dampak Otomatis</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-dark/50">
                                <tr>
                                    <td class="p-4 text-white font-medium">Penjualan</td>
                                    <td class="p-4 text-text-muted">Invoice ke pelanggan</td>
                                    <td class="p-4 text-text-muted">Piutang (+), Penjualan (+), Stok (-)</td>
                                </tr>
                                <tr>
                                    <td class="p-4 text-white font-medium">Pembelian</td>
                                    <td class="p-4 text-text-muted">Tagihan dari supplier</td>
                                    <td class="p-4 text-text-muted">Hutang (+), Stok (+)</td>
                                </tr>
                                <tr>
                                    <td class="p-4 text-white font-medium">Penerimaan Kas</td>
                                    <td class="p-4 text-text-muted">Terima pembayaran</td>
                                    <td class="p-4 text-text-muted">Kas (+), Pendapatan (+)</td>
                                </tr>
                                <tr>
                                    <td class="p-4 text-white font-medium">Pengeluaran Kas</td>
                                    <td class="p-4 text-text-muted">Bayar beban</td>
                                    <td class="p-4 text-text-muted">Kas (-), Beban (+)</td>
                                </tr>
                                <tr>
                                    <td class="p-4 text-white font-medium">Jurnal Umum</td>
                                    <td class="p-4 text-text-muted">Entry manual</td>
                                    <td class="p-4 text-text-muted">Sesuai akun yang dipilih</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- PSAK 69 -->
            <section id="psak69" class="scroll-mt-24">
                <h2 class="text-xl font-bold text-white mb-6 border-b border-border-dark pb-2">
                    <span class="text-emerald-400">🌿</span> Aset Biologis (PSAK 69)
                </h2>
                
                <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4 mb-6">
                    <p class="text-emerald-300 text-sm">
                        <strong>Catatan:</strong> Fitur ini harus diaktifkan di <strong>Pengaturan Perusahaan → Aktifkan PSAK 69</strong>
                    </p>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="font-bold text-white mb-3">Kategori Aset</h3>
                            <ul class="text-sm text-text-muted space-y-2">
                                <li>🌴 <strong>Tanaman Produktif</strong> - Kelapa sawit, karet, teh</li>
                                <li>🌾 <strong>Tanaman Semusim</strong> - Padi, jagung, sayuran</li>
                                <li>🐄 <strong>Hewan Ternak</strong> - Sapi, kambing, ayam</li>
                                <li>🥛 <strong>Hewan Produksi</strong> - Sapi perah, ayam petelur</li>
                            </ul>
                        </div>

                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="font-bold text-white mb-3">Fitur Utama</h3>
                            <ul class="text-sm text-text-muted space-y-2">
                                <li><span class="text-primary">📊</span> <strong>Valuasi</strong> - Penilaian nilai wajar</li>
                                <li><span class="text-primary">🔄</span> <strong>Transformasi</strong> - Perubahan biologis</li>
                                <li><span class="text-primary">🌾</span> <strong>Panen</strong> - Catat hasil panen</li>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-surface-dark border border-border-dark rounded-xl p-6">
                        <h3 class="font-bold text-white mb-3">Laporan PSAK 69</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <a href="{{ route('reports.biological-reconciliation') }}" class="p-3 bg-surface-highlight/20 rounded-lg text-center hover:bg-surface-highlight/40 transition">
                                <span class="text-text-muted text-sm">Rekonsiliasi</span>
                            </a>
                            <a href="{{ route('reports.biological-fair-value') }}" class="p-3 bg-surface-highlight/20 rounded-lg text-center hover:bg-surface-highlight/40 transition">
                                <span class="text-text-muted text-sm">Perubahan Nilai Wajar</span>
                            </a>
                            <a href="{{ route('reports.biological-production') }}" class="p-3 bg-surface-highlight/20 rounded-lg text-center hover:bg-surface-highlight/40 transition">
                                <span class="text-text-muted text-sm">Produksi</span>
                            </a>
                            <a href="{{ route('reports.biological-disclosure') }}" class="p-3 bg-surface-highlight/20 rounded-lg text-center hover:bg-surface-highlight/40 transition">
                                <span class="text-text-muted text-sm">Disclosure</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Manufacturing -->
            <section id="manufacturing" class="scroll-mt-24">
                <h2 class="text-xl font-bold text-white mb-6 border-b border-border-dark pb-2">
                    <span class="text-blue-400">🏭</span> Manufaktur
                </h2>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="flex items-center gap-2 font-bold text-white mb-3">
                                <span class="material-symbols-outlined text-blue-400">list_alt</span>
                                Bill of Materials (BOM)
                            </h3>
                            <p class="text-text-muted text-sm mb-3">
                                Definisikan resep produksi:
                            </p>
                            <ul class="text-sm text-text-muted space-y-1 list-disc list-inside">
                                <li>Produk jadi yang dihasilkan</li>
                                <li>Komponen/bahan baku</li>
                                <li>Kuantitas setiap komponen</li>
                            </ul>
                        </div>

                        <div class="bg-surface-dark/50 border border-border-dark rounded-xl p-6">
                            <h3 class="flex items-center gap-2 font-bold text-white mb-3">
                                <span class="material-symbols-outlined text-blue-400">factory</span>
                                Production Order
                            </h3>
                            <p class="text-text-muted text-sm mb-3">
                                Proses produksi:
                            </p>
                            <div class="flex gap-2 flex-wrap">
                                <span class="px-3 py-1 bg-gray-500/20 text-gray-300 text-xs rounded-full">Draft</span>
                                <span class="text-text-muted">→</span>
                                <span class="px-3 py-1 bg-yellow-500/20 text-yellow-300 text-xs rounded-full">In Progress</span>
                                <span class="text-text-muted">→</span>
                                <span class="px-3 py-1 bg-green-500/20 text-green-300 text-xs rounded-full">Completed</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-surface-dark border border-border-dark rounded-xl p-6">
                        <h3 class="font-bold text-white mb-3">Laporan Manufaktur</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <a href="{{ route('reports.manufacturing.production-cost') }}" class="p-3 bg-surface-highlight/20 rounded-lg text-center hover:bg-surface-highlight/40 transition">
                                <span class="text-text-muted text-sm">Biaya Produksi</span>
                            </a>
                            <a href="{{ route('reports.manufacturing.material-usage') }}" class="p-3 bg-surface-highlight/20 rounded-lg text-center hover:bg-surface-highlight/40 transition">
                                <span class="text-text-muted text-sm">Penggunaan Material</span>
                            </a>
                            <a href="{{ route('reports.manufacturing.wip') }}" class="p-3 bg-surface-highlight/20 rounded-lg text-center hover:bg-surface-highlight/40 transition">
                                <span class="text-text-muted text-sm">Work In Progress</span>
                            </a>
                        </div>
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
                                Posisi keuangan: Aset = Kewajiban + Modal
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
                                Pendapatan - Beban = Laba/Rugi Bersih
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
                                Operasional, Investasi, Pendanaan
                            </p>
                        </div>
                    </div>

                </div>
                <div class="mt-6 p-4 bg-primary/10 border border-primary/20 rounded-xl flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">picture_as_pdf</span>
                    <p class="text-sm text-white">
                        Semua laporan dapat diekspor ke <strong>PDF</strong> untuk keperluan arsip atau pelaporan.
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
                            Ubah logo, alamat, aktifkan fitur PSAK 69, dan informasi dasar perusahaan.
                        </p>
                    </div>
                    <div class="bg-surface-dark border border-border-dark rounded-xl p-5">
                        <h4 class="font-bold text-white">Manajemen Pengguna</h4>
                        <p class="text-text-muted text-sm mt-2">
                            Tambah pengguna dan atur hak akses:
                        </p>
                        <ul class="list-disc list-inside text-text-muted text-sm mt-2 space-y-1">
                            <li><strong>Administrator</strong>: Akses penuh</li>
                            <li><strong>Accountant</strong>: Transaksi & Laporan</li>
                            <li><strong>Viewer</strong>: Hanya lihat</li>
                        </ul>
                    </div>
                    <div class="bg-surface-dark border border-border-dark rounded-xl p-5">
                        <h4 class="font-bold text-white">Audit Trail</h4>
                        <p class="text-text-muted text-sm mt-2">
                            Catatan semua perubahan data: siapa, kapan, nilai sebelum & sesudah.
                        </p>
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
