<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white font-display">Tutup Buku</h2>
            <p class="text-text-muted text-sm mt-1">Proses penutupan periode akuntansi</p>
        </div>
    </x-slot>

    <!-- Info -->
    <div class="mb-6 p-4 rounded-xl bg-blue-500/10 border border-blue-500/30 flex items-start gap-3">
        <span class="material-symbols-outlined text-blue-400 mt-0.5">info</span>
        <div class="text-sm text-blue-400">
            <p class="font-medium mb-1">Tentang Tutup Buku</p>
            <p class="text-blue-400/80">Proses tutup buku akan memindahkan saldo akun Pendapatan dan Beban ke akun Laba Ditahan. Pastikan semua transaksi periode berjalan sudah dicatat sebelum melakukan tutup buku.</p>
        </div>
    </div>

    <!-- Period Selection -->
    <div class="rounded-2xl border border-border-dark bg-surface-dark/30 p-6 mb-6">
        <h3 class="text-lg font-bold text-white mb-4">Pilih Periode</h3>
        <form id="closingForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Tahun</label>
                    <select id="year" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Bulan (opsional)</label>
                    <select id="month" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        <option value="">Tutup Tahunan</option>
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $name)
                        <option value="{{ $i + 1 }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <x-btn type="primary" onclick="previewClosing()">
                <span class="material-symbols-outlined text-xl">preview</span>
                Preview Jurnal Penutup
            </x-btn>
        </form>
    </div>

    <!-- Preview Section (Hidden initially) -->
    <div id="previewSection" class="hidden">
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-border-dark bg-surface-highlight/30">
                <h3 class="font-bold text-white">Preview Jurnal Penutup</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4" id="previewContent">
                    <!-- Will be filled by JavaScript -->
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <x-btn type="secondary" onclick="hidePreview()">Batal</x-btn>
            <x-btn type="primary" onclick="executeClosing()">
                <span class="material-symbols-outlined text-xl">lock</span>
                Eksekusi Tutup Buku
            </x-btn>
        </div>
    </div>

    <!-- Note -->
    <div class="mt-6 p-4 rounded-xl border border-border-dark bg-surface-dark/30">
        <h4 class="text-white font-bold mb-2">Catatan Penting:</h4>
        <ul class="text-sm text-text-muted space-y-1 list-disc list-inside">
            <li>Tutup buku akan membuat jurnal penutup secara otomatis</li>
            <li>Saldo akun Pendapatan akan dipindahkan ke Laba Ditahan</li>
            <li>Saldo akun Beban akan dipindahkan ke Laba Ditahan</li>
            <li>Proses ini tidak dapat dibatalkan, pastikan data sudah benar</li>
        </ul>
    </div>

    @push('scripts')
    <script>
        function previewClosing() {
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;
            
            // Show preview with mock data (in real app, fetch from API)
            document.getElementById('previewContent').innerHTML = `
                <div class="text-text-muted text-sm mb-4">
                    Periode: ${month ? getMonthName(month) + ' ' : ''}${year}
                </div>
                <div class="space-y-3">
                    <div class="p-4 rounded-xl bg-surface-highlight/30">
                        <div class="flex justify-between mb-2">
                            <span class="text-white font-medium">Ikhtisar Laba Rugi</span>
                            <span class="text-primary">Debit</span>
                        </div>
                        <p class="text-text-muted text-sm">Menutup akun Pendapatan</p>
                    </div>
                    <div class="p-4 rounded-xl bg-surface-highlight/30">
                        <div class="flex justify-between mb-2">
                            <span class="text-white font-medium">Ikhtisar Laba Rugi</span>
                            <span class="text-red-400">Kredit</span>
                        </div>
                        <p class="text-text-muted text-sm">Menutup akun Beban</p>
                    </div>
                    <div class="p-4 rounded-xl bg-primary/10 border border-primary/30">
                        <div class="flex justify-between mb-2">
                            <span class="text-white font-medium">Laba Ditahan</span>
                            <span class="text-primary">Transfer Laba/Rugi</span>
                        </div>
                        <p class="text-text-muted text-sm">Memindahkan saldo Ikhtisar Laba Rugi ke Laba Ditahan</p>
                    </div>
                </div>
            `;
            
            document.getElementById('previewSection').classList.remove('hidden');
        }

        function hidePreview() {
            document.getElementById('previewSection').classList.add('hidden');
        }

        function getMonthName(month) {
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                           'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return months[parseInt(month) - 1];
        }

        async function executeClosing() {
            if (!confirm('Yakin ingin melakukan tutup buku? Proses ini tidak dapat dibatalkan.')) return;
            
            alert('Fitur tutup buku dalam pengembangan. Jurnal penutup akan dibuat secara otomatis.');
            // In real implementation, call API to execute closing
        }
    </script>
    @endpush
</x-app-layout>
