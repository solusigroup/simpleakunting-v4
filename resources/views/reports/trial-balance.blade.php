<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Neraca Saldo</h2>
                <p class="text-text-muted text-sm mt-1">Trial Balance - Daftar Saldo Akun</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
                    <span class="text-text-muted text-sm">Per Tanggal:</span>
                    <input type="date" id="endDate" class="bg-transparent border-0 text-white text-sm focus:ring-0">
                </div>
                <button onclick="loadReport()" class="px-6 py-2 rounded-full bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition">
                    <span class="material-symbols-outlined align-middle mr-1">refresh</span>
                    Muat
                </button>
                <button onclick="window.print()" class="px-4 py-2 rounded-full border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition">
                    <span class="material-symbols-outlined">print</span>
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Balance Check -->
    <div id="balanceAlert" class="hidden mb-6 p-4 rounded-xl bg-accent-red/10 border border-accent-red/30">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-accent-red">warning</span>
            <p class="text-accent-red font-medium">Neraca Saldo tidak seimbang!</p>
        </div>
    </div>

    <!-- Report Table -->
    <div class="rounded-2xl border border-border-dark overflow-hidden bg-surface-dark/30">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-border-dark bg-surface-dark">
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Kode</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Nama Akun</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Debit</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Kredit</th>
                </tr>
            </thead>
            <tbody class="text-sm" id="trialBalanceBody">
                <tr>
                    <td colspan="4" class="p-8 text-center text-text-muted">
                        <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
                        <p class="mt-2">Memuat data...</p>
                    </td>
                </tr>
            </tbody>
            <tfoot class="border-t-2 border-border-dark bg-surface-dark">
                <tr>
                    <td colspan="2" class="p-4 font-bold text-white">TOTAL</td>
                    <td class="p-4 text-right font-bold text-white font-mono" id="totalDebit">Rp 0</td>
                    <td class="p-4 text-right font-bold text-white font-mono" id="totalCredit">Rp 0</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @push('scripts')
    <script>
        function formatCurrency(value) {
            if (value === 0) return '-';
            return 'Rp ' + value.toLocaleString('id-ID');
        }

        function loadReport() {
            const endDate = document.getElementById('endDate').value;
            window.location.href = `/reports/trial-balance?end_date=${endDate}`;
        }

        function initializeWithServerData() {
            const serverData = @json($data ?? null);
            if (serverData) {
                renderData(serverData);
            }
        }

        function renderData(data) {
            const tbody = document.getElementById('trialBalanceBody');
            
            if (!data.accounts || data.accounts.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="p-8 text-center text-text-muted">Tidak ada data</td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = data.accounts.map(acc => `
                    <tr class="border-b border-border-dark/50 hover:bg-surface-highlight/30">
                        <td class="p-4 text-white font-mono">${acc.account_code}</td>
                        <td class="p-4 text-white">${acc.account_name}</td>
                        <td class="p-4 text-right font-mono ${acc.debit > 0 ? 'text-white' : 'text-text-muted'}">${formatCurrency(acc.debit)}</td>
                        <td class="p-4 text-right font-mono ${acc.credit > 0 ? 'text-white' : 'text-text-muted'}">${formatCurrency(acc.credit)}</td>
                    </tr>
                `).join('');
            }
            
            document.getElementById('totalDebit').textContent = formatCurrency(data.total_debit);
            document.getElementById('totalCredit').textContent = formatCurrency(data.total_credit);
            document.getElementById('balanceAlert').classList.toggle('hidden', data.is_balanced);
        }

        // Init - use server-provided data
        document.addEventListener('DOMContentLoaded', function() {
            const endDate = '{{ $end_date ?? now()->format("Y-m-d") }}';
            document.getElementById('endDate').value = endDate;
            initializeWithServerData();
        });
    </script>
    @endpush
</x-app-layout>
