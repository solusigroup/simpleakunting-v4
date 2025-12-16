<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Laporan Perubahan Ekuitas</h2>
                <p class="text-text-muted text-sm mt-1">Statement of Changes in Equity</p>
            </div>
            <div class="flex items-center gap-3" x-data="{ exportOpen: false }">
                <!-- Period Filter -->
                <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
                    <span class="text-text-muted text-sm">Periode:</span>
                    <input type="date" id="startDate" class="bg-transparent border-0 text-white text-sm focus:ring-0 w-32">
                    <span class="text-text-muted">-</span>
                    <input type="date" id="endDate" class="bg-transparent border-0 text-white text-sm focus:ring-0 w-32">
                </div>

                <button onclick="loadReport()" class="px-6 py-2 rounded-full bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition">
                    <span class="material-symbols-outlined align-middle mr-1">refresh</span>
                    Muat Ulang
                </button>

                <!-- Export Dropdown -->
                <div class="relative">
                    <button @click="exportOpen = !exportOpen" 
                            class="px-4 py-2 rounded-full border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition flex items-center gap-2">
                        <span class="material-symbols-outlined">download</span>
                        <span class="hidden sm:inline">Export</span>
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </button>
                    <div x-show="exportOpen" @click.away="exportOpen = false"
                         x-transition
                         class="absolute right-0 mt-2 w-48 rounded-xl border border-border-dark bg-surface-dark shadow-xl z-10">
                        <a href="#" onclick="exportPDF(); return false;" 
                           class="flex items-center gap-3 px-4 py-3 hover:bg-surface-highlight rounded-t-xl transition">
                            <span class="material-symbols-outlined text-accent-red">picture_as_pdf</span>
                            <span class="text-white">Export PDF</span>
                        </a>
                        <a href="#" onclick="window.print(); return false;" 
                           class="flex items-center gap-3 px-4 py-3 hover:bg-surface-highlight rounded-b-xl border-t border-border-dark transition">
                            <span class="material-symbols-outlined text-text-muted">print</span>
                            <span class="text-white">Print</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">account_balance</span>
                </div>
                <span class="text-text-muted text-sm uppercase tracking-wider">Modal Awal</span>
            </div>
            <p class="text-2xl font-bold text-white font-mono" id="beginningEquity">Rp 0</p>
        </div>
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-400">add_circle</span>
                </div>
                <span class="text-text-muted text-sm uppercase tracking-wider">Penambahan</span>
            </div>
            <p class="text-2xl font-bold text-blue-400 font-mono" id="additions">Rp 0</p>
        </div>
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-accent-red/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-accent-red">remove_circle</span>
                </div>
                <span class="text-text-muted text-sm uppercase tracking-wider">Pengurangan</span>
            </div>
            <p class="text-2xl font-bold text-accent-red font-mono" id="deductions">Rp 0</p>
        </div>
        <div class="p-6 rounded-2xl border border-primary/30 bg-primary/10">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">savings</span>
                </div>
                <span class="text-primary text-sm uppercase tracking-wider font-bold">Modal Akhir</span>
            </div>
            <p class="text-2xl font-bold text-primary font-mono" id="endingEquity">Rp 0</p>
        </div>
    </div>

    <!-- Equity Changes Table -->
    <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-dark bg-surface-dark">
            <h3 class="font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">account_tree</span>
                PERUBAHAN EKUITAS
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-dark/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-white font-bold">Keterangan</th>
                        <th class="px-6 py-4 text-right text-white font-bold">Modal Disetor</th>
                        <th class="px-6 py-4 text-right text-white font-bold">Laba Ditahan</th>
                        <th class="px-6 py-4 text-right text-white font-bold">Total Ekuitas</th>
                    </tr>
                </thead>
                <tbody id="equityTableBody" class="divide-y divide-border-dark/50">
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-text-muted">
                            <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
                            <p class="mt-2">Memuat data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        function formatCurrency(value) {
            const absValue = Math.abs(value);
            const formatted = 'Rp ' + absValue.toLocaleString('id-ID');
            return value < 0 ? '(' + formatted + ')' : formatted;
        }

        async function loadReport() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            try {
                const response = await fetch(`/reports/equity-changes?start_date=${startDate}&end_date=${endDate}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    
                    // Update summary cards
                    document.getElementById('beginningEquity').textContent = formatCurrency(data.beginning_equity);
                    document.getElementById('additions').textContent = formatCurrency(data.additions);
                    document.getElementById('deductions').textContent = formatCurrency(data.deductions);
                    document.getElementById('endingEquity').textContent = formatCurrency(data.ending_equity);
                    
                    // Update table
                    renderTable(data);
                }
            } catch (error) {
                console.error('Error loading report:', error);
            }
        }

        function renderTable(data) {
            const tbody = document.getElementById('equityTableBody');
            let rows = `
                <tr class="bg-surface-highlight/30">
                    <td class="px-6 py-4 font-bold text-white">Saldo Awal</td>
                    <td class="px-6 py-4 text-right font-mono text-white">${formatCurrency(data.beginning_capital)}</td>
                    <td class="px-6 py-4 text-right font-mono text-white">${formatCurrency(data.beginning_retained)}</td>
                    <td class="px-6 py-4 text-right font-mono font-bold text-white">${formatCurrency(data.beginning_equity)}</td>
                </tr>
            `;

            // Additions
            if (data.changes && data.changes.length > 0) {
                data.changes.forEach(change => {
                    const colorClass = change.amount >= 0 ? 'text-primary' : 'text-accent-red';
                    rows += `
                        <tr class="hover:bg-surface-highlight/20 transition">
                            <td class="px-6 py-3 text-text-muted">${change.description}</td>
                            <td class="px-6 py-3 text-right font-mono ${colorClass}">${change.type === 'capital' ? formatCurrency(change.amount) : '-'}</td>
                            <td class="px-6 py-3 text-right font-mono ${colorClass}">${change.type === 'retained' ? formatCurrency(change.amount) : '-'}</td>
                            <td class="px-6 py-3 text-right font-mono ${colorClass}">${formatCurrency(change.amount)}</td>
                        </tr>
                    `;
                });
            }

            // Net Income/Loss
            rows += `
                <tr class="hover:bg-surface-highlight/20 transition">
                    <td class="px-6 py-3 text-text-muted">Laba (Rugi) Periode Berjalan</td>
                    <td class="px-6 py-3 text-right font-mono">-</td>
                    <td class="px-6 py-3 text-right font-mono ${data.net_income >= 0 ? 'text-primary' : 'text-accent-red'}">${formatCurrency(data.net_income)}</td>
                    <td class="px-6 py-3 text-right font-mono ${data.net_income >= 0 ? 'text-primary' : 'text-accent-red'}">${formatCurrency(data.net_income)}</td>
                </tr>
            `;

            // Ending Balance
            rows += `
                <tr class="bg-primary/10 border-t-2 border-primary/30">
                    <td class="px-6 py-4 font-bold text-primary">Saldo Akhir</td>
                    <td class="px-6 py-4 text-right font-mono font-bold text-white">${formatCurrency(data.ending_capital)}</td>
                    <td class="px-6 py-4 text-right font-mono font-bold text-white">${formatCurrency(data.ending_retained)}</td>
                    <td class="px-6 py-4 text-right font-mono font-bold text-primary text-lg">${formatCurrency(data.ending_equity)}</td>
                </tr>
            `;

            tbody.innerHTML = rows;
        }

        function exportPDF() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            window.location.href = `/reports/equity-changes/export-pdf?start_date=${startDate}&end_date=${endDate}`;
        }

        function initializeDates() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), 0, 1); // Start of year

            document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
        }

        initializeDates();
        loadReport();
    </script>
    @endpush
</x-app-layout>
