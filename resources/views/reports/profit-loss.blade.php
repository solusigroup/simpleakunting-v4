<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Laba Rugi</h2>
                <p class="text-text-muted text-sm mt-1">Laporan Aktivitas Usaha</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
                    <span class="text-text-muted text-sm">Periode:</span>
                    <input type="date" id="startDate" class="bg-transparent border-0 text-white text-sm focus:ring-0 w-32">
                    <span class="text-text-muted">-</span>
                    <input type="date" id="endDate" class="bg-transparent border-0 text-white text-sm focus:ring-0 w-32">
                </div>
                @if(auth()->user()->company?->isBumdesa())
                <select id="unitFilter" class="px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30 text-white text-sm">
                    <option value="">Konsolidasi (Semua Unit)</option>
                </select>
                @endif
                <button onclick="loadReport()" class="px-6 py-2 rounded-full bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition">
                    <span class="material-symbols-outlined align-middle mr-1">refresh</span>
                    Muat
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">trending_up</span>
                </div>
                <span class="text-text-muted text-sm uppercase tracking-wider">Total Pendapatan</span>
            </div>
            <p class="text-2xl font-bold text-white font-mono" id="totalRevenueCard">Rp 0</p>
        </div>
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-accent-red/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-accent-red">trending_down</span>
                </div>
                <span class="text-text-muted text-sm uppercase tracking-wider">Total Beban</span>
            </div>
            <p class="text-2xl font-bold text-white font-mono" id="totalExpenseCard">Rp 0</p>
        </div>
        <div class="p-6 rounded-2xl border border-primary/30 bg-primary/10">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-primary/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
                </div>
                <span class="text-primary text-sm uppercase tracking-wider font-bold">Laba/Rugi Bersih</span>
            </div>
            <p class="text-2xl font-bold font-mono" id="netProfitCard">Rp 0</p>
        </div>
    </div>

    <!-- Report Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue -->
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-surface-dark">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">payments</span>
                    PENDAPATAN
                </h3>
            </div>
            <div id="revenueSection" class="divide-y divide-border-dark/50">
                <div class="p-8 text-center text-text-muted">
                    <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-border-dark bg-surface-dark flex justify-between">
                <span class="font-bold text-white">Total Pendapatan</span>
                <span class="font-bold text-primary font-mono" id="totalRevenue">Rp 0</span>
            </div>
        </div>

        <!-- Expenses -->
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-surface-dark">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-accent-red">receipt</span>
                    BEBAN
                </h3>
            </div>
            <div id="expenseSection" class="divide-y divide-border-dark/50">
                <div class="p-8 text-center text-text-muted">Memuat...</div>
            </div>
            <div class="px-6 py-4 border-t border-border-dark bg-surface-dark flex justify-between">
                <span class="font-bold text-white">Total Beban</span>
                <span class="font-bold text-accent-red font-mono" id="totalExpense">Rp 0</span>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function formatCurrency(value) {
            return 'Rp ' + Math.abs(value).toLocaleString('id-ID');
        }

        function renderSection(sectionId, items) {
            const section = document.getElementById(sectionId);
            if (!items || items.length === 0) {
                section.innerHTML = '<div class="px-6 py-4 text-text-muted text-sm">Tidak ada data</div>';
                return;
            }
            section.innerHTML = items.map(item => `
                <div class="px-6 py-3 flex justify-between hover:bg-surface-highlight/30">
                    <span class="text-text-muted">${item.account_code} - ${item.account_name}</span>
                    <span class="text-white font-mono">${formatCurrency(item.balance)}</span>
                </div>
            `).join('');
        }

        async function loadReport() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const unitId = document.getElementById('unitFilter')?.value || '';
            
            let url = `/reports/profit-loss?start_date=${startDate}&end_date=${endDate}`;
            if (unitId) url += `&unit_id=${unitId}`;

            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                const data = result.data;
                
                renderSection('revenueSection', data.sections.Pendapatan);
                renderSection('expenseSection', data.sections.Beban);
                
                document.getElementById('totalRevenue').textContent = formatCurrency(data.total_revenue);
                document.getElementById('totalExpense').textContent = formatCurrency(data.total_expense);
                document.getElementById('totalRevenueCard').textContent = formatCurrency(data.total_revenue);
                document.getElementById('totalExpenseCard').textContent = formatCurrency(data.total_expense);
                
                const netProfit = data.net_profit;
                const netProfitEl = document.getElementById('netProfitCard');
                netProfitEl.textContent = (netProfit >= 0 ? '' : '-') + formatCurrency(netProfit);
                netProfitEl.className = `text-2xl font-bold font-mono ${netProfit >= 0 ? 'text-primary' : 'text-accent-red'}`;
            }
        }

        async function loadUnits() {
            const unitFilter = document.getElementById('unitFilter');
            if (!unitFilter) return;
            
            const response = await fetch('/units', { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            if (result.success && result.data) {
                result.data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.name;
                    unitFilter.appendChild(option);
                });
            }
        }

        // Init - set to current month
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
        document.getElementById('endDate').value = today.toISOString().split('T')[0];
        loadUnits();
        loadReport();
    </script>
    @endpush
</x-app-layout>
