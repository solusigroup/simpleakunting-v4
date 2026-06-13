<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Pelanggan Internet</h2>
                <p class="text-text-muted text-sm mt-1">Kelola data pelanggan layanan internet</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="secondary" onclick="window.location.href='{{ route('internet.settings') }}'">
                    <span class="material-symbols-outlined text-xl">settings</span>
                    Pengaturan
                </x-btn>
                <x-btn type="secondary" onclick="window.location.href='{{ route('internet.import.form') }}'">
                    <span class="material-symbols-outlined text-xl">upload</span>
                    Import Excel
                </x-btn>
                <x-btn type="primary" onclick="openCreateModal()">
                    <span class="material-symbols-outlined text-xl">person_add</span>
                    Tambah Pelanggan
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="text-text-muted text-sm">Total Pelanggan</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="text-text-muted text-sm">Pelanggan Aktif</div>
            <div class="text-2xl font-bold text-primary mt-1">{{ $stats['active'] }}</div>
        </div>
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="text-text-muted text-sm">Pendapatan Bulanan</div>
            <div class="text-2xl font-bold text-white mt-1">Rp {{ number_format($stats['total_monthly_revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="text-text-muted text-sm">Total Piutang</div>
            <div class="text-2xl font-bold {{ $stats['total_outstanding'] > 0 ? 'text-orange-400' : 'text-primary' }} mt-1">Rp {{ number_format($stats['total_outstanding'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Filter -->
    <div class="flex items-center gap-4 mb-6 flex-wrap">
        <div class="flex items-center gap-2">
            <a href="{{ route('internet.index') }}" class="px-4 py-2 rounded-full text-sm font-medium transition border border-border-dark {{ !request('status') ? 'bg-surface-highlight text-white' : 'text-text-muted hover:bg-surface-highlight' }}">Semua</a>
            <a href="{{ route('internet.index', ['status' => 'active']) }}" class="px-4 py-2 rounded-full text-sm font-medium transition border border-border-dark {{ request('status') == 'active' ? 'bg-primary/20 text-primary' : 'text-text-muted hover:bg-surface-highlight' }}">Aktif</a>
            <a href="{{ route('internet.index', ['status' => 'suspended']) }}" class="px-4 py-2 rounded-full text-sm font-medium transition border border-border-dark {{ request('status') == 'suspended' ? 'bg-orange-500/20 text-orange-400' : 'text-text-muted hover:bg-surface-highlight' }}">Suspended</a>
            <a href="{{ route('internet.index', ['status' => 'terminated']) }}" class="px-4 py-2 rounded-full text-sm font-medium transition border border-border-dark {{ request('status') == 'terminated' ? 'bg-red-500/20 text-red-400' : 'text-text-muted hover:bg-surface-highlight' }}">Terminated</a>
        </div>
        <form method="GET" class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30 ml-auto">
            <span class="material-symbols-outlined text-text-muted">search</span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pelanggan..." 
                   class="bg-transparent border-0 text-white text-sm focus:ring-0 placeholder-text-muted w-48">
        </form>
    </div>

    <!-- Customers Table -->
    <div class="rounded-2xl border border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-surface-dark/50">
                        <th class="p-4 text-left text-xs font-bold text-text-muted uppercase">ID</th>
                        <th class="p-4 text-left text-xs font-bold text-text-muted uppercase">Nama</th>
                        <th class="p-4 text-left text-xs font-bold text-text-muted uppercase">Paket</th>
                        <th class="p-4 text-right text-xs font-bold text-text-muted uppercase">Tarif/Bulan</th>
                        <th class="p-4 text-right text-xs font-bold text-text-muted uppercase">Piutang</th>
                        <th class="p-4 text-center text-xs font-bold text-text-muted uppercase">Status</th>
                        <th class="p-4 text-center text-xs font-bold text-text-muted uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr class="border-t border-border-dark/50 hover:bg-surface-dark/30 transition">
                        <td class="p-4 text-white font-mono text-sm">{{ $customer->customer_id }}</td>
                        <td class="p-4">
                            <div class="text-white font-medium">{{ $customer->name }}</div>
                            <div class="text-text-muted text-xs">{{ $customer->phone ?? $customer->email ?? '-' }}</div>
                        </td>
                        <td class="p-4 text-white">{{ $customer->package_name }}</td>
                        <td class="p-4 text-right text-white font-mono">Rp {{ number_format($customer->monthly_rate, 0, ',', '.') }}</td>
                        <td class="p-4 text-right font-mono {{ $customer->outstanding_balance > 0 ? 'text-orange-400' : 'text-primary' }}">
                            Rp {{ number_format($customer->outstanding_balance, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            @if($customer->status == 'active')
                                <span class="px-2 py-1 rounded text-xs font-medium bg-primary/20 text-primary">Aktif</span>
                            @elseif($customer->status == 'suspended')
                                <span class="px-2 py-1 rounded text-xs font-medium bg-orange-500/20 text-orange-400">Suspended</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs font-medium bg-red-500/20 text-red-400">Terminated</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('internet.show', $customer->id) }}" class="text-text-muted hover:text-primary transition" title="Detail">
                                    <span class="material-symbols-outlined text-xl">visibility</span>
                                </a>
                                <button onclick="editCustomer({{ $customer->id }}, {{ json_encode($customer) }})" class="text-text-muted hover:text-blue-400 transition" title="Edit">
                                    <span class="material-symbols-outlined text-xl">edit</span>
                                </button>
                                <button onclick="deleteCustomer({{ $customer->id }})" class="text-text-muted hover:text-red-400 transition" title="Hapus">
                                    <span class="material-symbols-outlined text-xl">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-5xl mb-3">wifi_off</span>
                            <p>Belum ada pelanggan internet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $customers->links() }}

    <!-- Create/Edit Modal -->
    <div id="customerModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-lg">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white" id="modalTitle">Tambah Pelanggan</h3>
                    <button onclick="closeModal()" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form id="customerForm" class="p-6 space-y-4">
                    <input type="hidden" id="customerId">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Nama Pelanggan *</label>
                        <input type="text" id="custName" required class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">No. HP</label>
                            <input type="text" id="custPhone" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Email</label>
                            <input type="email" id="custEmail" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Alamat</label>
                        <textarea id="custAddress" rows="2" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Nama Paket *</label>
                            <input type="text" id="custPackage" required placeholder="e.g. 10 Mbps" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Tarif Bulanan (Rp) *</label>
                            <input type="number" id="custRate" required min="1000" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Tanggal Tagih (1-28) *</label>
                            <input type="number" id="custBillingDate" required min="1" max="28" value="1" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Tanggal Aktivasi</label>
                            <input type="date" id="custActivated" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div id="statusField" class="hidden">
                        <label class="block text-sm font-medium text-text-muted mb-2">Status</label>
                        <select id="custStatus" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="active">Aktif</option>
                            <option value="suspended">Suspended</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Catatan</label>
                        <textarea id="custNotes" rows="2" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary resize-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <x-btn type="secondary" type="button" onclick="closeModal()">Batal</x-btn>
                        <x-btn type="primary" type="submit">Simpan</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Move functions to top level for global availability
        function openCreateModal() {
            const modal = document.getElementById('customerModal');
            if (!modal) return;
            document.getElementById('modalTitle').textContent = 'Tambah Pelanggan';
            document.getElementById('customerForm').reset();
            document.getElementById('customerId').value = '';
            document.getElementById('statusField').classList.add('hidden');
            const activatedInput = document.getElementById('custActivated');
            if (activatedInput) activatedInput.value = new Date().toISOString().split('T')[0];
            modal.classList.remove('hidden');
        }

        function editCustomer(id, data) {
            const modal = document.getElementById('customerModal');
            if (!modal) return;
            document.getElementById('modalTitle').textContent = 'Edit Pelanggan';
            document.getElementById('customerId').value = id;
            document.getElementById('custName').value = data.name || '';
            document.getElementById('custPhone').value = data.phone || '';
            document.getElementById('custEmail').value = data.email || '';
            document.getElementById('custAddress').value = data.address || '';
            document.getElementById('custPackage').value = data.package_name || '';
            document.getElementById('custRate').value = data.monthly_rate || 0;
            document.getElementById('custBillingDate').value = data.billing_date || 1;
            document.getElementById('custStatus').value = data.status || 'active';
            document.getElementById('custNotes').value = data.notes || '';
            document.getElementById('statusField').classList.remove('hidden');
            modal.classList.remove('hidden');
        }

        function closeModal() {
            const modal = document.getElementById('customerModal');
            if (modal) modal.classList.add('hidden');
        }

        async function deleteCustomer(id) {
            if (!confirm('Yakin ingin menghapus pelanggan ini?')) return;
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : '';
            try {
                const response = await fetch(`/internet/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                });
                const result = await response.json();
                alert(result.message);
                if (result.success) location.reload();
            } catch (err) { alert('Terjadi kesalahan: ' + err.message); }
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : '';

            const custForm = document.getElementById('customerForm');
            if (custForm) {
                custForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const btn = this.querySelector('button');
                    const initialText = btn ? btn.textContent : '';
                    if (btn) { btn.disabled = true; btn.textContent = 'Menyimpan...'; }

                    const id = document.getElementById('customerId').value;
                    const isEdit = !!id;

                    const payload = {
                        name: document.getElementById('custName').value,
                        phone: document.getElementById('custPhone').value,
                        email: document.getElementById('custEmail').value,
                        address: document.getElementById('custAddress').value,
                        package_name: document.getElementById('custPackage').value,
                        monthly_rate: document.getElementById('custRate').value,
                        billing_date: document.getElementById('custBillingDate').value,
                        activated_at: document.getElementById('custActivated').value,
                        notes: document.getElementById('custNotes').value,
                    };
                    if (isEdit) payload.status = document.getElementById('custStatus').value;

                    try {
                        const response = await fetch(isEdit ? `/internet/${id}` : '/internet', {
                            method: isEdit ? 'PUT' : 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify(payload)
                        });
                        const result = await response.json();
                        if (result.success) {
                            closeModal();
                            location.reload();
                        } else {
                            alert(result.message || 'Terjadi kesalahan');
                        }
                    } catch (err) { alert('Terjadi kesalahan: ' + err.message); }
                    
                    if (btn) { btn.disabled = false; btn.textContent = initialText; }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
