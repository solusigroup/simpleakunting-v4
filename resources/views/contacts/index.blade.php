<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Pelanggan & Supplier</h2>
                <p class="text-text-muted text-sm mt-1">Kelola data pelanggan dan supplier</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="window.location.href='/contacts/export'">
                    <span class="material-symbols-outlined text-xl">download</span>
                    Export Excel
                </x-btn>
                <x-btn type="secondary" onclick="window.location.href='/contacts/import'">
                    <span class="material-symbols-outlined text-xl">upload_file</span>
                    Import Excel
                </x-btn>
                <x-btn type="primary" onclick="openCreateModal()">
                    <span class="material-symbols-outlined text-xl">add</span>
                    Tambah Kontak
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="flex items-center gap-4 mb-6">
        <div class="flex items-center gap-2">
            <button onclick="filterType('')" id="btnAll" class="px-4 py-2 rounded-full text-sm font-medium transition border border-border-dark bg-surface-highlight text-white">
                Semua
            </button>
            <button onclick="filterType('CUSTOMER')" id="btnCustomer" class="px-4 py-2 rounded-full text-sm font-medium transition border border-border-dark text-text-muted hover:bg-surface-highlight">
                Pelanggan
            </button>
            <button onclick="filterType('SUPPLIER')" id="btnSupplier" class="px-4 py-2 rounded-full text-sm font-medium transition border border-border-dark text-text-muted hover:bg-surface-highlight">
                Supplier
            </button>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30 ml-auto">
            <span class="material-symbols-outlined text-text-muted">search</span>
            <input type="text" id="searchInput" placeholder="Cari kontak..." 
                   class="bg-transparent border-0 text-white text-sm focus:ring-0 placeholder-text-muted w-48">
        </div>
    </div>

    <!-- Contacts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="contactsGrid">
        <!-- Will be loaded via JavaScript -->
    </div>

    <!-- Create/Edit Modal -->
    <div id="contactModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-lg">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white" id="modalTitle">Tambah Kontak</h3>
                    <button onclick="closeModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="contactForm" class="p-6 space-y-4">
                    <input type="hidden" id="contactId">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama</label>
                        <input type="text" id="name" required
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Tipe</label>
                        <select id="type" required
                                class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="Customer">Pelanggan</option>
                            <option value="Supplier">Supplier</option>
                            <option value="Both">Keduanya</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Telepon</label>
                            <input type="text" id="phone"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Email</label>
                            <input type="email" id="email"
                                   class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Alamat</label>
                        <textarea id="address" rows="2"
                                  class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white placeholder-text-muted focus:border-primary focus:ring-primary resize-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <x-btn type="secondary" onclick="closeModal()">Batal</x-btn>
                        <x-btn type="primary" type="submit">Simpan</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detail/History Modal -->
    <div id="detailModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDetailModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-3xl max-h-[80vh] flex flex-col">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between shrink-0">
                    <h3 class="text-lg font-bold text-white" id="detailTitle">Detail Kontak</h3>
                    <button onclick="closeDetailModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto flex-1" id="detailContent">
                    <!-- Will be loaded via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let contacts = [];
        let currentType = '';

        async function loadContacts() {
            const url = `/contacts${currentType ? `?type=${currentType}` : ''}`;
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            contacts = data.data || [];
            renderGrid();
        }

        function renderGrid() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const grid = document.getElementById('contactsGrid');
            
            const filtered = contacts.filter(c => 
                c.name.toLowerCase().includes(search) ||
                (c.email && c.email.toLowerCase().includes(search))
            );

            if (filtered.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full py-12 text-center text-text-muted">
                        <span class="material-symbols-outlined text-5xl mb-3">person_off</span>
                        <p>Tidak ada kontak ditemukan</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = filtered.map(contact => `
                <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition group cursor-pointer" onclick="showContactDetail(${contact.id})">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-12 h-12 rounded-full ${getTypeColor(contact.type)} flex items-center justify-center">
                            <span class="font-bold text-lg">${contact.name.charAt(0).toUpperCase()}</span>
                        </div>
                        <span class="px-2 py-1 rounded text-xs font-medium ${getTypeBadge(contact.type)}">${getTypeLabel(contact.type)}</span>
                    </div>
                    <h3 class="text-white font-bold mb-1">${contact.name}</h3>
                    <p class="text-text-muted text-sm mb-3">${contact.email || contact.phone || '-'}</p>
                    <div class="flex items-center justify-between pt-3 border-t border-border-dark/50">
                        <span class="text-xs text-text-muted">${contact.invoices_count || 0} transaksi</span>
                        <button onclick="event.stopPropagation(); editContact(${contact.id})" class="text-text-muted hover:text-primary opacity-0 group-hover:opacity-100 transition">
                            <span class="material-symbols-outlined">edit</span>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function getTypeColor(type) {
            return type === 'Customer' ? 'bg-primary/20 text-primary' :
                   type === 'Supplier' ? 'bg-blue-500/20 text-blue-400' :
                   'bg-purple-500/20 text-purple-400';
        }

        function getTypeBadge(type) {
            return type === 'Customer' ? 'bg-primary/20 text-primary' :
                   type === 'Supplier' ? 'bg-blue-500/20 text-blue-400' :
                   'bg-purple-500/20 text-purple-400';
        }

        function getTypeLabel(type) {
            return type === 'Customer' ? 'Pelanggan' :
                   type === 'Supplier' ? 'Supplier' : 'Keduanya';
        }

        function filterType(type) {
            currentType = type;
            
            document.querySelectorAll('[id^="btn"]').forEach(btn => {
                btn.classList.remove('bg-surface-highlight', 'text-white');
                btn.classList.add('text-text-muted');
            });
            
            const activeBtn = type === '' ? 'btnAll' : type === 'CUSTOMER' ? 'btnCustomer' : 'btnSupplier';
            document.getElementById(activeBtn).classList.add('bg-surface-highlight', 'text-white');
            document.getElementById(activeBtn).classList.remove('text-text-muted');
            
            loadContacts();
        }

        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Kontak';
            document.getElementById('contactForm').reset();
            document.getElementById('contactId').value = '';
            document.getElementById('contactModal').classList.remove('hidden');
        }

        function editContact(id) {
            const contact = contacts.find(c => c.id === id);
            if (!contact) return;

            document.getElementById('modalTitle').textContent = 'Edit Kontak';
            document.getElementById('contactId').value = contact.id;
            document.getElementById('name').value = contact.name;
            document.getElementById('type').value = contact.type;
            document.getElementById('phone').value = contact.phone || '';
            document.getElementById('email').value = contact.email || '';
            document.getElementById('address').value = contact.address || '';
            document.getElementById('contactModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('contactModal').classList.add('hidden');
        }

        async function showContactDetail(id) {
            document.getElementById('detailContent').innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <span class="material-symbols-outlined animate-spin text-3xl text-primary">progress_activity</span>
                </div>
            `;
            document.getElementById('detailModal').classList.remove('hidden');

            try {
                const response = await fetch(`/contacts/${id}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                
                if (data.success) {
                    renderContactDetail(data.data);
                } else {
                    document.getElementById('detailContent').innerHTML = `
                        <div class="text-center text-accent-red py-8">Gagal memuat data</div>
                    `;
                }
            } catch (error) {
                document.getElementById('detailContent').innerHTML = `
                    <div class="text-center text-accent-red py-8">Terjadi kesalahan</div>
                `;
            }
        }

        function renderContactDetail(contact) {
            document.getElementById('detailTitle').textContent = contact.name;
            
            const totalAmount = contact.invoices_sum_total ? parseFloat(contact.invoices_sum_total).toLocaleString('id-ID') : '0';
            
            let html = `
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="p-4 rounded-xl bg-surface-dark border border-border-dark">
                        <div class="text-text-muted text-sm mb-1">Tipe</div>
                        <div class="text-white font-bold">${getTypeLabel(contact.type)}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-surface-dark border border-border-dark">
                        <div class="text-text-muted text-sm mb-1">Total Transaksi</div>
                        <div class="text-white font-bold">Rp ${totalAmount}</div>
                    </div>
                    ${contact.phone ? `
                    <div class="p-4 rounded-xl bg-surface-dark border border-border-dark">
                        <div class="text-text-muted text-sm mb-1">Telepon</div>
                        <div class="text-white">${contact.phone}</div>
                    </div>
                    ` : ''}
                    ${contact.email ? `
                    <div class="p-4 rounded-xl bg-surface-dark border border-border-dark">
                        <div class="text-text-muted text-sm mb-1">Email</div>
                        <div class="text-white">${contact.email}</div>
                    </div>
                    ` : ''}
                </div>
                
                <h4 class="text-white font-bold mb-4">Riwayat Transaksi (${contact.invoices?.length || 0})</h4>
            `;

            if (contact.invoices && contact.invoices.length > 0) {
                html += `
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border-dark">
                                    <th class="p-3 text-left text-xs font-bold text-text-muted uppercase">Tanggal</th>
                                    <th class="p-3 text-left text-xs font-bold text-text-muted uppercase">No. Invoice</th>
                                    <th class="p-3 text-left text-xs font-bold text-text-muted uppercase">Tipe</th>
                                    <th class="p-3 text-right text-xs font-bold text-text-muted uppercase">Total</th>
                                    <th class="p-3 text-center text-xs font-bold text-text-muted uppercase">Status</th>
                                    <th class="p-3 w-16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                ${contact.invoices.map(inv => `
                                    <tr class="border-b border-border-dark/50 hover:bg-surface-dark/50">
                                        <td class="p-3 text-white">${new Date(inv.date).toLocaleDateString('id-ID')}</td>
                                        <td class="p-3 text-white font-mono">${inv.invoice_number}</td>
                                        <td class="p-3">
                                            <span class="px-2 py-1 rounded text-xs font-medium ${inv.type === 'Sales' ? 'bg-primary/20 text-primary' : 'bg-orange-500/20 text-orange-400'}">
                                                ${inv.type === 'Sales' ? 'Penjualan' : 'Pembelian'}
                                            </span>
                                        </td>
                                        <td class="p-3 text-right text-white font-mono">Rp ${parseFloat(inv.total).toLocaleString('id-ID')}</td>
                                        <td class="p-3 text-center">
                                            <span class="px-2 py-1 rounded text-xs font-medium ${inv.status === 'Paid' ? 'bg-green-500/20 text-green-400' : inv.status === 'Posted' ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-500/20 text-gray-400'}">
                                                ${inv.status}
                                            </span>
                                        </td>
                                        <td class="p-3">
                                            <button onclick="deleteInvoice('${inv.type === 'Sales' ? 'sales' : 'purchases'}', ${inv.id})" 
                                                    class="text-text-muted hover:text-accent-red transition" 
                                                    title="Hapus">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html += `
                    <div class="py-8 text-center text-text-muted">
                        <span class="material-symbols-outlined text-4xl mb-2">receipt_long</span>
                        <p>Belum ada transaksi</p>
                    </div>
                `;
            }

            document.getElementById('detailContent').innerHTML = html;
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        async function deleteInvoice(type, id) {
            if (!confirm('Yakin ingin menghapus transaksi ini? Stok persediaan akan dikembalikan.')) {
                return;
            }

            try {
                const response = await fetch(`/${type}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                if (result.success) {
                    alert(result.message);
                    // Refresh the detail modal
                    const contactId = contacts.find(c => c.invoices?.some(i => i.id === id))?.id;
                    if (contactId) {
                        showContactDetail(contactId);
                    }
                    loadContacts();
                } else {
                    alert(result.message || 'Gagal menghapus');
                }
            } catch (error) {
                alert('Terjadi kesalahan');
            }
        }

        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('contactId').value;
            const isEdit = !!id;
            
            const data = {
                name: document.getElementById('name').value,
                type: document.getElementById('type').value,
                phone: document.getElementById('phone').value,
                email: document.getElementById('email').value,
                address: document.getElementById('address').value,
            };

            const response = await fetch(isEdit ? `/contacts/${id}` : '/contacts', {
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
                loadContacts();
            } else {
                alert(result.message || 'Terjadi kesalahan');
            }
        });

        document.getElementById('searchInput').addEventListener('input', renderGrid);

        // Initial load
        loadContacts();
    </script>
    @endpush
</x-app-layout>
