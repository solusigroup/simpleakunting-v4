<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Neraca</h2>
                <p class="text-text-muted text-sm mt-1">Laporan Posisi Keuangan</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
                    <span class="text-text-muted text-sm">Per Tanggal:</span>
                    <input type="date" id="endDate" class="bg-transparent border-0 text-white text-sm focus:ring-0">
                </div>
                @if(auth()->user()->company?->isBumdesa())
                <select id="unitFilter" class="px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30 text-white text-sm focus:ring-primary">
                    <option value="">Konsolidasi (Semua Unit)</option>
                </select>
                @endif
                <button onclick="loadReport()" class="px-6 py-2 rounded-full bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition">
                    <span class="material-symbols-outlined align-middle mr-1">refresh</span>
                    Muat Ulang
                </button>
                <button onclick="printReport()" class="px-4 py-2 rounded-full border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition">
                    <span class="material-symbols-outlined">print</span>
                </button>
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

    <!-- Report Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="reportContent">
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
            const endDate = document.getElementById('endDate').value;
            const unitId = document.getElementById('unitFilter')?.value || '';
            
            let url = `/reports/balance-sheet?end_date=${endDate}`;
            if (unitId) url += `&unit_id=${unitId}`;

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

        function printReport() {
            window.print();
        }

        // Init
        document.getElementById('endDate').value = new Date().toISOString().split('T')[0];
        loadUnits();
        loadReport();
    </script>
    @endpush
</x-app-layout>
