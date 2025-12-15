<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Jurnal Umum</h2>
                <p class="text-text-muted text-sm mt-1">Riwayat transaksi dan jurnal</p>
            </div>
            <x-btn type="primary" onclick="openCreateModal()">
                <span class="material-symbols-outlined text-xl">add</span>
                Jurnal Manual
            </x-btn>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="flex items-center gap-4 mb-6">
        <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
            <select id="sourceFilter" class="bg-transparent border-0 text-white text-sm focus:ring-0">
                <option value="">Semua Sumber</option>
                <option value="manual">Manual</option>
                <option value="sales">Penjualan</option>
                <option value="purchase">Pembelian</option>
                <option value="cash_bank">Kas & Bank</option>
            </select>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
            <span class="material-symbols-outlined text-text-muted">calendar_today</span>
            <input type="date" id="dateStart" class="bg-transparent border-0 text-white text-sm focus:ring-0">
            <span class="text-text-muted">-</span>
            <input type="date" id="dateEnd" class="bg-transparent border-0 text-white text-sm focus:ring-0">
        </div>
    </div>

    <!-- Journals List -->
    <div class="space-y-4" id="journalsList">
        <!-- Loading -->
        <div class="p-8 text-center text-text-muted">
            <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
            <p class="mt-2">Memuat data...</p>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="journalModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-2xl max-h-[90vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Jurnal Manual</h3>
                    <button onclick="closeModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="journalForm" class="p-6 space-y-4 overflow-y-auto max-h-[70vh]">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Tanggal</label>
                            <input type="date" id="date" required
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Deskripsi</label>
                            <input type="text" id="description" required placeholder="Deskripsi jurnal"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                        </div>
                    </div>

                    <!-- Journal Lines -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-text-muted">Entry Lines</label>
                            <button type="button" onclick="addLine()" class="text-primary text-sm hover:underline">+ Tambah Baris</button>
                        </div>
                        <div class="rounded-xl border border-border-dark overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-surface-dark">
                                    <tr>
                                        <th class="p-3 text-left text-text-muted font-medium">Akun</th>
                                        <th class="p-3 text-right text-text-muted font-medium w-32">Debit</th>
                                        <th class="p-3 text-right text-text-muted font-medium w-32">Kredit</th>
                                        <th class="p-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="linesBody">
                                    <!-- Lines will be added here -->
                                </tbody>
                                <tfoot class="bg-surface-dark border-t border-border-dark">
                                    <tr>
                                        <td class="p-3 text-right font-bold text-text-muted">Total</td>
                                        <td class="p-3 text-right font-bold text-white" id="totalDebit">0</td>
                                        <td class="p-3 text-right font-bold text-white" id="totalCredit">0</td>
                                        <td></td>
                                    </tr>
                                    <tr id="balanceRow" class="hidden">
                                        <td colspan="4" class="p-3 text-center">
                                            <span class="text-accent-red text-sm">⚠️ Jurnal tidak seimbang!</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <x-btn type="secondary" onclick="closeModal()">Batal</x-btn>
                        <x-btn type="primary" type="submit" id="submitBtn">Simpan Jurnal</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let accounts = [];
        let lineCount = 0;

        async function loadAccounts() {
            const response = await fetch('/accounts?detail_only=1', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            accounts = data.data || [];
        }

        async function loadJournals() {
            const source = document.getElementById('sourceFilter').value;
            const start = document.getElementById('dateStart').value;
            const end = document.getElementById('dateEnd').value;
            
            let url = '/journals?';
            if (source) url += `source=${source}&`;
            if (start) url += `date_start=${start}&`;
            if (end) url += `date_end=${end}`;
            
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            renderJournals(result.data?.data || []);
        }

        function renderJournals(journals) {
            const container = document.getElementById('journalsList');
            
            if (journals.length === 0) {
                container.innerHTML = `
                    <div class="p-12 text-center text-text-muted rounded-2xl border border-border-dark bg-surface-dark/30">
                        <span class="material-symbols-outlined text-5xl mb-3">receipt_long</span>
                        <p>Belum ada jurnal</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = journals.map(journal => `
                <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl ${getSourceColor(journal.source)} flex items-center justify-center">
                                <span class="material-symbols-outlined text-xl">${getSourceIcon(journal.source)}</span>
                            </div>
                            <div>
                                <p class="text-white font-medium">${journal.description}</p>
                                <p class="text-text-muted text-sm">${journal.reference} • ${new Date(journal.date).toLocaleDateString('id-ID')}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 rounded text-xs font-medium ${getSourceBadge(journal.source)}">${getSourceLabel(journal.source)}</span>
                    </div>
                </div>
            `).join('');
        }

        function getSourceColor(source) {
            const colors = {
                'manual': 'bg-purple-500/20 text-purple-400',
                'sales': 'bg-primary/20 text-primary',
                'purchase': 'bg-orange-500/20 text-orange-400',
                'cash_bank': 'bg-blue-500/20 text-blue-400',
            };
            return colors[source] || 'bg-gray-500/20 text-gray-400';
        }

        function getSourceIcon(source) {
            const icons = {
                'manual': 'edit_note',
                'sales': 'point_of_sale',
                'purchase': 'shopping_cart',
                'cash_bank': 'account_balance',
            };
            return icons[source] || 'receipt';
        }

        function getSourceBadge(source) {
            return getSourceColor(source);
        }

        function getSourceLabel(source) {
            const labels = {
                'manual': 'Manual',
                'sales': 'Penjualan',
                'purchase': 'Pembelian',
                'cash_bank': 'Kas & Bank',
            };
            return labels[source] || source;
        }

        function addLine() {
            lineCount++;
            const tbody = document.getElementById('linesBody');
            const tr = document.createElement('tr');
            tr.id = `line-${lineCount}`;
            tr.className = 'border-t border-border-dark/50';
            tr.innerHTML = `
                <td class="p-2">
                    <select name="account_${lineCount}" required
                            class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih Akun</option>
                        ${accounts.map(a => `<option value="${a.id}">${a.code} - ${a.name}</option>`).join('')}
                    </select>
                </td>
                <td class="p-2">
                    <input type="number" name="debit_${lineCount}" value="0" min="0" step="0.01"
                           onchange="updateTotals()"
                           class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm text-right focus:border-primary focus:ring-primary">
                </td>
                <td class="p-2">
                    <input type="number" name="credit_${lineCount}" value="0" min="0" step="0.01"
                           onchange="updateTotals()"
                           class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm text-right focus:border-primary focus:ring-primary">
                </td>
                <td class="p-2">
                    <button type="button" onclick="removeLine(${lineCount})" class="text-text-muted hover:text-accent-red">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        }

        function removeLine(id) {
            document.getElementById(`line-${id}`)?.remove();
            updateTotals();
        }

        function updateTotals() {
            let totalDebit = 0;
            let totalCredit = 0;
            
            document.querySelectorAll('[name^="debit_"]').forEach(input => {
                totalDebit += parseFloat(input.value) || 0;
            });
            document.querySelectorAll('[name^="credit_"]').forEach(input => {
                totalCredit += parseFloat(input.value) || 0;
            });
            
            document.getElementById('totalDebit').textContent = totalDebit.toLocaleString('id-ID');
            document.getElementById('totalCredit').textContent = totalCredit.toLocaleString('id-ID');
            
            const balanced = Math.abs(totalDebit - totalCredit) < 0.01;
            document.getElementById('balanceRow').classList.toggle('hidden', balanced);
            document.getElementById('submitBtn').disabled = !balanced || totalDebit === 0;
        }

        function openCreateModal() {
            document.getElementById('journalForm').reset();
            document.getElementById('date').value = new Date().toISOString().split('T')[0];
            document.getElementById('linesBody').innerHTML = '';
            lineCount = 0;
            addLine();
            addLine();
            updateTotals();
            document.getElementById('journalModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('journalModal').classList.add('hidden');
        }

        document.getElementById('journalForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const lines = [];
            document.querySelectorAll('#linesBody tr').forEach(tr => {
                const id = tr.id.replace('line-', '');
                const account_id = tr.querySelector(`[name="account_${id}"]`).value;
                const debit = parseFloat(tr.querySelector(`[name="debit_${id}"]`).value) || 0;
                const credit = parseFloat(tr.querySelector(`[name="credit_${id}"]`).value) || 0;
                if (account_id && (debit > 0 || credit > 0)) {
                    lines.push({ account_id: parseInt(account_id), debit, credit });
                }
            });
            
            const response = await fetch('/journals/manual', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    date: document.getElementById('date').value,
                    description: document.getElementById('description').value,
                    lines
                })
            });

            const result = await response.json();
            if (result.success) {
                closeModal();
                loadJournals();
            } else {
                alert(result.message || 'Terjadi kesalahan');
            }
        });

        // Set default dates
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
        const day = String(today.getDate()).padStart(2, '0');
        
        document.getElementById('dateStart').value = `${year}-${month}-01`; // First day of current month
        document.getElementById('dateEnd').value = `${year}-${month}-${day}`; // Today

        document.getElementById('sourceFilter').addEventListener('change', loadJournals);
        document.getElementById('dateStart').addEventListener('change', loadJournals);
        document.getElementById('dateEnd').addEventListener('change', loadJournals);

        // Initial load
        loadAccounts();
        loadJournals();
    </script>
    @endpush
</x-app-layout>
