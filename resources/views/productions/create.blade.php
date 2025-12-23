<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Buat Produksi Baru</h2>
                <p class="text-text-muted text-sm mt-1">Buat order produksi untuk assembly</p>
            </div>
            <x-btn type="secondary" onclick="window.location.href='{{ route('productions.index') }}'">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
                Kembali
            </x-btn>
        </div>
    </x-slot>

    @if(count($missingCOAs) > 0)
    <div class="mb-6 p-4 rounded-xl bg-yellow-500/20 border border-yellow-500/50 text-yellow-400">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined">warning</span>
            <div>
                <p class="font-bold">Setup COA Diperlukan</p>
                <p class="text-sm">Modul manufaktur membutuhkan Chart of Accounts berikut:</p>
                <ul class="list-disc ml-4 mt-2 text-sm">
                    @foreach($missingCOAs as $coa)
                    <li>{{ $coa }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('accounts.index') }}" class="text-sm underline mt-2 inline-block">Buat COA &rarr;</a>
            </div>
        </div>
    </div>
    @endif

    @if($assemblies->isEmpty())
    <div class="py-12 text-center text-text-muted">
        <span class="material-symbols-outlined text-5xl mb-3">precision_manufacturing</span>
        <p>Belum ada item assembly dengan BOM</p>
        <p class="text-sm mt-1">Buat assembly dan definisikan komponen terlebih dahulu</p>
        <x-btn type="primary" class="mt-4" onclick="window.location.href='{{ route('assemblies.index') }}'">
            Kelola BOM
        </x-btn>
    </div>
    @else

    <form id="productionForm" class="space-y-6">
        <!-- Production Details -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <h3 class="text-lg font-bold text-white mb-4">Detail Produksi</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Tanggal Produksi *</label>
                    <input type="date" id="production_date" required value="{{ date('Y-m-d') }}"
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Pilih Assembly *</label>
                    <select id="assembly_id" required onchange="loadBOM()"
                            class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        <option value="">-- Pilih Assembly --</option>
                        @foreach($assemblies as $assembly)
                        <option value="{{ $assembly->id }}" 
                                data-unit="{{ $assembly->unit }}"
                                data-components="{{ json_encode($assembly->components->map(fn($c) => ['id' => $c->component_id, 'name' => $c->component->name, 'qty' => $c->quantity, 'unit' => $c->unit, 'cost' => $c->component->cost, 'stock' => $c->component->stock])) }}">
                            {{ $assembly->name }} ({{ $assembly->components->count() }} komponen)
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Kuantitas Produksi *</label>
                    <div class="flex gap-2">
                        <input type="number" id="quantity" required min="1" step="1" value="1" onchange="calculateComponents()"
                               class="flex-1 px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        <input type="text" id="unit" readonly
                               class="w-20 px-3 py-3 rounded-xl bg-surface-dark border border-border-dark text-text-muted text-center">
                    </div>
                </div>
            </div>
        </div>

        <!-- Components -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30" id="componentsSection" style="display: none;">
            <h3 class="text-lg font-bold text-white mb-4">Komponen yang Dibutuhkan</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full" id="componentsTable">
                    <thead class="border-b border-border-dark">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-white">Komponen</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-white">Dibutuhkan</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-white">Aktual Digunakan</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-white">Biaya/Unit</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-white">Total Biaya</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-white">Stok</th>
                        </tr>
                    </thead>
                    <tbody id="componentsBody"></tbody>
                    <tfoot class="border-t-2 border-primary">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-white font-bold">Total Biaya Material</td>
                            <td class="px-4 py-3 text-right text-primary font-bold" id="totalMaterialCost">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Costs -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30" id="costsSection" style="display: none;">
            <h3 class="text-lg font-bold text-white mb-4">Biaya Tambahan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Biaya Tenaga Kerja</label>
                    <input type="number" id="labor_cost" min="0" step="1000" value="0" onchange="calculateTotal()"
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    <select id="labor_coa_id" class="w-full mt-2 px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white text-sm focus:border-primary focus:ring-primary">
                        <option value="">-- Pilih COA Tenaga Kerja --</option>
                        @foreach($expenseAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Biaya Overhead</label>
                    <input type="number" id="overhead_cost" min="0" step="1000" value="0" onchange="calculateTotal()"
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    <select id="overhead_coa_id" class="w-full mt-2 px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white text-sm focus:border-primary focus:ring-primary">
                        <option value="">-- Pilih COA Overhead --</option>
                        @foreach($expenseAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-text-muted mb-2 mt-4">Catatan</label>
                <textarea id="notes" rows="2"
                          class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary resize-none"></textarea>
            </div>
        </div>

        <!-- Summary -->
        <div class="p-6 rounded-2xl border border-primary bg-primary/10" id="summarySection" style="display: none;">
            <h3 class="text-lg font-bold text-white mb-4">Ringkasan Produksi</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-text-muted text-sm">Biaya Material</p>
                    <p class="text-xl font-bold text-white" id="summaryMaterial">Rp 0</p>
                </div>
                <div>
                    <p class="text-text-muted text-sm">Biaya Tenaga Kerja</p>
                    <p class="text-xl font-bold text-white" id="summaryLabor">Rp 0</p>
                </div>
                <div>
                    <p class="text-text-muted text-sm">Biaya Overhead</p>
                    <p class="text-xl font-bold text-white" id="summaryOverhead">Rp 0</p>
                </div>
                <div>
                    <p class="text-text-muted text-sm">Total Biaya</p>
                    <p class="text-xl font-bold text-primary" id="summaryTotal">Rp 0</p>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-border-dark">
                <p class="text-text-muted text-sm">Biaya per Unit</p>
                <p class="text-2xl font-bold text-green-400" id="summaryUnitCost">Rp 0</p>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3" id="submitSection" style="display: none;">
            <x-btn type="secondary" onclick="window.location.href='{{ route('productions.index') }}'">Batal</x-btn>
            <x-btn type="primary" onclick="submitProduction()">
                <span class="material-symbols-outlined text-xl">save</span>
                Simpan Produksi
            </x-btn>
        </div>
    </form>
    @endif

    @push('scripts')
    <script>
        let components = [];
        let materialCost = 0;

        function loadBOM() {
            const select = document.getElementById('assembly_id');
            const option = select.options[select.selectedIndex];
            
            if (!option.value) {
                document.getElementById('componentsSection').style.display = 'none';
                document.getElementById('costsSection').style.display = 'none';
                document.getElementById('summarySection').style.display = 'none';
                document.getElementById('submitSection').style.display = 'none';
                return;
            }

            document.getElementById('unit').value = option.dataset.unit;
            components = JSON.parse(option.dataset.components);
            
            document.getElementById('componentsSection').style.display = 'block';
            document.getElementById('costsSection').style.display = 'block';
            document.getElementById('summarySection').style.display = 'block';
            document.getElementById('submitSection').style.display = 'flex';
            
            calculateComponents();
        }

        function calculateComponents() {
            const qty = parseFloat(document.getElementById('quantity').value) || 1;
            const tbody = document.getElementById('componentsBody');
            tbody.innerHTML = '';
            materialCost = 0;

            components.forEach((comp, idx) => {
                const required = comp.qty * qty;
                const totalCost = required * comp.cost;
                const sufficient = comp.stock >= required;
                materialCost += totalCost;

                tbody.innerHTML += `
                    <tr class="border-b border-border-dark/50">
                        <td class="px-4 py-3 text-white">${comp.name}</td>
                        <td class="px-4 py-3 text-right text-text-muted">${required.toFixed(2)} ${comp.unit}</td>
                        <td class="px-4 py-3 text-right">
                            <input type="number" name="components[${idx}][quantity_used]" value="${required.toFixed(2)}" 
                                   min="0" step="0.01" onchange="recalculateCosts()"
                                   class="w-24 px-2 py-1 rounded bg-surface-dark border border-border-dark text-white text-right">
                            <input type="hidden" name="components[${idx}][component_id]" value="${comp.id}">
                        </td>
                        <td class="px-4 py-3 text-right text-text-muted">Rp ${formatNumber(comp.cost)}</td>
                        <td class="px-4 py-3 text-right text-primary component-cost" data-cost="${comp.cost}">Rp ${formatNumber(totalCost)}</td>
                        <td class="px-4 py-3 text-right ${sufficient ? 'text-green-400' : 'text-red-400'}">${comp.stock} ${sufficient ? '' : '⚠️'}</td>
                    </tr>
                `;
            });

            calculateTotal();
        }

        function recalculateCosts() {
            materialCost = 0;
            document.querySelectorAll('#componentsBody tr').forEach((row, idx) => {
                const input = row.querySelector('input[type="number"]');
                const costCell = row.querySelector('.component-cost');
                const usedQty = parseFloat(input.value) || 0;
                const unitCost = parseFloat(costCell.dataset.cost);
                const total = usedQty * unitCost;
                costCell.textContent = 'Rp ' + formatNumber(total);
                materialCost += total;
            });
            calculateTotal();
        }

        function calculateTotal() {
            const laborCost = parseFloat(document.getElementById('labor_cost').value) || 0;
            const overheadCost = parseFloat(document.getElementById('overhead_cost').value) || 0;
            const totalCost = materialCost + laborCost + overheadCost;
            const qty = parseFloat(document.getElementById('quantity').value) || 1;
            const unitCost = totalCost / qty;

            document.getElementById('totalMaterialCost').textContent = 'Rp ' + formatNumber(materialCost);
            document.getElementById('summaryMaterial').textContent = 'Rp ' + formatNumber(materialCost);
            document.getElementById('summaryLabor').textContent = 'Rp ' + formatNumber(laborCost);
            document.getElementById('summaryOverhead').textContent = 'Rp ' + formatNumber(overheadCost);
            document.getElementById('summaryTotal').textContent = 'Rp ' + formatNumber(totalCost);
            document.getElementById('summaryUnitCost').textContent = 'Rp ' + formatNumber(unitCost);
        }

        function formatNumber(num) {
            return Math.round(num).toLocaleString('id-ID');
        }

        async function submitProduction() {
            const assemblyId = document.getElementById('assembly_id').value;
            if (!assemblyId) {
                alert('Pilih assembly terlebih dahulu');
                return;
            }

            const laborCost = parseFloat(document.getElementById('labor_cost').value) || 0;
            const overheadCost = parseFloat(document.getElementById('overhead_cost').value) || 0;

            if (laborCost > 0 && !document.getElementById('labor_coa_id').value) {
                alert('Pilih COA untuk biaya tenaga kerja');
                return;
            }

            if (overheadCost > 0 && !document.getElementById('overhead_coa_id').value) {
                alert('Pilih COA untuk biaya overhead');
                return;
            }

            const componentsData = [];
            document.querySelectorAll('#componentsBody tr').forEach((row, idx) => {
                const input = row.querySelector('input[type="number"]');
                const hiddenInput = row.querySelector('input[type="hidden"]');
                componentsData.push({
                    component_id: parseInt(hiddenInput.value),
                    quantity_used: parseFloat(input.value)
                });
            });

            const data = {
                production_date: document.getElementById('production_date').value,
                assembly_id: parseInt(assemblyId),
                quantity: parseFloat(document.getElementById('quantity').value),
                labor_cost: laborCost,
                labor_coa_id: document.getElementById('labor_coa_id').value || null,
                overhead_cost: overheadCost,
                overhead_coa_id: document.getElementById('overhead_coa_id').value || null,
                notes: document.getElementById('notes').value,
                components: componentsData
            };

            try {
                const response = await fetch('{{ route("productions.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    window.location.href = '{{ route("productions.index") }}';
                } else {
                    alert(result.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan');
            }
        }
    </script>
    @endpush
</x-app-layout>
