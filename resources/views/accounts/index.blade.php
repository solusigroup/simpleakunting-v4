<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Chart of Accounts</h2>
                <p class="text-text-muted text-sm mt-1">Kelola akun-akun untuk pencatatan transaksi</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="window.location.href='/accounts/import'">
                    <span class="material-symbols-outlined text-xl">upload_file</span>
                    Import Excel
                </x-btn>
                <x-btn type="primary" onclick="openCreateModal()">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Tambah Akun
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="flex items-center gap-4 mb-6">
        <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
            <span class="material-symbols-outlined text-text-muted">filter_list</span>
            <select id="typeFilter" class="bg-transparent border-0 text-white text-sm focus:ring-0">
                <option value="">Semua Tipe</option>
                <option value="Asset">Asset</option>
                <option value="Liability">Liability</option>
                <option value="Equity">Equity</option>
                <option value="Revenue">Revenue</option>
                <option value="Expense">Expense</option>
            </select>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
            <span class="material-symbols-outlined text-text-muted">search</span>
            <input type="text" id="searchInput" placeholder="Cari akun..." 
                   class="bg-transparent border-0 text-white text-sm focus:ring-0 placeholder-text-muted">
        </div>
    </div>

    <!-- Accounts Table -->
    <div class="rounded-2xl border border-border-dark overflow-hidden bg-surface-dark/30">
        <table class="w-full text-left border-collapse" id="accountsTable">
            <thead>
                <tr class="border-b border-border-dark bg-surface-dark">
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Kode</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Nama Akun</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider w-24">Jenis</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Tipe</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Saldo Normal</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right w-40">Saldo Awal</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Status</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm" id="accountsBody">
                <!-- Will be loaded via JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Create/Edit Modal -->
    <div id="accountModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-lg">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white" id="modalTitle">Tambah Akun</h3>
                    <button onclick="closeModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="accountForm" class="p-6 space-y-4">
                    <input type="hidden" id="accountId">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Kode Akun</label>
                            <input type="text" id="code" required
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Tipe</label>
                            <select id="type" required
                                    class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                <option value="Asset">Asset</option>
                                <option value="Liability">Liability</option>
                                <option value="Equity">Equity</option>
                                <option value="Revenue">Revenue</option>
                                <option value="Expense">Expense</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama Akun</label>
                        <input type="text" id="name" required
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Laporan</label>
                            <select id="report_type" required
                                    class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                <option value="NERACA">Neraca</option>
                                <option value="LABARUGI">Laba Rugi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Saldo Normal</label>
                            <select id="normal_balance" required
                                    class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                <option value="DEBIT">Debit</option>
                                <option value="KREDIT">Kredit</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Kategori <span class="text-xs">(opsional)</span></label>
                        <select id="account_category"
                                class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">-- Pilih Kategori --</option>
                            <optgroup label="Assets">
                                <option value="cash_bank">Kas & Bank</option>
                                <option value="accounts_receivable">Piutang Usaha</option>
                                <option value="other_receivable">Piutang Lainnya</option>
                                <option value="inventory">Persediaan</option>
                                <option value="prepaid_expense">Biaya Dibayar Dimuka</option>
                                <option value="other_current_asset">Aset Lancar Lainnya</option>
                                <option value="fixed_asset">Aset Tetap</option>
                                <option value="accumulated_depreciation">Akumulasi Penyusutan</option>
                                <option value="intangible_asset">Aset Tidak Berwujud</option>
                                <option value="other_asset">Aset Lainnya</option>
                            </optgroup>
                            <optgroup label="Liabilities">
                                <option value="accounts_payable">Hutang Usaha</option>
                                <option value="other_payable">Hutang Lainnya</option>
                                <option value="accrued_expense">Biaya Yang Masih Harus Dibayar</option>
                                <option value="other_current_liability">Kewajiban Lancar Lainnya</option>
                                <option value="long_term_liability">Kewajiban Jangka Panjang</option>
                            </optgroup>
                            <optgroup label="Equity">
                                <option value="equity_capital">Modal</option>
                                <option value="equity_retained">Laba Ditahan</option>
                                <option value="equity_other">Ekuitas Lainnya</option>
                            </optgroup>
                            <optgroup label="Revenue">
                                <option value="revenue_sales">Pendapatan Penjualan</option>
                                <option value="revenue_service">Pendapatan Jasa</option>
                                <option value="revenue_other">Pendapatan Lainnya</option>
                                <option value="other_income">Pendapatan Lain-lain</option>
                            </optgroup>
                            <optgroup label="Expenses">
                                <option value="cogs">Harga Pokok Penjualan</option>
                                <option value="expense_operational">Beban Operasional</option>
                                <option value="expense_administrative">Beban Administrasi</option>
                                <option value="expense_selling">Beban Penjualan</option>
                                <option value="expense_other">Beban Lainnya</option>
                                <option value="other_expense">Beban Lain-lain</option>
                            </optgroup>
                            <optgroup label="Other">
                                <option value="general">Umum</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_parent" class="rounded border-border-dark bg-surface-dark text-primary focus:ring-primary">
                        <label for="is_parent" class="text-sm text-text-muted">Header Account (tidak bisa diisi transaksi)</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <x-btn type="secondary" onclick="closeModal()">Batal</x-btn>
                        <x-btn type="primary" type="submit">Simpan</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let accounts = [];

        // Load accounts
        async function loadAccounts() {
            const typeFilter = document.getElementById('typeFilter').value;
            const url = `/accounts${typeFilter ? `?type=${typeFilter}` : ''}`;
            
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            accounts = data.data || [];
            renderTable();
        }

        function renderTable() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const tbody = document.getElementById('accountsBody');
            
            const filtered = accounts.filter(acc => 
                acc.code.toLowerCase().includes(search) || 
                acc.name.toLowerCase().includes(search)
            );

            tbody.innerHTML = filtered.map(acc => `
                <tr class="border-b border-border-dark/50 hover:bg-surface-highlight/30" data-id="${acc.id}">
                    <td class="p-4 text-white font-mono">${acc.code}</td>
                    <td class="p-4 ${acc.is_parent ? 'font-bold' : ''}">
                        <span class="text-text-muted">${'—'.repeat(acc.level - 1)}</span>
                        ${!acc.has_transactions ? `
                            <input type="text" value="${acc.name}" 
                                   data-id="${acc.id}" data-field="name"
                                   onchange="updateField(${acc.id}, 'name', this.value)"
                                   class="bg-transparent border-0 text-white p-0 focus:ring-0 focus:border-b focus:border-primary w-full hover:bg-surface-dark/50 rounded px-1 -mx-1"/>
                        ` : `
                            <span class="text-white">${acc.name}</span>
                        `}
                        ${acc.is_system ? '<span class="ml-2 px-2 py-0.5 text-[10px] bg-yellow-500/20 text-yellow-400 rounded border border-yellow-500/30 font-semibold">SYSTEM</span>' : ''}
                        ${acc.has_transactions ? '<span class="ml-2 px-2 py-0.5 text-[10px] bg-blue-500/20 text-blue-400 rounded border border-blue-500/30 font-semibold">AKTIF</span>' : ''}
                    </td>
                    <td class="p-4">
                        ${acc.is_parent 
                            ? '<span class="px-2 py-1 rounded text-xs font-medium bg-gray-500/20 text-gray-400 border border-gray-500/30">HEADER</span>' 
                            : '<span class="px-2 py-1 rounded text-xs font-medium bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">DETAIL</span>'
                        }
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded text-xs font-medium ${getTypeColor(acc.type)}">${acc.type}</span>
                    </td>
                    <td class="p-4 text-text-muted">${acc.normal_balance}</td>
                    <td class="p-4 text-right">
                        ${!acc.is_parent ? `
                            <input type="number" value="${acc.opening_balance || 0}" 
                                   data-id="${acc.id}" data-field="opening_balance"
                                   onchange="updateField(${acc.id}, 'opening_balance', this.value)"
                                   class="w-28 px-2 py-1 rounded bg-background-dark border border-border-dark text-white text-sm text-right focus:border-primary focus:ring-1 focus:ring-primary"/>
                        ` : '<span class="text-text-muted/50 text-xs">—</span>'}
                    </td>
                    <td class="p-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   ${acc.is_active ? 'checked' : ''} 
                                   onchange="toggleStatus(${acc.id}, this.checked)"
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            <span class="ms-3 text-sm font-medium ${acc.is_active ? 'text-primary' : 'text-text-muted'}">
                                ${acc.is_active ? 'Aktif' : 'Nonaktif'}
                            </span>
                        </label>
                    </td>
                    <td class="p-4 text-right">
                        ${!acc.is_system ? `
                            <button onclick="editAccount(${acc.id})" 
                                    class="text-text-muted hover:text-white p-1 transition-colors"
                                    title="Edit akun lengkap">
                                <span class="material-symbols-outlined text-xl">edit</span>
                            </button>
                        ` : `
                            <span class="inline-flex items-center gap-1 text-text-muted/40" title="Akun sistem tidak dapat diedit">
                                <span class="material-symbols-outlined text-xl">lock</span>
                            </span>
                        `}
                    </td>
                </tr>
            `).join('');
        }

        function getTypeColor(type) {
            const colors = {
                'Asset': 'bg-blue-500/20 text-blue-400',
                'Liability': 'bg-orange-500/20 text-orange-400',
                'Equity': 'bg-purple-500/20 text-purple-400',
                'Revenue': 'bg-green-500/20 text-green-400',
                'Expense': 'bg-red-500/20 text-red-400',
            };
            return colors[type] || 'bg-gray-500/20 text-gray-400';
        }

        function getCategoryLabel(category) {
            const labels = {
                'cash_bank': 'Kas & Bank',
                'accounts_receivable': 'Piutang Usaha',
                'other_receivable': 'Piutang Lainnya',
                'inventory': 'Persediaan',
                'prepaid_expense': 'Biaya Dibayar Dimuka',
                'other_current_asset': 'Aset Lancar Lainnya',
                'fixed_asset': 'Aset Tetap',
                'accumulated_depreciation': 'Akumulasi Penyusutan',
                'intangible_asset': 'Aset Tidak Berwujud',
                'other_asset': 'Aset Lainnya',
                'accounts_payable': 'Hutang Usaha',
                'other_payable': 'Hutang Lainnya',
                'accrued_expense': 'Biaya YMH Dibayar',
                'other_current_liability': 'Kewajiban Lancar',
                'long_term_liability': 'Kewajiban Jk. Panjang',
                'equity_capital': 'Modal',
                'equity_retained': 'Laba Ditahan',
                'equity_other': 'Ekuitas Lainnya',
                'revenue_sales': 'Pend. Penjualan',
                'revenue_service': 'Pend. Jasa',
                'revenue_other': 'Pend. Lainnya',
                'other_income': 'Pend. Lain-lain',
                'cogs': 'HPP',
                'expense_operational': 'Beban Operasional',
                'expense_administrative': 'Beban Administrasi',
                'expense_selling': 'Beban Penjualan',
                'expense_other': 'Beban Lainnya',
                'other_expense': 'Beban Lain-lain',
                'general': 'Umum'
            };
            return labels[category] || category;
        }

        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Akun';
            document.getElementById('accountForm').reset();
            document.getElementById('accountId').value = '';
            document.getElementById('accountModal').classList.remove('hidden');
        }

        function editAccount(id) {
            const account = accounts.find(a => a.id === id);
            if (!account) return;

            document.getElementById('modalTitle').textContent = 'Edit Akun';
            document.getElementById('accountId').value = account.id;
            document.getElementById('code').value = account.code;
            document.getElementById('name').value = account.name;
            document.getElementById('type').value = account.type;
            document.getElementById('report_type').value = account.report_type;
            document.getElementById('normal_balance').value = account.normal_balance;
            document.getElementById('account_category').value = account.account_category || '';
            document.getElementById('is_parent').checked = account.is_parent;
            document.getElementById('accountModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('accountModal').classList.add('hidden');
        }

        // Inline update for name and opening_balance
        async function updateField(id, field, value) {
            try {
                const data = {};
                data[field] = field === 'opening_balance' ? parseFloat(value) || 0 : value;
                
                const response = await fetch(`/accounts/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    // Update local data
                    const account = accounts.find(a => a.id === id);
                    if (account) {
                        account[field] = value;
                    }
                    // Show quick feedback
                    const input = document.querySelector(`[data-id="${id}"][data-field="${field}"]`);
                    if (input) {
                        input.classList.add('border-green-500');
                        setTimeout(() => input.classList.remove('border-green-500'), 1000);
                    }
                } else {
                    alert(result.message || 'Terjadi kesalahan');
                    loadAccounts();
                }
            } catch (error) {
                console.error('Error updating field:', error);
                alert('Terjadi kesalahan saat menyimpan');
                loadAccounts();
            }
        }

        async function toggleStatus(id, isActive) {
            try {
                const response = await fetch(`/accounts/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ is_active: isActive })
                });

                const result = await response.json();
                if (result.success) {
                    // Update local data
                    const account = accounts.find(a => a.id === id);
                    if (account) {
                        account.is_active = isActive;
                    }
                    renderTable();
                } else {
                    alert(result.message || 'Terjadi kesalahan');
                    // Revert by reloading
                    loadAccounts();
                }
            } catch (error) {
                console.error('Error toggling status:', error);
                alert('Terjadi kesalahan saat mengubah status');
                loadAccounts();
            }
        }

        document.getElementById('accountForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('accountId').value;
            const isEdit = !!id;
            
            const data = {
                code: document.getElementById('code').value,
                name: document.getElementById('name').value,
                type: document.getElementById('type').value,
                report_type: document.getElementById('report_type').value,
                normal_balance: document.getElementById('normal_balance').value,
                account_category: document.getElementById('account_category').value || null,
                is_parent: document.getElementById('is_parent').checked,
            };

            const response = await fetch(isEdit ? `/accounts/${id}` : '/accounts', {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                closeModal();
                loadAccounts();
            } else {
                alert(result.message || 'Terjadi kesalahan');
            }
        });

        document.getElementById('typeFilter').addEventListener('change', loadAccounts);
        document.getElementById('searchInput').addEventListener('input', renderTable);

        // Initial load
        loadAccounts();
    </script>
    @endpush
</x-app-layout>
