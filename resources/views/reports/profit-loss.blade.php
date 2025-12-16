<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Laba Rugi</h2>
                <p class="text-text-muted text-sm mt-1">Laporan Aktivitas Usaha</p>
            </div>
            <div class="flex items-center gap-3" x-data="{ exportOpen: false, currentMode: 'single' }">
                <!-- Mode Toggle -->
                <div class="flex items-center gap-2 px-2 py-1 rounded-full border border-border-dark bg-surface-dark/30">
                    <button @click="currentMode = 'single'; setMode('single')" 
                            :class="currentMode === 'single' ? 'bg-primary text-background-dark' : 'text-text-muted hover:text-white'"
                            class="px-4 py-1.5 rounded-full text-sm font-medium transition">
                        Single
                    </button>
                    <button @click="currentMode = 'comparative'; setMode('comparative')" 
                            :class="currentMode === 'comparative' ? 'bg-primary text-background-dark' : 'text-text-muted hover:text-white'"
                            class="px-4 py-1.5 rounded-full text-sm font-medium transition">
                        Komparatif
                    </button>
                </div>

                <!-- Single Period Filters -->
                <div id="singleFilters" class="flex items-center gap-3">
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
                </div>

                <!-- Comparative Period Filters -->
                <div id="comparativeFilters" class="hidden flex items-center gap-3">
                    <div class="flex items-center gap-2 px-3 py-2 rounded-full border border-border-dark bg-surface-dark/30">
                        <span class="text-text-muted text-xs">Periode 1:</span>
                        <input type="date" id="period1Start" class="bg-transparent border-0 text-white text-xs w-28 focus:ring-0">
                        <span class="text-text-muted">-</span>
                        <input type="date" id="period1End" class="bg-transparent border-0 text-white text-xs w-28 focus:ring-0">
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 rounded-full border border-border-dark bg-surface-dark/30">
                        <span class="text-text-muted text-xs">Periode 2:</span>
                        <input type="date" id="period2Start" class="bg-transparent border-0 text-white text-xs w-28 focus:ring-0">
                        <span class="text-text-muted">-</span>
                        <input type="date" id="period2End" class="bg-transparent border-0 text-white text-xs w-28 focus:ring-0">
                    </div>
                </div>

                <!-- Action Buttons -->
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
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:leave="transition ease-in duration-150"
                         class="absolute right-0 mt-2 w-48 rounded-xl border border-border-dark bg-surface-dark shadow-xl z-10">
                        <a href="#" @click.prevent="exportPDF(); exportOpen = false" 
                           class="flex items-center gap-3 px-4 py-3 hover:bg-surface-highlight rounded-t-xl transition">
                            <span class="material-symbols-outlined text-accent-red">picture_as_pdf</span>
                            <span class="text-white">Export PDF</span>
                        </a>
                        <a href="#" @click.prevent="window.print(); exportOpen = false" 
                           class="flex items-center gap-3 px-4 py-3 hover:bg-surface-highlight rounded-b-xl border-t border-border-dark transition">
                            <span class="material-symbols-outlined text-text-muted">print</span>
                            <span class="text-white">Print</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Single Period View -->
    <div id="singleReport">
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
    </div>

    <!-- Comparative Report Content -->
    <div id="comparativeReport" class="hidden">
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="comparativeTable">
                    <thead class="bg-surface-dark border-b border-border-dark">
                        <tr>
                            <th class="px-4 py-3 text-left text-white font-bold">Nama Akun</th>
                            <th class="px-4 py-3 text-right text-white font-bold" id="period1Header">Periode 1</th>
                            <th class="px-4 py-3 text-right text-white font-bold" id="period2Header">Periode 2</th>
                            <th class="px-4 py-3 text-right text-white font-bold">Selisih</th>
                            <th class="px-4 py-3 text-right text-white font-bold">Perubahan (%)</th>
                        </tr>
                    </thead>
                    <tbody id="comparativeTableBody" class="divide-y divide-border-dark/50">
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-text-muted">
                                <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
                                <p class="mt-2">Memuat data komparatif...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentMode = 'single';

        function formatCurrency(value) {
            return 'Rp ' + Math.abs(value).toLocaleString('id-ID');
        }

        function formatPercentage(value) {
            const sign = value >= 0 ? '+' : '';
            return sign + value.toFixed(2) + '%';
        }

        function getTrendIcon(percentage) {
            if (percentage > 5) return '↑';
            if (percentage < -5) return '↓';
            return '→';
        }

        function getTrendClass(trend) {
            if (trend === 'increase') return 'text-primary';
            if (trend === 'decrease') return 'text-accent-red';
            return 'text-text-muted';
        }

        function setMode(mode) {
            currentMode = mode;
            
            document.getElementById('singleFilters').classList.toggle('hidden', mode !== 'single');
            document.getElementById('comparativeFilters').classList.toggle('hidden', mode !== 'comparative');
            document.getElementById('singleReport').classList.toggle('hidden', mode !== 'single');
            document.getElementById('comparativeReport').classList.toggle('hidden', mode !== 'comparative');
            
            loadReport();
        }

        function renderSection(sectionId, items) {
            const section = document.getElementById(sectionId);
            if (!items || items.length === 0) {
                section.innerHTML = '<div class="px-6 py-4 text-text-muted text-sm">Tidak ada data</div>';
                return;
            }
            section.innerHTML = items.map(item => `
                <div class="px-6 py-3 flex justify-between hover:bg-surface-highlight/30 transition">
                    <span class="text-text-muted">${item.account_code} - ${item.account_name}</span>
                    <span class="text-white font-mono">${formatCurrency(item.balance)}</span>
                </div>
            `).join('');
        }

        function renderComparativeTable(data) {
            if (data.periods && data.periods.length >= 2) {
                document.getElementById('period1Header').textContent = data.periods[0].label || 'Periode 1';
                document.getElementById('period2Header').textContent = data.periods[1].label || 'Periode 2';
            }

            const tbody = document.getElementById('comparativeTableBody');
            let rows = '';

            ['Pendapatan', 'Beban'].forEach(section => {
                if (!data.sections[section]) return;

                rows += `
                    <tr class="bg-surface-dark">
                        <td colspan="5" class="px-4 py-3 font-bold text-primary uppercase tracking-wide">${section}</td>
                    </tr>
                `;

                data.sections[section].forEach(item => {
                    const trendClass = getTrendClass(item.variance?.trend || 'stable');
                    const trendIcon = getTrendIcon(item.variance?.percentage || 0);
                    
                    rows += `
                        <tr class="hover:bg-surface-highlight/30 transition">
                            <td class="px-4 py-3 text-text-muted">${item.account_name}</td>
                            <td class="px-4 py-3 text-right text-white font-mono">${formatCurrency(item.values[0])}</td>
                            <td class="px-4 py-3 text-right text-white font-mono">${formatCurrency(item.values[1])}</td>
                            <td class="px-4 py-3 text-right font-mono ${trendClass}">
                                ${formatCurrency(item.variance?.absolute || 0)} ${trendIcon}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold ${trendClass}">
                                ${formatPercentage(item.variance?.percentage || 0)}
                            </td>
                        </tr>
                    `;
                });

                if (data.totals && data.totals[section]) {
                    const totalVariance = data.totals_variance?.[section];
                    const trendClass = getTrendClass(totalVariance?.trend || 'stable');
                    const trendIcon = getTrendIcon(totalVariance?.percentage || 0);
                    
                    rows += `
                        <tr class="bg-primary/10 border-t-2 border-primary/30">
                            <td class="px-4 py-3 font-bold text-white">Total ${section}</td>
                            <td class="px-4 py-3 text-right font-bold text-white font-mono">${formatCurrency(data.totals[section][0])}</td>
                            <td class="px-4 py-3 text-right font-bold text-white font-mono">${formatCurrency(data.totals[section][1])}</td>
                            <td class="px-4 py-3 text-right font-bold font-mono ${trendClass}">
                                ${formatCurrency(totalVariance?.absolute || 0)} ${trendIcon}
                            </td>
                            <td class="px-4 py-3 text-right font-bold font-mono ${trendClass}">
                                ${formatPercentage(totalVariance?.percentage || 0)}
                            </td>
                        </tr>
                    `;
                }
            });

            tbody.innerHTML = rows;
        }

        async function loadSinglePeriodReport() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const unitId = document.getElementById('unitFilter')?.value || '';
            
            let url = `/reports/profit-loss?start_date=${startDate}&end_date=${endDate}`;
            if (unitId) url += `&unit_id=${unitId}`;

            try {
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
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
            } catch (error) {
                console.error('Error loading report:', error);
            }
        }

        async function loadComparativeReport() {
            const p1Start = document.getElementById('period1Start').value || null;
            const p1End = document.getElementById('period1End').value;
            const p2Start = document.getElementById('period2Start').value || null;
            const p2End = document.getElementById('period2End').value;

            if (!p1End || !p2End) {
                alert('Mohon lengkapi tanggal periode 1 dan periode 2');
                return;
            }

            const periods = [
                { start_date: p1Start, end_date: p1End, label: `Periode 1 (${new Date(p1End).toLocaleDateString('id-ID')})` },
                { start_date: p2Start, end_date: p2End, label: `Periode 2 (${new Date(p2End).toLocaleDateString('id-ID')})` }
            ];

            try {
                const response = await fetch('/reports/profit-loss/comparative', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ periods })
                });

                const result = await response.json();
                if (result.success) {
                    renderComparativeTable(result.data);
                }
            } catch (error) {
                console.error('Error loading comparative report:', error);
            }
        }

        function loadReport() {
            if (currentMode === 'single') {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const unitId = document.getElementById('unitFilter')?.value || '';
                let url = `/reports/profit-loss?start_date=${startDate}&end_date=${endDate}`;
                if (unitId) url += `&unit_id=${unitId}`;
                window.location.href = url;
            } else {
                loadComparativeReport();
            }
        }

        function exportPDF() {
            if (currentMode === 'single') {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const unitId = document.getElementById('unitFilter')?.value || '';
                let url = `/reports/profit-loss/export-pdf?start_date=${startDate}&end_date=${endDate}`;
                if (unitId) url += `&unit_id=${unitId}`;
                window.location.href = url;
            } else {
                const periods = [
                    {
                        start_date: document.getElementById('period1Start').value,
                        end_date: document.getElementById('period1End').value,
                        label: 'Periode 1'
                    },
                    {
                        start_date: document.getElementById('period2Start').value,
                        end_date: document.getElementById('period2End').value,
                        label: 'Periode 2'
                    }
                ];
                const periodsParam = encodeURIComponent(JSON.stringify(periods));
                window.location.href = `/reports/profit-loss/comparative/export-pdf?periods=${periodsParam}`;
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

        function initializeDates() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastMonth = new Date(today);
            lastMonth.setMonth(lastMonth.getMonth() - 1);

            document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
            document.getElementById('endDate').value = today.toISOString().split('T')[0];

            const thisMonthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastMonthStart = new Date(lastMonth.getFullYear(), lastMonth.getMonth(), 1);
            const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);

            document.getElementById('period1Start').value = lastMonthStart.toISOString().split('T')[0];
            document.getElementById('period1End').value = lastMonthEnd.toISOString().split('T')[0];
            document.getElementById('period2Start').value = thisMonthStart.toISOString().split('T')[0];
            document.getElementById('period2End').value = today.toISOString().split('T')[0];
        }

        initializeDates();
        loadUnits();
        loadReport();
    </script>
    @endpush
</x-app-layout>
