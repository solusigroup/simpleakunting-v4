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
            <p class="text-blue-400/80">Proses tutup buku akan memindahkan saldo akun Pendapatan dan Beban ke akun Ikhtisar Laba-Rugi, lalu ke Laba Ditahan. Pastikan semua transaksi periode berjalan sudah dicatat sebelum melakukan tutup buku.</p>
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
            <x-btn type="primary" onclick="executeClosing()" id="executeBtn">
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
            <li>Saldo akun Pendapatan akan dipindahkan ke Ikhtisar Laba-Rugi</li>
            <li>Saldo akun Beban akan dipindahkan ke Ikhtisar Laba-Rugi</li>
            <li>Selisih Laba/Rugi akan dipindahkan ke Laba Ditahan</li>
            <li>Proses ini tidak dapat dibatalkan, pastikan data sudah benar</li>
        </ul>
    </div>

    @push('scripts')
    <script>
        let previewData = null;

        async function previewClosing() {
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;
            
            try {
                const response = await fetch('/journals/closing/preview', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ year: parseInt(year), month: month ? parseInt(month) : null })
                });

                const result = await response.json();
                
                if (!result.success) {
                    alert(result.message || 'Terjadi kesalahan');
                    return;
                }

                previewData = result.data;
                renderPreview(result.data);
                document.getElementById('previewSection').classList.remove('hidden');

            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan saat memuat preview');
            }
        }

        function renderPreview(data) {
            const formatRupiah = (num) => 'Rp ' + Math.abs(num).toLocaleString('id-ID');
            
            let html = `
                <div class="text-text-muted text-sm mb-4">
                    Periode: <span class="text-white font-medium">${data.period}</span>
                </div>
            `;

            // Revenue accounts
            if (data.revenue_accounts.length > 0) {
                html += `
                    <div class="p-4 rounded-xl bg-green-500/10 border border-green-500/30 mb-3">
                        <h4 class="text-green-400 font-bold mb-2">Menutup Akun Pendapatan</h4>
                        <div class="space-y-1 text-sm">
                            ${data.revenue_accounts.map(a => `
                                <div class="flex justify-between">
                                    <span class="text-text-muted">${a.code} - ${a.name}</span>
                                    <span class="text-green-400">Debit: ${formatRupiah(a.balance)}</span>
                                </div>
                            `).join('')}
                        </div>
                        <div class="mt-2 pt-2 border-t border-green-500/30 flex justify-between font-bold">
                            <span class="text-white">Total Pendapatan</span>
                            <span class="text-green-400">${formatRupiah(data.total_revenue)}</span>
                        </div>
                    </div>
                `;
            }

            // Expense accounts
            if (data.expense_accounts.length > 0) {
                html += `
                    <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 mb-3">
                        <h4 class="text-red-400 font-bold mb-2">Menutup Akun Beban</h4>
                        <div class="space-y-1 text-sm">
                            ${data.expense_accounts.map(a => `
                                <div class="flex justify-between">
                                    <span class="text-text-muted">${a.code} - ${a.name}</span>
                                    <span class="text-red-400">Kredit: ${formatRupiah(a.balance)}</span>
                                </div>
                            `).join('')}
                        </div>
                        <div class="mt-2 pt-2 border-t border-red-500/30 flex justify-between font-bold">
                            <span class="text-white">Total Beban</span>
                            <span class="text-red-400">${formatRupiah(data.total_expense)}</span>
                        </div>
                    </div>
                `;
            }

            // Net Income
            const isProfit = data.net_income >= 0;
            html += `
                <div class="p-4 rounded-xl ${isProfit ? 'bg-primary/10 border-primary/30' : 'bg-orange-500/10 border-orange-500/30'} border">
                    <div class="flex justify-between items-center">
                        <span class="text-white font-bold">${isProfit ? 'Laba Bersih' : 'Rugi Bersih'}</span>
                        <span class="${isProfit ? 'text-primary' : 'text-orange-400'} font-bold text-xl">${formatRupiah(data.net_income)}</span>
                    </div>
                    <p class="text-text-muted text-sm mt-1">Akan dipindahkan ke Laba Ditahan</p>
                </div>
            `;

            document.getElementById('previewContent').innerHTML = html;
        }

        function hidePreview() {
            document.getElementById('previewSection').classList.add('hidden');
            previewData = null;
        }

        async function executeClosing() {
            if (!confirm('Yakin ingin melakukan tutup buku? Proses ini tidak dapat dibatalkan.')) return;
            
            const year = document.getElementById('year').value;
            const month = document.getElementById('month').value;

            const btn = document.getElementById('executeBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined text-xl animate-spin">progress_activity</span> Memproses...';

            try {
                const response = await fetch('/journals/closing/execute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ year: parseInt(year), month: month ? parseInt(month) : null })
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('Tutup buku berhasil! Jurnal penutup telah dibuat.\n\nLaba/Rugi: Rp ' + Math.abs(result.data.net_income).toLocaleString('id-ID'));
                    hidePreview();
                    window.location.reload();
                } else {
                    alert(result.message || 'Terjadi kesalahan');
                }

            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan saat eksekusi tutup buku');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined text-xl">lock</span> Eksekusi Tutup Buku';
            }
        }
    </script>
    @endpush
</x-app-layout>
