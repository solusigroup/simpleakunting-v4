<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('purchases.index') }}" class="w-10 h-10 rounded-xl bg-surface-dark border border-border-dark flex items-center justify-center text-text-muted hover:text-white hover:border-primary transition">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white font-display">Tagihan Pembelian Baru</h2>
                    <p class="text-text-muted text-sm mt-1">Catat tagihan dari supplier</p>
                </div>
            </div>
        </div>
    </x-slot>

    <form id="purchaseForm" class="space-y-6">
        <!-- Supplier & Date -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="font-bold text-white mb-4">Informasi Tagihan</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Supplier *</label>
                        <select id="contact_id" required class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Supplier...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Unit Usaha</label>
                        <select id="unit_id" class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Unit Usaha...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Akun Utang/Kas *</label>
                        <select id="payable_account_id" required class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Akun...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Tanggal *</label>
                        <input type="date" id="date" required class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Jatuh Tempo</label>
                        <input type="date" id="due_date" class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-text-muted mb-2">Catatan</label>
                    <textarea id="notes" rows="2" placeholder="Catatan tambahan..." class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary resize-none"></textarea>
                </div>
            </div>

            <!-- Summary -->
            <div class="p-6 rounded-2xl border border-orange-500/30 bg-orange-500/10">
                <h3 class="font-bold text-orange-400 mb-4">Ringkasan</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-text-muted">Subtotal</span>
                        <span class="text-white font-mono" id="subtotal">Rp 0</span>
                    </div>
                    <hr class="border-border-dark">
                    <div class="flex justify-between">
                        <span class="text-orange-400 font-bold">Total Tagihan</span>
                        <span class="text-orange-400 font-bold font-mono text-xl" id="total">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-white">Item Pembelian</h3>
                <button type="button" onclick="addItem()" class="px-4 py-2 rounded-full bg-orange-500 text-white font-bold hover:bg-orange-600 transition text-sm">
                    <span class="material-symbols-outlined align-middle mr-1 text-xl">add</span>
                    Tambah Item
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-dark">
                            <th class="p-3 text-left text-xs font-bold text-text-muted uppercase w-48">Barang</th>
                            <th class="p-3 text-left text-xs font-bold text-text-muted uppercase">Akun Biaya/Aset</th>
                            <th class="p-3 text-left text-xs font-bold text-text-muted uppercase">Deskripsi</th>
                            <th class="p-3 text-right text-xs font-bold text-text-muted uppercase w-20">Qty</th>
                            <th class="p-3 text-right text-xs font-bold text-text-muted uppercase w-32">Harga</th>
                            <th class="p-3 text-right text-xs font-bold text-text-muted uppercase w-36">Jumlah</th>
                            <th class="p-3 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <!-- Items will be added here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('purchases.index') }}" class="px-6 py-3 rounded-full border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition">
                Batal
            </a>
            <button type="submit" id="submitBtn" class="px-8 py-3 rounded-full bg-orange-500 text-white font-bold hover:bg-orange-600 transition">
                <span class="material-symbols-outlined align-middle mr-1">save</span>
                Simpan Tagihan
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        let suppliers = [];
        let accounts = [];
        let expenseAccounts = [];
        let businessUnits = [];
        let inventoryItems = [];
        let itemCount = 0;

        async function loadData() {
            // Load suppliers
            const suppliersRes = await fetch('/contacts?type=SUPPLIER', { headers: { 'Accept': 'application/json' } });
            const suppliersData = await suppliersRes.json();
            suppliers = suppliersData.data || [];
            
            const supplierSelect = document.getElementById('contact_id');
            suppliers.forEach(s => {
                supplierSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });

            // Load business units
            try {
                const unitsRes = await fetch('/units', { headers: { 'Accept': 'application/json' } });
                const unitsData = await unitsRes.json();
                if (unitsData.success) {
                    businessUnits = unitsData.data || [];
                    const unitSelect = document.getElementById('unit_id');
                    businessUnits.forEach(u => {
                        unitSelect.innerHTML += `<option value="${u.id}">${u.code} - ${u.name}</option>`;
                    });
                }
            } catch (error) {
                console.log('Business units not available:', error);
            }

            // Load accounts
            const accountsRes = await fetch('/accounts?detail_only=1', { headers: { 'Accept': 'application/json' } });
            const accountsData = await accountsRes.json();
            accounts = accountsData.data || [];
            
            // Liability accounts for payable
            const payableSelect = document.getElementById('payable_account_id');
            accounts.filter(a => a.type === 'Liability' || a.type === 'Asset').forEach(a => {
                payableSelect.innerHTML += `<option value="${a.id}">${a.code} - ${a.name}</option>`;
            });
            
            // Expense and Asset accounts for items
            expenseAccounts = accounts.filter(a => a.type === 'Expense' || a.type === 'Asset');

            // Load inventory items (raw materials, supplies, finished goods for trading)
            try {
                const inventoryRes = await fetch('/inventory', { headers: { 'Accept': 'application/json' } });
                const inventoryData = await inventoryRes.json();
                if (inventoryData.success) {
                    inventoryItems = inventoryData.data || [];
                }
            } catch (error) {
                console.log('Inventory not available:', error);
            }
            
            // Initialize searchable select for payable account
            if (typeof makeSearchable === 'function') {
                makeSearchable(payableSelect);
            }
        }

        function getInventoryOptions() {
            let options = '<option value="">-- Manual / Jasa --</option>';
            const categories = {
                'raw_materials': 'Bahan Baku',
                'supplies': 'Bahan Pembantu',
                'finished_goods': 'Barang Jadi/Dagangan',
                'work_in_process': 'Barang Dalam Proses'
            };
            
            // Group by category
            Object.entries(categories).forEach(([cat, label]) => {
                const items = inventoryItems.filter(i => i.category === cat);
                if (items.length > 0) {
                    options += `<optgroup label="${label}">`;
                    items.forEach(i => {
                        options += `<option value="${i.id}" data-cost="${i.cost}" data-name="${i.name}" data-coa="${i.coa_id || ''}">${i.code} - ${i.name}</option>`;
                    });
                    options += '</optgroup>';
                }
            });
            return options;
        }

        function onInventoryChange(selectEl, itemId) {
            const selected = selectEl.options[selectEl.selectedIndex];
            if (selected.value) {
                const cost = selected.dataset.cost || 0;
                const name = selected.dataset.name || '';
                const coaId = selected.dataset.coa || '';
                
                document.querySelector(`[name="desc_${itemId}"]`).value = name;
                document.querySelector(`[name="price_${itemId}"]`).value = parseFloat(cost).toFixed(0);
                
                if (coaId) {
                    document.querySelector(`[name="account_${itemId}"]`).value = coaId;
                }
                
                calculateTotals();
            }
        }

        function addItem() {
            itemCount++;
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.id = `item-${itemCount}`;
            tr.className = 'border-b border-border-dark/50';
            tr.innerHTML = `
                <td class="p-2">
                    <select name="inventory_${itemCount}" onchange="onInventoryChange(this, ${itemCount})" class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm focus:border-primary">
                        ${getInventoryOptions()}
                    </select>
                </td>
                <td class="p-2">
                    <select name="account_${itemCount}" required class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm focus:border-primary">
                        <option value="">Pilih...</option>
                        ${expenseAccounts.map(a => `<option value="${a.id}">${a.code} - ${a.name}</option>`).join('')}
                    </select>
                </td>
                <td class="p-2">
                    <input type="text" name="desc_${itemCount}" required placeholder="Deskripsi item" 
                           class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm placeholder-text-muted focus:border-primary">
                </td>
                <td class="p-2">
                    <input type="number" name="qty_${itemCount}" value="1" min="0.01" step="0.01" required
                           onchange="calculateTotals()"
                           class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm text-right focus:border-primary">
                </td>
                <td class="p-2">
                    <input type="number" name="price_${itemCount}" value="0" min="0" step="1" required
                           onchange="calculateTotals()"
                           class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm text-right focus:border-primary">
                </td>
                <td class="p-2 text-right font-mono text-white" id="total_${itemCount}">Rp 0</td>
                <td class="p-2">
                    <button type="button" onclick="removeItem(${itemCount})" class="text-text-muted hover:text-accent-red">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            
            // Initialize searchable select for the account dropdown
            const accountSelect = tr.querySelector('[name^="account_"]');
            if (typeof makeSearchable === 'function' && accountSelect) {
                makeSearchable(accountSelect);
            }
        }

        function removeItem(id) {
            document.getElementById(`item-${id}`)?.remove();
            calculateTotals();
        }

        function calculateTotals() {
            let subtotal = 0;
            document.querySelectorAll('[name^="qty_"]').forEach(qtyInput => {
                const id = qtyInput.name.replace('qty_', '');
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(document.querySelector(`[name="price_${id}"]`)?.value) || 0;
                const total = qty * price;
                subtotal += total;
                const totalEl = document.getElementById(`total_${id}`);
                if (totalEl) {
                    totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
                }
            });
            
            document.getElementById('subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('total').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        }

        document.getElementById('purchaseForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin align-middle mr-1">progress_activity</span> Menyimpan...';

            const items = [];
            document.querySelectorAll('[name^="account_"]').forEach(sel => {
                const id = sel.name.replace('account_', '');
                if (sel.value) {
                    const inventoryId = document.querySelector(`[name="inventory_${id}"]`)?.value;
                    const qty = parseFloat(document.querySelector(`[name="qty_${id}"]`)?.value) || 1;
                    const price = parseFloat(document.querySelector(`[name="price_${id}"]`)?.value) || 0;
                    items.push({
                        account_id: parseInt(sel.value),
                        inventory_id: inventoryId ? parseInt(inventoryId) : null,
                        description: document.querySelector(`[name="desc_${id}"]`)?.value || '',
                        quantity: qty,
                        amount: price,
                    });
                }
            });

            const unitId = document.getElementById('unit_id').value;
            const data = {
                contact_id: parseInt(document.getElementById('contact_id').value),
                date: document.getElementById('date').value,
                due_date: document.getElementById('due_date').value || document.getElementById('date').value,
                unit_id: unitId ? parseInt(unitId) : null,
                payable_account_id: parseInt(document.getElementById('payable_account_id').value),
                notes: document.getElementById('notes').value,
                items
            };

            try {
                const response = await fetch('/purchases', {
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
                    window.location.href = '/purchases';
                } else {
                    alert(result.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan saat menyimpan');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined align-middle mr-1">save</span> Simpan Tagihan';
            }
        });

        // Init
        document.getElementById('date').value = new Date().toISOString().split('T')[0];
        
        loadData().then(() => {
            addItem();
        });
    </script>
    @endpush
</x-app-layout>
