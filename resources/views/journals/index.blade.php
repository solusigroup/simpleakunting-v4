<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Jurnal Umum</h2>
                <p class="text-text-muted text-sm mt-1">Riwayat transaksi dan jurnal</p>
            </div>
            <x-btn type="primary" onclick="openCreateModal()">
                <span class="material-symbols-outlined text-xl">add</span>
                Jurnal Manual
            </x-btn>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="flex items-center gap-4 mb-6 flex-wrap">
        <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
            <select id="sourceFilter" class="bg-transparent border-0 text-white text-sm focus:ring-0">
                <option value="">Semua Sumber</option>
                <option value="manual">Manual</option>
                <option value="sales">Penjualan</option>
                <option value="purchase">Pembelian</option>
                <option value="cash_bank">Kas & Bank</option>
            </select>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
            <span class="material-symbols-outlined text-text-muted">business</span>
            <select id="unitFilter" class="bg-transparent border-0 text-white text-sm focus:ring-0">
                <option value="">Semua Unit Usaha</option>
            </select>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
            <span class="material-symbols-outlined text-text-muted">calendar_today</span>
            <input type="date" id="dateStart" class="bg-transparent border-0 text-white text-sm focus:ring-0">
            <span class="text-text-muted">-</span>
            <input type="date" id="dateEnd" class="bg-transparent border-0 text-white text-sm focus:ring-0">
        </div>
    </div>

    <!-- Journals List -->
    <div class="space-y-4" id="journalsList">
        <!-- Loading -->
        <div class="p-8 text-center text-text-muted">
            <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
            <p class="mt-2">Memuat data...</p>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="journalModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-6xl max-h-[90vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white" id="modalTitle">Jurnal Manual</h3>
                    <button onclick="closeModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="journalForm" class="p-6 space-y-4 overflow-y-auto max-h-[70vh]">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Tanggal</label>
                            <input type="date" id="date" required
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Deskripsi</label>
                            <input type="text" id="description" required placeholder="Deskripsi jurnal"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Unit Usaha</label>
                            <select id="unitId" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                <option value="">-- Tidak Ada --</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Kontak (Opsional)</label>
                        <select id="contactId" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">-- Tidak Ada --</option>
                        </select>
                        <p class="text-xs text-text-muted mt-1">Untuk hutang Bank/pinjaman, piutang karyawan, dll.</p>
                    </div>

                    <!-- Journal Lines -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-text-muted">Entry Lines</label>
                            <button type="button" onclick="addLine()" class="text-primary text-sm hover:underline">+ Tambah Baris</button>
                        </div>
                        <div class="rounded-xl border border-border-dark overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-surface-dark">
                                    <tr>
                                        <th class="p-3 text-left text-text-muted font-medium" style="min-width: 450px;">Akun</th>
                                        <th class="p-3 text-right text-text-muted font-medium w-36">Debit</th>
                                        <th class="p-3 text-right text-text-muted font-medium w-36">Kredit</th>
                                        <th class="p-3 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody id="linesBody">
                                    <!-- Lines will be added here -->
                                </tbody>
                                <tfoot class="bg-surface-dark border-t border-border-dark">
                                    <tr>
                                        <td class="p-3 text-right font-bold text-text-muted">Total</td>
                                        <td class="p-3 text-right font-bold text-white" id="totalDebit">0</td>
                                        <td class="p-3 text-right font-bold text-white" id="totalCredit">0</td>
                                        <td></td>
                                    </tr>
                                    <tr id="balanceRow" class="hidden">
                                        <td colspan="4" class="p-3 text-center">
                                            <span class="text-accent-red text-sm">⚠️ Jurnal tidak seimbang!</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <x-btn type="secondary" onclick="closeModal()">Batal</x-btn>
                        <x-btn type="primary" type="submit" id="submitBtn">Simpan Jurnal</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeViewModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-3xl border border-border-dark w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
                <div class="px-8 py-6 border-b border-border-dark flex items-center justify-between bg-surface-dark/50">
                    <div>
                        <h3 class="text-xl font-bold text-white" id="viewDescription">Detail Jurnal</h3>
                        <p class="text-text-muted text-sm mt-1" id="viewReferenceDate"></p>
                    </div>
                    <button onclick="closeViewModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/10 text-text-muted hover:text-white transition">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-8 overflow-y-auto flex-1 space-y-6">
                    <!-- Journal Metadata -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 p-6 rounded-2xl bg-surface-dark/30 border border-border-dark/50">
                        <div>
                            <p class="text-xs text-text-muted uppercase tracking-wider font-bold mb-1">Sumber</p>
                            <p id="viewSource" class="text-white font-medium"></p>
                        </div>
                        <div>
                            <p class="text-xs text-text-muted uppercase tracking-wider font-bold mb-1">Unit Usaha</p>
                            <p id="viewUnit" class="text-white font-medium"></p>
                        </div>
                        <div>
                            <p class="text-xs text-text-muted uppercase tracking-wider font-bold mb-1">Kontak</p>
                            <p id="viewContact" class="text-white font-medium"></p>
                        </div>
                        <div>
                            <p class="text-xs text-text-muted uppercase tracking-wider font-bold mb-1">Status</p>
                            <div id="viewStatus"></div>
                        </div>
                    </div>

                    <!-- Journal Items Table -->
                    <div class="rounded-2xl border border-border-dark overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-surface-dark">
                                <tr>
                                    <th class="px-6 py-4 text-left text-text-muted font-bold uppercase tracking-wider">Akun</th>
                                    <th class="px-6 py-4 text-right text-text-muted font-bold uppercase tracking-wider w-40">Debit</th>
                                    <th class="px-6 py-4 text-right text-text-muted font-bold uppercase tracking-wider w-40">Kredit</th>
                                </tr>
                            </thead>
                            <tbody id="viewItemsBody">
                                <!-- Detailed lines will be injected here -->
                            </tbody>
                            <tfoot class="bg-surface-dark/50 border-t border-border-dark">
                                <tr>
                                    <td class="px-6 py-4 text-right font-bold text-white">TOTAL</td>
                                    <td class="px-6 py-4 text-right font-extrabold text-primary" id="viewTotalDebit">0</td>
                                    <td class="px-6 py-4 text-right font-extrabold text-primary" id="viewTotalCredit">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="px-8 py-6 border-t border-border-dark bg-surface-dark/30 flex justify-end">
                    <button onclick="closeViewModal()" class="px-6 py-2.5 rounded-xl bg-white/5 border border-border-dark text-white font-bold hover:bg-white/10 transition">Tutup Detail</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let accounts = [];
        let businessUnits = [];
        let contacts = [];
        let lineCount = 0;
        let editingJournalId = null;

        async function loadAccounts() {
            const response = await fetch('/accounts?detail_only=1', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            accounts = data.data || [];
        }

        async function loadBusinessUnits() {
            const response = await fetch('/units', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            businessUnits = data.data || [];
            
            // Populate filter dropdown
            const filterSelect = document.getElementById('unitFilter');
            filterSelect.innerHTML = '<option value="">Semua Unit Usaha</option>' +
                businessUnits.map(u => `<option value="${u.id}">${u.code} - ${u.name}</option>`).join('');
            
            // Populate form dropdown
            const formSelect = document.getElementById('unitId');
            formSelect.innerHTML = '<option value="">-- Tidak Ada --</option>' +
                businessUnits.map(u => `<option value="${u.id}">${u.code} - ${u.name}</option>`).join('');
        }

        async function loadContacts() {
            const response = await fetch('/contacts', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            contacts = data.data || [];
            
            // Populate contact dropdown in form
            const contactSelect = document.getElementById('contactId');
            contactSelect.innerHTML = '<option value="">-- Tidak Ada --</option>' +
                contacts.map(c => `<option value="${c.id}">${c.name} (${c.type})</option>`).join('');
        }

        async function loadJournals() {
            const source = document.getElementById('sourceFilter').value;
            const unitId = document.getElementById('unitFilter').value;
            const start = document.getElementById('dateStart').value;
            const end = document.getElementById('dateEnd').value;
            
            let url = '/journals?';
            if (source) url += `source=${source}&`;
            if (unitId) url += `unit_id=${unitId}&`;
            if (start) url += `date_start=${start}&`;
            if (end) url += `date_end=${end}`;
            
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            renderJournals(result.data?.data || []);
        }

        function renderJournals(journals) {
            const container = document.getElementById('journalsList');
            
            if (journals.length === 0) {
                container.innerHTML = `
                    <div class="p-12 text-center text-text-muted rounded-2xl border border-border-dark bg-surface-dark/30">
                        <span class="material-symbols-outlined text-5xl mb-3">receipt_long</span>
                        <p>Belum ada jurnal</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = journals.map(journal => `
                <div class="group rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden hover:border-primary/50 transition duration-300">
                    <div class="px-6 py-5 flex items-center justify-between">
                        <div class="flex items-center gap-5">
                            <div class="w-12 h-12 rounded-2xl ${getSourceColor(journal.source)} flex items-center justify-center shadow-lg">
                                <span class="material-symbols-outlined text-2xl">${getSourceIcon(journal.source)}</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <p class="text-white font-bold text-lg">${journal.description}</p>
                                    ${journal.is_posted 
                                        ? '<span class="px-2.5 py-0.5 rounded-full bg-primary/20 text-primary text-[10px] font-black uppercase tracking-widest border border-primary/30">Posted</span>' 
                                        : '<span class="px-2.5 py-0.5 rounded-full bg-white/10 text-text-muted text-[10px] font-black uppercase tracking-widest border border-white/10">Draft</span>'
                                    }
                                </div>
                                <p class="text-text-muted text-sm flex items-center gap-2">
                                    <span class="font-mono text-xs opacity-70">${journal.reference}</span> 
                                    <span class="opacity-30">|</span> 
                                    <span>${new Date(journal.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
                                    ${journal.business_unit ? `<span class="ml-2 px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-bold border border-emerald-500/20">${journal.business_unit.name}</span>` : ''}
                                    ${journal.contact ? `<span class="ml-2 px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 text-[10px] font-bold border border-cyan-500/20">${journal.contact.name}</span>` : ''}
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button onclick="viewJournal(${journal.id})" class="p-2.5 rounded-xl bg-surface-dark border border-border-dark text-white hover:bg-white/10 transition group/btn" title="Lihat Detail">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                            </button>
                            
                            <button onclick="toggleJournalPost(${journal.id})" 
                                    class="p-2.5 rounded-xl border transition flex items-center gap-2 font-bold text-xs ${journal.is_posted ? 'bg-orange-500/10 border-orange-500/30 text-orange-400 hover:bg-orange-500/20' : 'bg-primary/10 border-primary/30 text-primary hover:bg-primary/20'}" 
                                    title="${journal.is_posted ? 'Batalkan Posting' : 'Posting Jurnal'}">
                                <span class="material-symbols-outlined text-sm">${journal.is_posted ? 'lock_open' : 'check_circle'}</span>
                                ${journal.is_posted ? 'Unpost' : 'Post'}
                            </button>

                            ${!journal.is_posted && (journal.source === 'manual' || journal.source === 'cash_bank') ? `
                                <button onclick="editJournal(${journal.id})" class="p-2.5 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-400 hover:bg-blue-500/20 transition" title="Edit Jurnal">
                                    <span class="material-symbols-outlined text-sm">edit</span>
                                </button>
                            ` : ''}

                            ${!journal.is_posted ? `
                                <button onclick="deleteJournal(${journal.id})" class="p-2.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-500 hover:bg-red-500/20 transition" title="Hapus Jurnal">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function getSourceColor(source) {
            const colors = {
                'manual': 'bg-purple-500/20 text-purple-400',
                'sales': 'bg-primary/20 text-primary',
                'purchase': 'bg-orange-500/20 text-orange-400',
                'cash_bank': 'bg-blue-500/20 text-blue-400',
            };
            return colors[source] || 'bg-gray-500/20 text-gray-400';
        }

        function getSourceIcon(source) {
            const icons = {
                'manual': 'edit_note',
                'sales': 'point_of_sale',
                'purchase': 'shopping_cart',
                'cash_bank': 'account_balance',
            };
            return icons[source] || 'receipt';
        }

        function getSourceBadge(source) {
            return getSourceColor(source);
        }

        function getSourceLabel(source) {
            const labels = {
                'manual': 'Manual',
                'sales': 'Penjualan',
                'purchase': 'Pembelian',
                'cash_bank': 'Kas & Bank',
            };
            return labels[source] || source;
        }

        function addLine(accountId = null, debit = 0, credit = 0) {
            lineCount++;
            const tbody = document.getElementById('linesBody');
            const tr = document.createElement('tr');
            tr.id = `line-${lineCount}`;
            tr.className = 'border-t border-border-dark/50';
            tr.innerHTML = `
                <td class="p-2" style="min-width: 450px;">
                    <select name="account_${lineCount}" required
                            class="account-select w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm focus:border-primary focus:ring-primary">
                        <option value="">Pilih Akun</option>
                        ${accounts.map(a => `<option value="${a.id}" ${accountId == a.id ? 'selected' : ''}>${a.code} - ${a.name}</option>`).join('')}
                    </select>
                </td>
                <td class="p-2">
                    <input type="number" name="debit_${lineCount}" value="${debit}" min="0" step="0.01"
                           onchange="updateTotals()"
                           class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm text-right focus:border-primary focus:ring-primary">
                </td>
                <td class="p-2">
                    <input type="number" name="credit_${lineCount}" value="${credit}" min="0" step="0.01"
                           onchange="updateTotals()"
                           class="w-full px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-sm text-right focus:border-primary focus:ring-primary">
                </td>
                <td class="p-2 text-center">
                    <button type="button" onclick="removeLine(${lineCount})" class="text-text-muted hover:text-accent-red">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            
            // Initialize searchable select for the new row
            const newSelect = tr.querySelector('.account-select');
            if (typeof makeSearchable === 'function') {
                makeSearchable(newSelect);
            }
        }

        function removeLine(id) {
            document.getElementById(`line-${id}`)?.remove();
            updateTotals();
        }

        function updateTotals() {
            let totalDebit = 0;
            let totalCredit = 0;
            
            document.querySelectorAll('[name^="debit_"]').forEach(input => {
                totalDebit += parseFloat(input.value) || 0;
            });
            document.querySelectorAll('[name^="credit_"]').forEach(input => {
                totalCredit += parseFloat(input.value) || 0;
            });
            
            document.getElementById('totalDebit').textContent = totalDebit.toLocaleString('id-ID');
            document.getElementById('totalCredit').textContent = totalCredit.toLocaleString('id-ID');
            
            const balanced = Math.abs(totalDebit - totalCredit) < 0.01;
            document.getElementById('balanceRow').classList.toggle('hidden', balanced);
            document.getElementById('submitBtn').disabled = !balanced || totalDebit === 0;
        }

        function openCreateModal() {
            editingJournalId = null;
            document.getElementById('modalTitle').textContent = 'Jurnal Manual';
            document.getElementById('journalForm').reset();
            document.getElementById('date').value = new Date().toISOString().split('T')[0];
            document.getElementById('linesBody').innerHTML = '';
            lineCount = 0;
            addLine();
            addLine();
            updateTotals();
            document.getElementById('journalModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('journalModal').classList.add('hidden');
            editingJournalId = null;
        }

        async function editJournal(id) {
            const response = await fetch(`/journals/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            
            if (result.success) {
                const journal = result.data;
                editingJournalId = journal.id;
                document.getElementById('modalTitle').textContent = 'Edit Jurnal Manual';
                
                document.getElementById('date').value = journal.date;
                document.getElementById('description').value = journal.description;
                document.getElementById('unitId').value = journal.business_unit_id || '';
                document.getElementById('contactId').value = journal.contact_id || '';
                
                const tbody = document.getElementById('linesBody');
                tbody.innerHTML = '';
                lineCount = 0;
                
                journal.items.forEach(item => {
                    addLine(item.coa_id, parseFloat(item.debit), parseFloat(item.credit));
                });
                
                updateTotals();
                document.getElementById('journalModal').classList.remove('hidden');
            } else {
                alert(result.message || 'Gagal memuat detail jurnal');
            }
        }

        document.getElementById('journalForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const lines = [];
            document.querySelectorAll('#linesBody tr').forEach(tr => {
                const id = tr.id.replace('line-', '');
                const account_id = tr.querySelector(`[name="account_${id}"]`).value;
                const debit = parseFloat(tr.querySelector(`[name="debit_${id}"]`).value) || 0;
                const credit = parseFloat(tr.querySelector(`[name="credit_${id}"]`).value) || 0;
                if (account_id && (debit > 0 || credit > 0)) {
                    lines.push({ account_id: parseInt(account_id), debit, credit });
                }
            });
            
            const unitId = document.getElementById('unitId').value;
            const contactId = document.getElementById('contactId').value;
            
            const url = editingJournalId ? `/journals/${editingJournalId}` : '/journals/manual';
            const method = editingJournalId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    date: document.getElementById('date').value,
                    description: document.getElementById('description').value,
                    unit_id: unitId || null,
                    contact_id: contactId || null,
                    lines
                })
            });

            const result = await response.json();
            if (result.success) {
                closeModal();
                loadJournals();
            } else {
                alert(result.message || 'Terjadi kesalahan');
            }
        });

        // Set default dates
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
        const day = String(today.getDate()).padStart(2, '0');
        
        document.getElementById('dateStart').value = `${year}-${month}-01`; // First day of current month
        document.getElementById('dateEnd').value = `${year}-${month}-${day}`; // Today

        document.getElementById('sourceFilter').addEventListener('change', loadJournals);
        document.getElementById('unitFilter').addEventListener('change', loadJournals);
        document.getElementById('dateStart').addEventListener('change', loadJournals);
        document.getElementById('dateEnd').addEventListener('change', loadJournals);

        async function viewJournal(id) {
            const response = await fetch(`/journals/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            
            if (result.success) {
                const journal = result.data;
                document.getElementById('viewDescription').textContent = journal.description;
                document.getElementById('viewReferenceDate').textContent = `${journal.reference} • ${new Date(journal.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`;
                document.getElementById('viewSource').textContent = getSourceLabel(journal.source);
                document.getElementById('viewUnit').textContent = journal.business_unit ? journal.business_unit.name : '-';
                document.getElementById('viewContact').textContent = journal.contact ? journal.contact.name : '-';
                
                const statusDiv = document.getElementById('viewStatus');
                statusDiv.innerHTML = journal.is_posted 
                    ? '<span class="px-3 py-1 rounded-full bg-primary/20 text-primary text-[10px] font-black uppercase tracking-widest border border-primary/30">Posted</span>' 
                    : '<span class="px-3 py-1 rounded-full bg-white/10 text-text-muted text-[10px] font-black uppercase tracking-widest border border-white/10">Draft</span>';

                const itemsBody = document.getElementById('viewItemsBody');
                itemsBody.innerHTML = journal.items.map(item => `
                    <tr class="border-t border-border-dark/50">
                        <td class="px-6 py-4">
                            <p class="text-white font-medium">${item.account.code} - ${item.account.name}</p>
                            ${item.memo ? `<p class="text-text-muted text-xs mt-0.5 italic">${item.memo}</p>` : ''}
                        </td>
                        <td class="px-6 py-4 text-right ${item.debit > 0 ? 'text-white' : 'text-text-muted opacity-30'}">
                            ${parseFloat(item.debit).toLocaleString('id-ID')}
                        </td>
                        <td class="px-6 py-4 text-right ${item.credit > 0 ? 'text-white' : 'text-text-muted opacity-30'}">
                            ${parseFloat(item.credit).toLocaleString('id-ID')}
                        </td>
                    </tr>
                `).join('');

                const totalDebit = journal.items.reduce((sum, item) => sum + parseFloat(item.debit), 0);
                const totalCredit = journal.items.reduce((sum, item) => sum + parseFloat(item.credit), 0);
                document.getElementById('viewTotalDebit').textContent = totalDebit.toLocaleString('id-ID');
                document.getElementById('viewTotalCredit').textContent = totalCredit.toLocaleString('id-ID');

                document.getElementById('viewModal').classList.remove('hidden');
            }
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        async function toggleJournalPost(id) {
            if (!confirm('Ubah status validasi/posting jurnal ini?')) return;
            
            const response = await fetch(`/journals/${id}/toggle-post`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const result = await response.json();
            
            if (result.success) {
                loadJournals();
            } else {
                alert(result.message);
            }
        }

        async function deleteJournal(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus jurnal ini? Tindakan ini tidak dapat dibatalkan.')) return;
            
            const response = await fetch(`/journals/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const result = await response.json();
            
            if (result.success) {
                loadJournals();
            } else {
                alert(result.message);
            }
        }

        // Initial load
        loadAccounts();
        loadBusinessUnits();
        loadContacts();
        loadJournals();
    </script>
    @endpush
</x-app-layout>
