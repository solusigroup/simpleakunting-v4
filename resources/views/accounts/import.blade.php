<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Import Chart of Accounts</h2>
                <p class="text-text-muted text-sm mt-1">Upload file Excel untuk import akun secara massal</p>
            </div>
            <x-btn type="secondary" onclick="window.location.href='/accounts'">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
                Kembali
            </x-btn>
        </div>
    </x-slot>

    <!-- Instructions -->
    <div class="bg-blue-500/10 border border-blue-500/30 rounded-2xl p-6 mb-6">
        <div class="flex items-start gap-4">
            <span class="material-symbols-outlined text-blue-400 text-3xl">info</span>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-blue-400 mb-2">Cara Import</h3>
                <ol class="list-decimal list-inside space-y-2 text-text-muted text-sm">
                    <li>Download template Excel dengan klik tombol di bawah</li>
                    <li>Isi data akun sesuai format (lihat sheet Instructions)</li>
                    <li>Upload file Excel yang sudah diisi</li>
                    <li>Review hasil import (sukses dan error)</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Download Template -->
    <div class="bg-surface-dark/30 border border-border-dark rounded-2xl p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white mb-2">Step 1: Download Template</h3>
                <p class="text-text-muted text-sm">Template Excel berisi format dan contoh data</p>
            </div>
            <x-btn type="primary" onclick="window.location.href='/accounts/import/template'">
                <span class="material-symbols-outlined text-xl">download</span>
                Download Template Excel
            </x-btn>
        </div>
    </div>

    <!-- Load Default -->
    <div class="bg-surface-dark/30 border border-border-dark rounded-2xl p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white mb-2">Opsi Alternatif: Reset & Load Default COA</h3>
                <p class="text-text-muted text-sm">Hapus SEMUA akun yang ada dan ganti dengan struktur standar BUMDesa.</p>
                <p class="text-red-400 text-xs mt-1 font-bold"><span class="material-symbols-outlined text-sm align-bottom">dangerous</span> PERINGATAN: Semua akun yang ada saat ini akan DIHAPUS. Proses ini tidak dapat dibatalkan!</p>
            </div>
            <x-btn type="secondary" onclick="loadDefaultCoa()" id="loadDefaultBtn" class="border-red-500/50 text-red-500 hover:bg-red-500/10">
                <span class="material-symbols-outlined text-xl">delete_forever</span>
                Reset & Load COA
            </x-btn>
        </div>
    </div>



    <!-- Upload File -->
    <div class="bg-surface-dark/30 border border-border-dark rounded-2xl p-6 mb-6">
        <h3 class="text-lg font-bold text-white mb-4">Step 2: Upload File</h3>
        
        <form id="importForm" enctype="multipart/form-data">
            @csrf
            <div id="dropZone" class="border-2 border-dashed border-border-dark rounded-xl p-12 text-center hover:border-primary transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-6xl text-text-muted mb-4 block">upload_file</span>
                <p class="text-white font-medium mb-2">Drag & drop file Excel di sini</p>
                <p class="text-text-muted text-sm mb-4">atau</p>
                <input type="file" id="fileInput" name="file" accept=".xlsx,.xls" class="hidden">
                <x-btn type="secondary" onclick="document.getElementById('fileInput').click(); return false;">
                    Browse File
                </x-btn>
                <p class="text-text-muted text-xs mt-4">Format: .xlsx atau .xls (Max: 5MB)</p>
            </div>

            <div id="fileInfo" class="hidden mt-4 p-4 bg-surface-dark border border-border-dark rounded-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary">description</span>
                        <div>
                            <p class="text-white font-medium" id="fileName"></p>
                            <p class="text-text-muted text-sm" id="fileSize"></p>
                        </div>
                    </div>
                    <button type="button" onclick="clearFile()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <x-btn type="secondary" onclick="window.location.href='/accounts'">
                    Batal
                </x-btn>
                <x-btn type="primary" onclick="submitImport(event)" id="importBtn" disabled>
                    <span class="material-symbols-outlined text-xl">upload</span>
                    Import Sekarang
                </x-btn>
            </div>
        </form>
    </div>

    <!-- Import Results -->
    <div id="resultsSection" class="hidden bg-surface-dark/30 border border-border-dark rounded-2xl p-6">
        <h3 class="text-lg font-bold text-white mb-4">Hasil Import</h3>
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-green-400 text-3xl">check_circle</span>
                    <div>
                        <p class="text-text-muted text-sm">Berhasil</p>
                        <p class="text-2xl font-bold text-green-400" id="successCount">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-400 text-3xl">error</span>
                    <div>
                        <p class="text-text-muted text-sm">Gagal</p>
                        <p class="text-2xl font-bold text-red-400" id="errorCount">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Table -->
        <div id="successTable" class="hidden mb-6">
            <h4 class="text-white font-medium mb-3">✓ Data Berhasil Di-import</h4>
            <div class="rounded-xl border border-border-dark overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-dark bg-surface-dark">
                            <th class="p-3 text-xs font-bold text-text-muted uppercase">Row</th>
                            <th class="p-3 text-xs font-bold text-text-muted uppercase">Code</th>
                            <th class="p-3 text-xs font-bold text-text-muted uppercase">Name</th>
                        </tr>
                    </thead>
                    <tbody id="successBody" class="text-sm"></tbody>
                </table>
            </div>
        </div>

        <!-- Error Table -->
        <div id="errorTable" class="hidden">
            <h4 class="text-white font-medium mb-3">✗ Data Gagal Di-import</h4>
            <div class="rounded-xl border border-border-dark overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border-dark bg-surface-dark">
                            <th class="p-3 text-xs font-bold text-text-muted uppercase">Row</th>
                            <th class="p-3 text-xs font-bold text-text-muted uppercase">Code</th>
                            <th class="p-3 text-xs font-bold text-text-muted uppercase">Name</th>
                            <th class="p-3 text-xs font-bold text-text-muted uppercase">Error</th>
                        </tr>
                    </thead>
                    <tbody id="errorBody" class="text-sm"></tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let selectedFile = null;

        // Drag & Drop
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-primary', 'bg-primary/5');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-primary', 'bg-primary/5');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-primary', 'bg-primary/5');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            // Validate file type
            const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
            if (!validTypes.includes(file.type)) {
                alert('File harus berformat .xlsx atau .xls');
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file maksimal 5MB');
                return;
            }

            selectedFile = file;
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatFileSize(file.size);
            document.getElementById('fileInfo').classList.remove('hidden');
            document.getElementById('importBtn').disabled = false;
        }

        function clearFile() {
            selectedFile = null;
            fileInput.value = '';
            document.getElementById('fileInfo').classList.add('hidden');
            document.getElementById('importBtn').disabled = true;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        async function submitImport(e) {
            e.preventDefault();
            
            if (!selectedFile) {
                alert('Pilih file terlebih dahulu');
                return;
            }

            const importBtn = document.getElementById('importBtn');
            const originalText = importBtn.innerHTML;
            importBtn.disabled = true;
            importBtn.innerHTML = '<span class="material-symbols-outlined text-xl animate-spin">progress_activity</span> Importing...';

            const formData = new FormData();
            formData.append('file', selectedFile);

            try {
                const response = await fetch('/accounts/import', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    displayResults(result.data);
                } else {
                    alert(result.message || 'Terjadi kesalahan saat import');
                }
            } catch (error) {
                console.error('Import error:', error);
                alert('Terjadi kesalahan saat import');
            } finally {
                importBtn.disabled = false;
                importBtn.innerHTML = originalText;
            }
        }

        function displayResults(data) {
            document.getElementById('resultsSection').classList.remove('hidden');
            document.getElementById('successCount').textContent = data.success_count;
            document.getElementById('errorCount').textContent = data.error_count;

            // Success table
            if (data.imported && data.imported.length > 0) {
                document.getElementById('successTable').classList.remove('hidden');
                const successBody = document.getElementById('successBody');
                successBody.innerHTML = data.imported.map(item => `
                    <tr class="border-b border-border-dark/50">
                        <td class="p-3 text-text-muted">${item.row}</td>
                        <td class="p-3 text-white font-mono">${item.code}</td>
                        <td class="p-3 text-white">${item.name}</td>
                    </tr>
                `).join('');
            }

            // Error table
            if (data.errors && data.errors.length > 0) {
                document.getElementById('errorTable').classList.remove('hidden');
                const errorBody = document.getElementById('errorBody');
                errorBody.innerHTML = data.errors.map(item => `
                    <tr class="border-b border-border-dark/50">
                        <td class="p-3 text-text-muted">${item.row}</td>
                        <td class="p-3 text-white font-mono">${item.code}</td>
                        <td class="p-3 text-white">${item.name}</td>
                        <td class="p-3 text-red-400">${item.error}</td>
                    </tr>
                `).join('');
            }

            // Scroll to results
            document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
        }

        async function loadDefaultCoa() {
            if (!confirm('PERINGATAN KERAS:\n\nTindakan ini akan MENGHAPUS SEMUA akun yang ada saat ini dan menggantinya dengan Default COA.\n\nAkun tidak dapat dihapus jika sudah digunakan dalam transaksi.\n\nApakah Anda yakin ingin melanjutkan?')) {
                return;
            }

            const btn = document.getElementById('loadDefaultBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined text-xl animate-spin">progress_activity</span> Deleting & Seeding...';

            try {
                const response = await fetch('/accounts/import/default', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.href = '/accounts';
                } else {
                    alert(result.message || 'Gagal memuat COA');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat COA');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    </script>
    @endpush
</x-app-layout>
