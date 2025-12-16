<x-guest-layout>
    <div class="min-h-screen bg-background-dark flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            <!-- Progress Steps -->
            <div class="flex items-center justify-center gap-4 mb-8">
                <div class="flex items-center gap-2" id="step1Indicator">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-background-dark font-bold">1</div>
                    <span class="text-white font-medium hidden sm:block">Info Perusahaan</span>
                </div>
                <div class="w-16 h-0.5 bg-border-dark" id="connector1"></div>
                <div class="flex items-center gap-2" id="step2Indicator">
                    <div class="w-10 h-10 rounded-full bg-surface-dark border border-border-dark flex items-center justify-center text-text-muted font-bold">2</div>
                    <span class="text-text-muted font-medium hidden sm:block">Standar Akuntansi</span>
                </div>
                <div class="w-16 h-0.5 bg-border-dark" id="connector2"></div>
                <div class="flex items-center gap-2" id="step3Indicator">
                    <div class="w-10 h-10 rounded-full bg-surface-dark border border-border-dark flex items-center justify-center text-text-muted font-bold">3</div>
                    <span class="text-text-muted font-medium hidden sm:block">Selesai</span>
                </div>
            </div>

            <!-- Card Container -->
            <div class="bg-surface-dark rounded-2xl border border-border-dark overflow-hidden">
                <!-- Step 1: Company Info -->
                <div id="step1" class="p-8">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-primary/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-primary text-3xl">business</span>
                        </div>
                        <h2 class="text-2xl font-bold text-white mb-2">Informasi Perusahaan</h2>
                        <p class="text-text-muted">Lengkapi data perusahaan Anda</p>
                    </div>

                    <form id="companyForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Logo Perusahaan</label>
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 rounded-xl bg-background-dark border border-border-dark flex items-center justify-center" id="logoPreview">
                                    <span class="material-symbols-outlined text-text-muted text-3xl">add_photo_alternate</span>
                                </div>
                                <input type="file" id="logo" accept="image/*" class="hidden">
                                <button type="button" onclick="document.getElementById('logo').click()" 
                                        class="px-4 py-2 rounded-xl border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition">
                                    Upload Logo
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-text-muted mb-2">No. Telepon</label>
                                <input type="text" id="phone" placeholder="021-1234567"
                                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-text-muted mb-2">Email</label>
                                <input type="email" id="companyEmail" placeholder="info@company.com"
                                       class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">NPWP</label>
                            <input type="text" id="npwp" placeholder="00.000.000.0-000.000"
                                   class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Alamat</label>
                            <textarea id="address" rows="2" placeholder="Alamat lengkap perusahaan"
                                      class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Awal Tahun Fiskal</label>
                            <input type="date" id="fiscalStart" 
                                   class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        
                        <!-- Signatory Section -->
                        <div class="pt-4 border-t border-border-dark">
                            <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">verified</span>
                                Pejabat Penandatangan Laporan
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-text-muted mb-2">Nama Direktur</label>
                                    <input type="text" id="directorName" placeholder="Nama lengkap"
                                           class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-text-muted mb-2">Jabatan Direktur</label>
                                    <input type="text" id="directorTitle" placeholder="Direktur Utama" value="Direktur"
                                           class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-text-muted mb-2">Nama Sekretaris/Wadir</label>
                                    <input type="text" id="secretaryName" placeholder="Nama lengkap"
                                           class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-text-muted mb-2">Jabatan</label>
                                    <select id="secretaryTitle" 
                                            class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                        <option value="Sekretaris">Sekretaris</option>
                                        <option value="Wakil Direktur Keuangan">Wakil Direktur Keuangan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="flex justify-end mt-8">
                        <button onclick="goToStep(2)" class="px-8 py-3 rounded-full bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition">
                            Lanjutkan
                            <span class="material-symbols-outlined align-middle ml-1">arrow_forward</span>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Accounting Standard -->
                <div id="step2" class="p-8 hidden">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-primary/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-primary text-3xl">account_tree</span>
                        </div>
                        <h2 class="text-2xl font-bold text-white mb-2">Standar Akuntansi</h2>
                        <p class="text-text-muted">Pilih standar yang sesuai dengan jenis usaha Anda</p>
                    </div>

                    <div class="grid gap-4" id="standardOptions">
                        <label class="block cursor-pointer">
                            <input type="radio" name="standard" value="SAK_EP" class="hidden peer" checked>
                            <div class="p-6 rounded-xl border-2 border-border-dark peer-checked:border-primary peer-checked:bg-primary/10 transition">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-blue-400 text-2xl">storefront</span>
                                    </div>
                                    <div>
                                        <h3 class="text-white font-bold mb-1">SAK Entitas Privat (UMKM)</h3>
                                        <p class="text-text-muted text-sm">Standar Akuntansi untuk Usaha Mikro, Kecil, dan Menengah. Cocok untuk CV, PT kecil, dan usaha perseorangan.</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <label class="block cursor-pointer">
                            <input type="radio" name="standard" value="KEPMENDESA" class="hidden peer">
                            <div class="p-6 rounded-xl border-2 border-border-dark peer-checked:border-primary peer-checked:bg-primary/10 transition">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-green-400 text-2xl">agriculture</span>
                                    </div>
                                    <div>
                                        <h3 class="text-white font-bold mb-1">Kepmendesa 136/2022 (BUMDesa)</h3>
                                        <p class="text-text-muted text-sm">Standar akuntansi khusus untuk Badan Usaha Milik Desa sesuai peraturan Kemendesa.</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div class="flex justify-between mt-8">
                        <button onclick="goToStep(1)" class="px-6 py-3 rounded-full border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition">
                            <span class="material-symbols-outlined align-middle mr-1">arrow_back</span>
                            Kembali
                        </button>
                        <button onclick="saveAndContinue()" id="saveBtn" class="px-8 py-3 rounded-full bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition">
                            Simpan & Lanjutkan
                            <span class="material-symbols-outlined align-middle ml-1">arrow_forward</span>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Complete -->
                <div id="step3" class="p-8 hidden">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-primary/20 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="material-symbols-outlined text-primary text-4xl">check_circle</span>
                        </div>
                        <h2 class="text-2xl font-bold text-white mb-2">Selamat!</h2>
                        <p class="text-text-muted mb-8">Perusahaan Anda sudah siap. Chart of Accounts telah dibuat sesuai standar yang dipilih.</p>
                        
                        <div class="bg-background-dark rounded-xl p-6 mb-8 text-left">
                            <h3 class="font-bold text-white mb-4">Langkah Selanjutnya:</h3>
                            <ul class="space-y-3">
                                <li class="flex items-center gap-3 text-text-muted">
                                    <span class="material-symbols-outlined text-primary">check</span>
                                    Tambahkan pelanggan dan supplier
                                </li>
                                <li class="flex items-center gap-3 text-text-muted">
                                    <span class="material-symbols-outlined text-primary">check</span>
                                    Catat saldo awal akun-akun
                                </li>
                                <li class="flex items-center gap-3 text-text-muted">
                                    <span class="material-symbols-outlined text-primary">check</span>
                                    Mulai mencatat transaksi
                                </li>
                            </ul>
                        </div>

                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-8 py-3 rounded-full bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition">
                            <span class="material-symbols-outlined">dashboard</span>
                            Buka Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;

        function goToStep(step) {
            document.querySelectorAll('[id^="step"]').forEach(el => {
                if (el.id.match(/^step\d$/)) el.classList.add('hidden');
            });
            document.getElementById(`step${step}`).classList.remove('hidden');
            
            // Update indicators
            for (let i = 1; i <= 3; i++) {
                const indicator = document.getElementById(`step${i}Indicator`);
                const dot = indicator.querySelector('div');
                const text = indicator.querySelector('span');
                
                if (i <= step) {
                    dot.className = 'w-10 h-10 rounded-full bg-primary flex items-center justify-center text-background-dark font-bold';
                    if (text) text.className = 'text-white font-medium hidden sm:block';
                } else {
                    dot.className = 'w-10 h-10 rounded-full bg-surface-dark border border-border-dark flex items-center justify-center text-text-muted font-bold';
                    if (text) text.className = 'text-text-muted font-medium hidden sm:block';
                }
            }
            
            currentStep = step;
        }

        async function saveAndContinue() {
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin align-middle mr-2">progress_activity</span> Menyimpan...';

            const standard = document.querySelector('input[name="standard"]:checked').value;
            
            // Save company info
            const companyData = {
                phone: document.getElementById('phone').value,
                email: document.getElementById('companyEmail').value,
                npwp: document.getElementById('npwp').value,
                address: document.getElementById('address').value,
                fiscal_start: document.getElementById('fiscalStart').value,
                director_name: document.getElementById('directorName').value,
                director_title: document.getElementById('directorTitle').value,
                secretary_name: document.getElementById('secretaryName').value,
                secretary_title: document.getElementById('secretaryTitle').value,
            };

            try {
                // Update company
                await fetch('/api/company/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(companyData)
                });

                // Initialize COA
                const coaResponse = await fetch('/setup/init-coa', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ standard })
                });

                const result = await coaResponse.json();
                if (result.success) {
                    goToStep(3);
                } else {
                    alert(result.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan saat menyimpan');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Simpan & Lanjutkan <span class="material-symbols-outlined align-middle ml-1">arrow_forward</span>';
            }
        }

        // Set default fiscal start
        document.getElementById('fiscalStart').value = new Date().getFullYear() + '-01-01';

        // Logo preview
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-xl">`;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-guest-layout>
