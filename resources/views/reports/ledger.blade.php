<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Buku Besar</h2>
                <p class="text-text-muted text-sm mt-1" id="accountTitle">Pilih akun untuk melihat rincian</p>
            </div>
            <div class="flex items-center gap-3">
                <select id="accountSelect" class="px-4 py-2 rounded-full border border-border-dark bg-surface-dark text-white text-sm focus:ring-primary min-w-[200px]">
                    <option value="">Pilih Akun...</option>
                </select>
                <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
                    <input type="date" id="startDate" class="bg-transparent border-0 text-white text-sm focus:ring-0 w-32">
                    <span class="text-text-muted">-</span>
                    <input type="date" id="endDate" class="bg-transparent border-0 text-white text-sm focus:ring-0 w-32">
                </div>
                <button onclick="loadLedger()" class="px-6 py-2 rounded-full bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition">
                    Tampilkan
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Account Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="summaryCards">
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm uppercase tracking-wider mb-2">Saldo Awal</p>
            <p class="text-2xl font-bold text-white font-mono" id="beginningBalance">Rp 0</p>
        </div>
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <p class="text-text-muted text-sm uppercase tracking-wider mb-2">Mutasi (Debit - Kredit)</p>
            <p class="text-2xl font-bold text-white font-mono" id="mutation">Rp 0</p>
        </div>
        <div class="p-6 rounded-2xl border border-primary/30 bg-primary/10">
            <p class="text-primary text-sm uppercase tracking-wider mb-2 font-bold">Saldo Akhir</p>
            <p class="text-2xl font-bold text-primary font-mono" id="endingBalance">Rp 0</p>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="rounded-2xl border border-border-dark overflow-hidden bg-surface-dark/30">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-border-dark bg-surface-dark">
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Tanggal</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Referensi</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Keterangan</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Debit</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Kredit</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Saldo</th>
                </tr>
            </thead>
            <tbody class="text-sm" id="ledgerBody">
                <tr>
                    <td colspan="6" class="p-12 text-center text-text-muted">
                        <span class="material-symbols-outlined text-5xl mb-3">account_balance</span>
                        <p>Pilih akun dan periode untuk melihat buku besar</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @push('scripts')
    <script>
        function formatCurrency(value) {
            if (value === 0) return '-';
            const prefix = value < 0 ? '-' : '';
            return prefix + 'Rp ' + Math.abs(value).toLocaleString('id-ID');
        }

        async function loadAccounts() {
            const response = await fetch('/accounts?detail_only=1', {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            
            const select = document.getElementById('accountSelect');
            if (result.success && result.data) {
                result.data.forEach(acc => {
                    const option = document.createElement('option');
                    option.value = acc.id;
                    option.textContent = `${acc.code} - ${acc.name}`;
                    option.dataset.name = acc.name;
                    option.dataset.code = acc.code;
                    select.appendChild(option);
                });
            }
        }

        async function loadLedger() {
            const accountId = document.getElementById('accountSelect').value;
            if (!accountId) {
                alert('Pilih akun terlebih dahulu');
                return;
            }

            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const url = `/reports/ledger/${accountId}?start_date=${startDate}&end_date=${endDate}`;

            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                const data = result.data;
                
                // Update title
                document.getElementById('accountTitle').textContent = `${data.account.code} - ${data.account.name}`;
                
                // Update summary
                document.getElementById('beginningBalance').textContent = formatCurrency(data.beginning_balance);
                const mutation = data.ending_balance - data.beginning_balance;
                document.getElementById('mutation').textContent = formatCurrency(mutation);
                document.getElementById('endingBalance').textContent = formatCurrency(data.ending_balance);
                
                // Render table
                const tbody = document.getElementById('ledgerBody');
                
                if (data.transactions.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="p-8 text-center text-text-muted">Tidak ada transaksi di periode ini</td>
                        </tr>
                    `;
                } else {
                    // Opening balance row
                    let rows = `
                        <tr class="bg-surface-dark/50">
                            <td class="p-4 text-text-muted" colspan="5">Saldo Awal</td>
                            <td class="p-4 text-right font-mono text-white font-bold">${formatCurrency(data.beginning_balance)}</td>
                        </tr>
                    `;
                    
                    // Transaction rows
                    rows += data.transactions.map(tx => `
                        <tr class="border-b border-border-dark/50 hover:bg-surface-highlight/30">
                            <td class="p-4 text-white">${tx.date}</td>
                            <td class="p-4 text-primary">${tx.reference}</td>
                            <td class="p-4 text-text-muted">${tx.description}${tx.memo ? ' - ' + tx.memo : ''}</td>
                            <td class="p-4 text-right font-mono ${tx.debit > 0 ? 'text-white' : 'text-text-muted'}">${formatCurrency(tx.debit)}</td>
                            <td class="p-4 text-right font-mono ${tx.credit > 0 ? 'text-white' : 'text-text-muted'}">${formatCurrency(tx.credit)}</td>
                            <td class="p-4 text-right font-mono text-white font-bold">${formatCurrency(tx.balance)}</td>
                        </tr>
                    `).join('');
                    
                    tbody.innerHTML = rows;
                }
            }
        }

        // Init
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
        document.getElementById('endDate').value = today.toISOString().split('T')[0];
        loadAccounts();
    </script>
    @endpush
</x-app-layout>
