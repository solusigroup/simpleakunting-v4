<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('sales.index') }}" class="w-10 h-10 rounded-xl bg-surface-dark border border-border-dark flex items-center justify-center text-text-muted hover:text-white hover:border-primary transition">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white font-display">Invoice Penjualan Baru</h2>
                    <p class="text-text-muted text-sm mt-1">Buat invoice untuk pelanggan</p>
                </div>
            </div>
        </div>
    </x-slot>

    <form id="salesForm" class="space-y-6">
        <!-- Customer & Date -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="font-bold text-white mb-4">Informasi Invoice</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Pelanggan *</label>
                        <select id="contact_id" required class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Pelanggan...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Unit Usaha</label>
                        <select id="unit_id" class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Unit Usaha...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Akun Piutang/Kas *</label>
                        <select id="receivable_account_id" required class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Akun...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Tanggal *</label>
                        <input type="date" id="date" required class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Jatuh Tempo *</label>
                        <input type="date" id="due_date" required class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Akun HPP</label>
                        <select id="cogs_account_id" class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Akun HPP...</option>
                        </select>
                        <p class="text-xs text-text-muted mt-1">Harga Pokok Penjualan (untuk barang dagangan)</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Akun Persediaan</label>
                        <select id="inventory_account_id" class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Akun Persediaan...</option>
                        </select>
                        <p class="text-xs text-text-muted mt-1">Persediaan Barang Dagangan</p>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-text-muted mb-2">Catatan</label>
                    <textarea id="notes" rows="2" placeholder="Catatan tambahan..." class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary resize-none"></textarea>
                </div>
            </div>

            <!-- Summary -->
            <div class="p-6 rounded-2xl border border-primary/30 bg-primary/10">
                <h3 class="font-bold text-primary mb-4">Ringkasan</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-text-muted">Subtotal</span>
                        <span class="text-white font-mono" id="subtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Pajak</span>
                        <span class="text-white font-mono">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-text-muted">Diskon</span>
                        <span class="text-white font-mono">Rp 0</span>
                    </div>
                    <hr class="border-border-dark">
                    <div class="flex justify-between">
                        <span class="text-primary font-bold">Total</span>
                        <span class="text-primary font-bold font-mono text-xl" id="total">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-white">Item Penjualan</h3>
                <button type="button" onclick="addItem()" class="px-4 py-2 rounded-full bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition text-sm">
                    <span class="material-symbols-outlined align-middle mr-1 text-xl">add</span>
                    Tambah Item
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-dark">
                            <th class="p-3 text-left text-xs font-bold text-text-muted uppercase w-48">Barang</th>
                            <th class="p-3 text-left text-xs font-bold text-text-muted uppercase">Akun Pendapatan</th>
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
            <a href="{{ route('sales.index') }}" class="px-6 py-3 rounded-full border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition">
                Batal
            </a>
            <button type="submit" id="submitBtn" class="px-8 py-3 rounded-full bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition">
                <span class="material-symbols-outlined align-middle mr-1">save</span>
                Simpan Invoice
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        let customers = [];
        let accounts = [];
        let revenueAccounts = [];
        let businessUnits = [];
        let inventoryItems = [];
        let itemCount = 0;

        async function loadData() {
            // Load customers
            const customersRes = await fetch('/contacts?type=CUSTOMER', { headers: { 'Accept': 'application/json' } });
            const customersData = await customersRes.json();
            customers = customersData.data || [];
            
            const customerSelect = document.getElementById('contact_id');
            customers.forEach(c => {
                customerSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
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
            
            // Asset accounts for receivable
            const receivableSelect = document.getElementById('receivable_account_id');
            accounts.filter(a => a.type === 'Asset').forEach(a => {
                receivableSelect.innerHTML += `<option value="${a.id}">${a.code} - ${a.name}</option>`;
            });

            // Expense accounts for COGS (HPP)
            const cogsSelect = document.getElementById('cogs_account_id');
            accounts.filter(a => a.type === 'Expense').forEach(a => {
                // Auto-select if name contains HPP or Harga Pokok
                const isHPP = a.name.toLowerCase().includes('harga pokok') || 
                              a.name.toLowerCase().includes('hpp') ||
                              a.name.toLowerCase().includes('beban pokok');
                cogsSelect.innerHTML += `<option value="${a.id}" ${isHPP ? 'selected' : ''}>${a.code} - ${a.name}</option>`;
            });

            // Asset accounts for Inventory
            const inventoryAccountSelect = document.getElementById('inventory_account_id');
            accounts.filter(a => a.type === 'Asset').forEach(a => {
                // Auto-select if name contains Persediaan
                const isPersediaan = a.name.toLowerCase().includes('persediaan') || 
                                     a.name.toLowerCase().includes('inventory');
                inventoryAccountSelect.innerHTML += `<option value="${a.id}" ${isPersediaan ? 'selected' : ''}>${a.code} - ${a.name}</option>`;
            });
            
            // Revenue accounts for items
            revenueAccounts = accounts.filter(a => a.type === 'Revenue');

            // Load inventory items (primarily finished goods for sales)
            try {
                const inventoryRes = await fetch('/inventory', { headers: { 'Accept': 'application/json' } });
                const inventoryData = await inventoryRes.json();
                if (inventoryData.success) {
                    inventoryItems = inventoryData.data || [];
                }
            } catch (error) {
                console.log('Inventory not available:', error);
            }
        }

        function getInventoryOptions() {
            let options = '<option value="">-- Manual / Jasa --</option>';
            const categories = {
                'finished_goods': 'Barang Jadi/Dagangan',
                'raw_materials': 'Bahan Baku',
                'supplies': 'Bahan Pembantu',
                'work_in_process': 'Barang Dalam Proses'
            };
            
            // Group by category - prioritize finished goods for sales
            Object.entries(categories).forEach(([cat, label]) => {
                const items = inventoryItems.filter(i => i.category === cat);
                if (items.length > 0) {
                    options += `<optgroup label="${label}">`;
                    items.forEach(i => {
                        const stockInfo = i.stock !== undefined ? ` (Stok: ${i.stock})` : '';
                        options += `<option value="${i.id}" data-price="${i.price}" data-name="${i.name}" data-coa="${i.coa_id || ''}" data-stock="${i.stock || 0}">${i.code} - ${i.name}${stockInfo}</option>`;
                    });
                    options += '</optgroup>';
                }
            });
            return options;
        }

        function onInventoryChange(selectEl, itemId) {
            const selected = selectEl.options[selectEl.selectedIndex];
            if (selected.value) {
                const price = selected.dataset.price || 0;
                const name = selected.dataset.name || '';
                
                document.querySelector(`[name="desc_${itemId}"]`).value = name;
                document.querySelector(`[name="amount_${itemId}"]`).value = parseFloat(price).toFixed(0);
                
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
                        ${revenueAccounts.map(a => `<option value="${a.id}">${a.code} - ${a.name}</option>`).join('')}
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
                    <input type="number" name="amount_${itemCount}" value="0" min="0" step="1" required
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
                const amount = parseFloat(document.querySelector(`[name="amount_${id}"]`)?.value) || 0;
                const total = qty * amount;
                subtotal += total;
                document.getElementById(`total_${id}`).textContent = 'Rp ' + total.toLocaleString('id-ID');
            });
            
            document.getElementById('subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('total').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        }

        document.getElementById('salesForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin align-middle mr-1">progress_activity</span> Menyimpan...';

            const items = [];
            document.querySelectorAll('[name^="account_"]').forEach(sel => {
                const id = sel.name.replace('account_', '');
                if (sel.value) {
                    const inventoryId = document.querySelector(`[name="inventory_${id}"]`)?.value;
                    items.push({
                        account_id: parseInt(sel.value),
                        inventory_id: inventoryId ? parseInt(inventoryId) : null,
                        description: document.querySelector(`[name="desc_${id}"]`)?.value || '',
                        qty: parseFloat(document.querySelector(`[name="qty_${id}"]`)?.value) || 0,
                        amount: parseFloat(document.querySelector(`[name="amount_${id}"]`)?.value) || 0,
                    });
                }
            });

            const unitId = document.getElementById('unit_id').value;
            const cogsAccountId = document.getElementById('cogs_account_id').value;
            const inventoryAccountId = document.getElementById('inventory_account_id').value;
            
            const data = {
                contact_id: parseInt(document.getElementById('contact_id').value),
                date: document.getElementById('date').value,
                due_date: document.getElementById('due_date').value,
                unit_id: unitId ? parseInt(unitId) : null,
                receivable_account_id: parseInt(document.getElementById('receivable_account_id').value),
                cogs_account_id: cogsAccountId ? parseInt(cogsAccountId) : null,
                inventory_account_id: inventoryAccountId ? parseInt(inventoryAccountId) : null,
                notes: document.getElementById('notes').value,
                items
            };

            try {
                const response = await fetch('/sales', {
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
                    window.location.href = '/sales';
                } else {
                    alert(result.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan saat menyimpan');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined align-middle mr-1">save</span> Simpan Invoice';
            }
        });

        // Init
        document.getElementById('date').value = new Date().toISOString().split('T')[0];
        const nextMonth = new Date();
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        document.getElementById('due_date').value = nextMonth.toISOString().split('T')[0];
        
        loadData().then(() => {
            addItem();
        });
    </script>
    @endpush
</x-app-layout>
