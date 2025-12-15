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
                <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition group">
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
                        <button onclick="editContact(${contact.id})" class="text-text-muted hover:text-primary opacity-0 group-hover:opacity-100 transition">
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
