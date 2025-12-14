<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Chart of Accounts</h2>
                <p class="text-text-muted text-sm mt-1">Kelola akun-akun untuk pencatatan transaksi</p>
            </div>
            <x-btn type="primary" onclick="openCreateModal()">
                <span class="material-symbols-outlined text-xl">add</span>
                Tambah Akun
            </x-btn>
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
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Tipe</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Laporan</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Saldo Normal</th>
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
                <tr class="border-b border-border-dark/50 hover:bg-surface-highlight/30">
                    <td class="p-4 text-white font-mono">${acc.code}</td>
                    <td class="p-4 text-white ${acc.is_parent ? 'font-bold' : ''}">${'—'.repeat(acc.level - 1)} ${acc.name}</td>
                    <td class="p-4">
                        <span class="px-2 py-1 rounded text-xs font-medium ${getTypeColor(acc.type)}">${acc.type}</span>
                    </td>
                    <td class="p-4 text-text-muted">${acc.report_type}</td>
                    <td class="p-4 text-text-muted">${acc.normal_balance}</td>
                    <td class="p-4">
                        ${acc.is_active 
                            ? '<span class="inline-flex items-center gap-1 text-primary text-xs"><span class="material-symbols-outlined text-[14px]">check_circle</span> Aktif</span>'
                            : '<span class="inline-flex items-center gap-1 text-text-muted text-xs"><span class="material-symbols-outlined text-[14px]">cancel</span> Nonaktif</span>'
                        }
                    </td>
                    <td class="p-4 text-right">
                        ${!acc.is_system ? `
                            <button onclick="editAccount(${acc.id})" class="text-text-muted hover:text-white p-1">
                                <span class="material-symbols-outlined text-xl">edit</span>
                            </button>
                        ` : ''}
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
            document.getElementById('is_parent').checked = account.is_parent;
            document.getElementById('accountModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('accountModal').classList.add('hidden');
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
