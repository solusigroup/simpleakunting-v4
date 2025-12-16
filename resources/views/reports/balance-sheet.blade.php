<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Neraca</h2>
                <p class="text-text-muted text-sm mt-1">Laporan Posisi Keuangan</p>
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
                        <span class="text-text-muted text-sm">Per Tanggal:</span>
                        <input type="date" id="endDate" class="bg-transparent border-0 text-white text-sm focus:ring-0">
                    </div>
                    @if(auth()->user()->company?->isBumdesa())
                    <select id="unitFilter" class="px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30 text-white text-sm focus:ring-primary">
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
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 rounded-xl border border-border-dark bg-surface-dark shadow-xl z-10">
                        <a href="#" @click.prevent="exportPDF(); exportOpen = false" 
                           class="flex items-center gap-3 px-4 py-3 hover:bg-surface-highlight rounded-t-xl transition">
                            <span class="material-symbols-outlined text-accent-red">picture_as_pdf</span>
                            <span class="text-white">Export PDF</span>
                        </a>
                        <a href="#" @click.prevent="printReport(); exportOpen = false" 
                           class="flex items-center gap-3 px-4 py-3 hover:bg-surface-highlight rounded-b-xl border-t border-border-dark transition">
                            <span class="material-symbols-outlined text-text-muted">print</span>
                            <span class="text-white">Print</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Balance Check -->
    <div id="balanceAlert" class="hidden mb-6 p-4 rounded-xl bg-accent-red/10 border border-accent-red/30">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-accent-red">warning</span>
            <p class="text-accent-red font-medium">Neraca tidak seimbang! Periksa kembali jurnal Anda.</p>
        </div>
    </div>

    <!-- Single Period Report Content -->
    <div id="singleReport" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Assets -->
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-surface-dark">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-400">account_balance</span>
                    ASET
                </h3>
            </div>
            <div id="assetsSection" class="divide-y divide-border-dark/50">
                <div class="p-8 text-center text-text-muted">
                    <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
                    <p class="mt-2">Memuat...</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-border-dark bg-surface-dark flex justify-between">
                <span class="font-bold text-white">Total Aset</span>
                <span class="font-bold text-white font-mono" id="totalAssets">Rp 0</span>
            </div>
        </div>

        <!-- Liabilities & Equity -->
        <div class="space-y-6">
            <!-- Liabilities -->
            <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
                <div class="px-6 py-4 border-b border-border-dark bg-surface-dark">
                    <h3 class="font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-orange-400">credit_card</span>
                        KEWAJIBAN
                    </h3>
                </div>
                <div id="liabilitiesSection" class="divide-y divide-border-dark/50">
                    <div class="p-8 text-center text-text-muted">Memuat...</div>
                </div>
                <div class="px-6 py-4 border-t border-border-dark bg-surface-dark flex justify-between">
                    <span class="font-bold text-white">Total Kewajiban</span>
                    <span class="font-bold text-white font-mono" id="totalLiabilities">Rp 0</span>
                </div>
            </div>

            <!-- Equity -->
            <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
                <div class="px-6 py-4 border-b border-border-dark bg-surface-dark">
                    <h3 class="font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-purple-400">savings</span>
                        EKUITAS
                    </h3>
                </div>
                <div id="equitySection" class="divide-y divide-border-dark/50">
                    <div class="p-8 text-center text-text-muted">Memuat...</div>
                </div>
                <div class="px-6 py-4 border-t border-border-dark bg-surface-dark flex justify-between">
                    <span class="font-bold text-white">Total Ekuitas</span>
                    <span class="font-bold text-white font-mono" id="totalEquity">Rp 0</span>
                </div>
            </div>

            <!-- Total Kewajiban + Ekuitas -->
            <div class="p-4 rounded-xl bg-primary/10 border border-primary/30 flex justify-between">
                <span class="font-bold text-primary">Total Kewajiban + Ekuitas</span>
                <span class="font-bold text-primary font-mono" id="totalLiabEquity">Rp 0</span>
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
        let comparativeData = null;

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
            
            // Toggle filter visibility
            document.getElementById('singleFilters').classList.toggle('hidden', mode !== 'single');
            document.getElementById('comparativeFilters').classList.toggle('hidden', mode !== 'comparative');
            
            // Toggle report visibility
            document.getElementById('singleReport').classList.toggle('hidden', mode !== 'single');
            document.getElementById('comparativeReport').classList.toggle('hidden', mode !== 'comparative');
            
            // Load appropriate report
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
            comparativeData = data;
            
            // Update period headers
            if (data.periods && data.periods.length >= 2) {
                document.getElementById('period1Header').textContent = data.periods[0].label || 'Periode 1';
                document.getElementById('period2Header').textContent = data.periods[1].label || 'Periode 2';
            }

            const tbody = document.getElementById('comparativeTableBody');
            let rows = '';

            // Render each section
            ['Aset', 'Kewajiban', 'Ekuitas'].forEach(section => {
                if (!data.sections[section]) return;

                // Section header
                rows += `
                    <tr class="bg-surface-dark">
                        <td colspan="5" class="px-4 py-3 font-bold text-primary uppercase tracking-wide">${section}</td>
                    </tr>
                `;

                // Section items
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

                // Section total
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
            const endDate = document.getElementById('endDate').value;
            const unitId = document.getElementById('unitFilter')?.value || '';
            
            let url = `/reports/balance-sheet?end_date=${endDate}`;
            if (unitId) url += `&unit_id=${unitId}`;

            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    
                    renderSection('assetsSection', data.sections.Aset);
                    renderSection('liabilitiesSection', data.sections.Kewajiban);
                    renderSection('equitySection', data.sections.Ekuitas);
                    
                    document.getElementById('totalAssets').textContent = formatCurrency(data.totals.Aset);
                    document.getElementById('totalLiabilities').textContent = formatCurrency(data.totals.Kewajiban);
                    document.getElementById('totalEquity').textContent = formatCurrency(data.totals.Ekuitas);
                    document.getElementById('totalLiabEquity').textContent = formatCurrency(data.totals.Kewajiban + data.totals.Ekuitas);
                    
                    document.getElementById('balanceAlert').classList.toggle('hidden', data.is_balanced);
                }
            } catch (error) {
                console.error('Error loading report:', error);
                alert('Gagal memuat laporan. Silakan coba lagi.');
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
                {
                    start_date: p1Start,
                    end_date: p1End,
                    label: `Periode 1 (${new Date(p1End).toLocaleDateString('id-ID')})`
                },
                {
                    start_date: p2Start,
                    end_date: p2End,
                    label: `Periode 2 (${new Date(p2End).toLocaleDateString('id-ID')})`
                }
            ];

            try {
                const response = await fetch('/reports/balance-sheet/comparative', {
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
                } else {
                    alert('Gagal memuat data komparatif');
                }
            } catch (error) {
                console.error('Error loading comparative report:', error);
                alert('Gagal memuat laporan komparatif. Silakan coba lagi.');
            }
        }

        function loadReport() {
            if (currentMode === 'single') {
                const endDate = document.getElementById('endDate').value;
                const unitId = document.getElementById('unitFilter')?.value || '';
                let url = `/reports/balance-sheet?end_date=${endDate}`;
                if (unitId) url += `&unit_id=${unitId}`;
                window.location.href = url;
            } else {
                loadComparativeReport();
            }
        }

        function exportPDF() {
            if (currentMode === 'single') {
                const endDate = document.getElementById('endDate').value;
                const unitId = document.getElementById('unitFilter')?.value || '';
                let url = `/reports/balance-sheet/export-pdf?end_date=${endDate}`;
                if (unitId) url += `&unit_id=${unitId}`;
                window.location.href = url;
            } else {
                // Comparative PDF export
                const p1Start = document.getElementById('period1Start').value || null;
                const p1End = document.getElementById('period1End').value;
                const p2Start = document.getElementById('period2Start').value || null;
                const p2End = document.getElementById('period2End').value;

                if (!p1End || !p2End) {
                    alert('Mohon lengkapi periode terlebih dahulu');
                    return;
                }

                const periods = [
                    {start_date: p1Start, end_date: p1End, label: 'Periode 1'},
                    {start_date: p2Start, end_date: p2End, label: 'Periode 2'}
                ];

                const periodsParam = encodeURIComponent(JSON.stringify(periods));
                window.location.href = `/reports/balance-sheet/comparative/export-pdf?periods=${periodsParam}`;
            }
        }

        function printReport() {
            window.print();
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

        // Initialize dates
        function initializeDates() {
            const today = new Date().toISOString().split('T')[0];
            const lastMonth = new Date();
            lastMonth.setMonth(lastMonth.getMonth() - 1);
            const lastMonthISO = lastMonth.toISOString().split('T')[0];

            // Single period
            document.getElementById('endDate').value = today;

            // Comparative periods (last month vs this month)
            const thisMonthStart = new Date();
            thisMonthStart.setDate(1);
            const lastMonthStart = new Date(lastMonth);
            lastMonthStart.setDate(1);

            document.getElementById('period1Start').value = lastMonthStart.toISOString().split('T')[0];
            document.getElementById('period1End').value = lastMonthISO;
            document.getElementById('period2Start').value = thisMonthStart.toISOString().split('T')[0];
            document.getElementById('period2End').value = today;
        }

        // Initialize data from server on page load
        function initializeWithServerData() {
            const serverData = @json([
                'sections' => $sections ?? [],
                'totals' => $totals ?? ['Aset' => 0, 'Kewajiban' => 0, 'Ekuitas' => 0],
                'is_balanced' => $is_balanced ?? true
            ]);

            // Render sections
            renderSection('assetsSection', serverData.sections.Aset || []);
            renderSection('liabilitiesSection', serverData.sections.Kewajiban || []);
            renderSection('equitySection', serverData.sections.Ekuitas || []);

            // Update totals
            document.getElementById('totalAssets').textContent = formatCurrency(serverData.totals.Aset || 0);
            document.getElementById('totalLiabilities').textContent = formatCurrency(serverData.totals.Kewajiban || 0);
            document.getElementById('totalEquity').textContent = formatCurrency(serverData.totals.Ekuitas || 0);
            document.getElementById('totalLiabEquity').textContent = formatCurrency((serverData.totals.Kewajiban || 0) + (serverData.totals.Ekuitas || 0));

            // Balance alert
            document.getElementById('balanceAlert').classList.toggle('hidden', serverData.is_balanced);
        }

        // Init
        initializeDates();
        loadUnits();
        initializeWithServerData(); // Render data from server immediately
    </script>
    @endpush
</x-app-layout>
