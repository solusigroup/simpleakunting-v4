<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Import Pelanggan Internet</h2>
                <p class="text-text-muted text-sm mt-1">Upload file Excel untuk import Pelanggan Internet secara massal</p>
            </div>
            <x-btn type="secondary" onclick="window.location.href='{{ route('internet.index') }}'">
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
                    <li>Isi data pelanggan internet sesuai format (lihat sheet Instructions)</li>
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
                <p class="text-text-muted text-sm">Template Excel berisi format dan contoh data pelanggan internet</p>
            </div>
            <x-btn type="primary" onclick="window.location.href='{{ route('internet.import.template') }}'">
                <span class="material-symbols-outlined text-xl">download</span>
                Download Template Excel
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
                <x-btn type="secondary" onclick="window.location.href='{{ route('internet.index') }}'">
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

        <div id="successTable" class="hidden mb-6">
            <h4 class="text-white font-medium mb-3">✓ Data Berhasil Di-import</h4>
            <div class="rounded-xl border border-border-dark overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-dark bg-surface-dark">
                            <th class="p-3 text-xs font-bold text-text-muted uppercase text-left">Row</th>
                            <th class="p-3 text-xs font-bold text-text-muted uppercase text-left">Customer ID</th>
                            <th class="p-3 text-xs font-bold text-text-muted uppercase text-left">Nama</th>
                        </tr>
                    </thead>
                    <tbody id="successBody" class="text-sm"></tbody>
                </table>
            </div>
        </div>

        <div id="errorTable" class="hidden">
            <h4 class="text-white font-medium mb-3">✗ Data Gagal Di-import</h4>
            <div class="rounded-xl border border-border-dark overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-dark bg-surface-dark">
                            <th class="p-3 text-xs font-bold text-text-muted uppercase text-left">Row</th>
                            <th class="p-3 text-xs font-bold text-text-muted uppercase text-left">Customer ID</th>
                            <th class="p-3 text-xs font-bold text-text-muted uppercase text-left">Nama</th>
                            <th class="p-3 text-xs font-bold text-text-muted uppercase text-left">Error</th>
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
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const importBtn = document.getElementById('importBtn');

        // Drag & Drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-primary');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-primary');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-primary');
            if (e.dataTransfer.files.length > 0) {
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            const ext = file.name.split('.').pop().toLowerCase();
            if (ext !== 'xlsx' && ext !== 'xls') {
                showToast('Hanya file Excel (.xlsx atau .xls) yang diperbolehkan.', 'error');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                showToast('Ukuran file maksimal 5MB.', 'error');
                return;
            }

            selectedFile = file;
            fileName.textContent = file.name;
            fileSize.textContent = formatBytes(file.size);
            
            dropZone.classList.add('hidden');
            fileInfo.classList.remove('hidden');
            importBtn.removeAttribute('disabled');
        }

        function clearFile() {
            selectedFile = null;
            fileInput.value = '';
            dropZone.classList.remove('hidden');
            fileInfo.classList.add('hidden');
            importBtn.setAttribute('disabled', 'true');
        }

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function submitImport(e) {
            e.preventDefault();
            if (!selectedFile) return;

            const formData = new FormData();
            formData.append('file', selectedFile);
            formData.append('_token', '{{ csrf_token() }}');

            importBtn.setAttribute('disabled', 'true');
            importBtn.innerHTML = '<span class="material-symbols-outlined text-xl animate-spin">progress_activity</span> Mengimport...';

            fetch('{{ route("internet.import") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    displayResults(data.data);
                } else {
                    showToast(data.message || 'Gagal melakukan import.', 'error');
                    importBtn.removeAttribute('disabled');
                    importBtn.innerHTML = '<span class="material-symbols-outlined text-xl">upload</span> Import Sekarang';
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Terjadi kesalahan saat memproses data.', 'error');
                importBtn.removeAttribute('disabled');
                importBtn.innerHTML = '<span class="material-symbols-outlined text-xl">upload</span> Import Sekarang';
            });
        }

        function displayResults(results) {
            // Update stats
            document.getElementById('successCount').textContent = results.success_count;
            document.getElementById('errorCount').textContent = results.error_count;

            // Success table
            const successBody = document.getElementById('successBody');
            successBody.innerHTML = '';
            if (results.imported && results.imported.length > 0) {
                results.imported.forEach(row => {
                    successBody.innerHTML += `
                        <tr class="border-t border-border-dark/30 hover:bg-surface-dark/20 transition">
                            <td class="p-3 text-text-muted font-mono">${row.row}</td>
                            <td class="p-3 text-white font-mono">${row.customer_id}</td>
                            <td class="p-3 text-white">${row.name}</td>
                        </tr>
                    `;
                });
                document.getElementById('successTable').classList.remove('hidden');
            } else {
                document.getElementById('successTable').classList.add('hidden');
            }

            // Error table
            const errorBody = document.getElementById('errorBody');
            errorBody.innerHTML = '';
            if (results.errors && results.errors.length > 0) {
                results.errors.forEach(row => {
                    errorBody.innerHTML += `
                        <tr class="border-t border-border-dark/30 hover:bg-surface-dark/20 transition">
                            <td class="p-3 text-text-muted font-mono">${row.row}</td>
                            <td class="p-3 text-white font-mono">${row.customer_id || '-'}</td>
                            <td class="p-3 text-white">${row.name || '-'}</td>
                            <td class="p-3 text-accent-red">${row.error}</td>
                        </tr>
                    `;
                });
                document.getElementById('errorTable').classList.remove('hidden');
            } else {
                document.getElementById('errorTable').classList.add('hidden');
            }

            // Hide/Reset upload controls
            document.getElementById('resultsSection').classList.remove('hidden');
            clearFile();
            
            // Adjust button layout
            importBtn.innerHTML = '<span class="material-symbols-outlined text-xl">check</span> Selesai';
            importBtn.onclick = () => window.location.href = '{{ route("internet.index") }}';
            importBtn.removeAttribute('disabled');
        }
    </script>
    @endpush
</x-app-layout>
