<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="w-10 h-10 rounded-xl bg-surface-dark border border-border-dark flex items-center justify-center text-text-muted hover:text-white hover:border-primary transition">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white font-display">Pengeluaran Kas</h2>
                    <p class="text-text-muted text-sm mt-1">Catat pengeluaran uang dari kas/bank</p>
                </div>
            </div>
        </div>
    </x-slot>

    <form id="spendForm" class="space-y-6">
        <!-- Header Info -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
                <h3 class="font-bold text-white mb-4">Informasi Pengeluaran</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Akun Kas/Bank Sumber *</label>
                        <select id="from_account_id" required class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Akun Kas/Bank...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Unit Usaha</label>
                        <select id="unit_id" class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Unit Usaha...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Tanggal *</label>
                        <input type="date" id="date" required class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-text-muted mb-2">Keterangan</label>
                    <textarea id="description" rows="2" placeholder="Keterangan pengeluaran..." class="w-full px-4 py-3 rounded-xl bg-background-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary resize-none"></textarea>
                </div>
            </div>

            <!-- Summary -->  
            <div class="p-6 rounded-2xl border border-orange-500/30 bg-orange-500/10">
                <h3 class="font-bold text-orange-400 mb-4">Ringkasan</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-text-muted">Total Pengeluaran</span>
                        <span class="text-orange-400 font-bold font-mono text-xl" id="total">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-white">Detail Pengeluaran</h3>
                <button type="button" onclick="addItem()" class="px-4 py-2 rounded-full bg-orange-500 text-white font-bold hover:bg-orange-600 transition text-sm">
                    <span class="material-symbols-outlined align-middle mr-1 text-xl">add</span>
                    Tambah Item
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-dark">
                            <th class="p-3 text-left text-xs font-bold text-text-muted uppercase">Akun Tujuan (Beban)</th>
                            <th class="p-3 text-left text-xs font-bold text-text-muted uppercase">Keterangan</th>
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
            <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-full border border-border-dark text-text-muted hover:bg-surface-highlight hover:text-white transition">
                Batal
            </a>
            <button type="submit" id="submitBtn" class="px-8 py-3 rounded-full bg-orange-500 text-white font-bold hover:bg-orange-600 transition">
                <span class="material-symbols-outlined align-middle mr-1">save</span>
                Simpan Pengeluaran
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        let cashAccounts = [];
        let expenseAccounts = [];
        let businessUnits = [];
        let itemCount = 0;

        async function loadData() {
            // Load accounts
            const accountsRes = await fetch('/accounts?detail_only=1', { headers: { 'Accept': 'application/json' } });
            const accountsData = await accountsRes.json();
            const accounts = accountsData.data || [];
            
            // Cash/Bank accounts for FROM account
            const fromSelect = document.getElementById('from_account_id');
            cashAccounts = accounts.filter(a => a.type === 'Asset' && (
                a.code.startsWith('1.1.1') || 
                a.code.startsWith('1100') ||
                a.name.toLowerCase().includes('kas') ||
                a.name.toLowerCase().includes('bank')
            ));
            cashAccounts.forEach(a => {
                fromSelect.innerHTML += `<option value="${a.id}">${a.code} - ${a.name}</option>`;
            });
            
            // Expense accounts for TO accounts
            expenseAccounts = accounts.filter(a => a.type === 'Expense' || a.type === 'Asset');

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
            
            // Initialize searchable select for from account
            if (typeof makeSearchable === 'function') {
                makeSearchable(fromSelect);
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
                    <select name="to_account_${itemCount}" required class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm focus:border-primary">
                        <option value="">Pilih...</option>
                        ${expenseAccounts.map(a => `<option value="${a.id}">${a.code} - ${a.name}</option>`).join('')}
                    </select>
                </td>
                <td class="p-2">
                    <input type="text" name="memo_${itemCount}" placeholder="Keterangan item" 
                           class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm placeholder-text-muted focus:border-primary">
                </td>
                <td class="p-2">
                    <input type="number" name="amount_${itemCount}" value="0" min="0" step="1" required
                           onchange="calculateTotal()"
                           class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm text-right focus:border-primary">
                </td>
                <td class="p-2">
                    <button type="button" onclick="removeItem(${itemCount})" class="text-text-muted hover:text-accent-red">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            
            // Initialize searchable select for to account
            const toAccountSelect = tr.querySelector('[name^="to_account_"]');
            if (typeof makeSearchable === 'function' && toAccountSelect) {
                makeSearchable(toAccountSelect);
            }
        }

        function removeItem(id) {
            document.getElementById(`item-${id}`)?.remove();
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('[name^="amount_"]').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            
            document.getElementById('total').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        document.getElementById('spendForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin align-middle mr-1">progress_activity</span> Menyimpan...';

            const items = [];
            document.querySelectorAll('[name^="to_account_"]').forEach(sel => {
                const id = sel.name.replace('to_account_', '');
                if (sel.value) {
                    items.push({
                        to_account_id: parseInt(sel.value),
                        amount: parseFloat(document.querySelector(`[name="amount_${id}"]`)?.value) || 0,
                        memo: document.querySelector(`[name="memo_${id}"]`)?.value || null,
                    });
                }
            });

            if (items.length === 0) {
                alert('Tambahkan minimal 1 item pengeluaran');
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined align-middle mr-1">save</span> Simpan Pengeluaran';
                return;
            }

            const unitId = document.getElementById('unit_id').value;
            const data = {
                from_account_id: parseInt(document.getElementById('from_account_id').value),
                date: document.getElementById('date').value,
                description: document.getElementById('description').value,
                unit_id: unitId ? parseInt(unitId) : null,
                items
            };

            try {
                const response = await fetch('/cash/spend', {
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
                    alert('Pengeluaran kas berhasil dicatat!');
                    window.location.href = '/dashboard';
                } else {
                    alert(result.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan saat menyimpan');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined align-middle mr-1">save</span> Simpan Pengeluaran';
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
