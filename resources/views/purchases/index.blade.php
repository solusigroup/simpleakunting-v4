<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Pembelian</h2>
                <p class="text-text-muted text-sm mt-1">Kelola tagihan dari supplier</p>
            </div>
            <x-btn type="primary" onclick="window.location.href='{{ route('purchases.index') }}/create'">
                <span class="material-symbols-outlined text-xl">add</span>
                Tagihan Baru
            </x-btn>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="flex items-center gap-4 mb-6">
        <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30">
            <span class="material-symbols-outlined text-text-muted">calendar_today</span>
            <input type="date" id="dateStart" class="bg-transparent border-0 text-white text-sm focus:ring-0">
            <span class="text-text-muted">-</span>
            <input type="date" id="dateEnd" class="bg-transparent border-0 text-white text-sm focus:ring-0">
        </div>
        <div class="flex items-center gap-2 px-4 py-2 rounded-full border border-border-dark bg-surface-dark/30 ml-auto">
            <span class="material-symbols-outlined text-text-muted">search</span>
            <input type="text" id="searchInput" placeholder="Cari tagihan..." 
                   class="bg-transparent border-0 text-white text-sm focus:ring-0 placeholder-text-muted w-48">
        </div>
    </div>

    <!-- Purchases Table -->
    <div class="rounded-2xl border border-border-dark overflow-hidden bg-surface-dark/30">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-border-dark bg-surface-dark">
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">No. Tagihan</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Tanggal</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Supplier</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Jatuh Tempo</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Total</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Status</th>
                    <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm" id="purchasesBody">
                <tr>
                    <td colspan="7" class="p-8 text-center text-text-muted">
                        <span class="material-symbols-outlined animate-spin text-3xl">progress_activity</span>
                        <p class="mt-2">Memuat data...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @push('scripts')
    <script>
        async function loadPurchases() {
            const start = document.getElementById('dateStart').value;
            const end = document.getElementById('dateEnd').value;
            let url = '/purchases?';
            if (start) url += `date_start=${start}&`;
            if (end) url += `date_end=${end}`;
            
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            renderTable(result.data?.data || []);
        }

        function renderTable(purchases) {
            const tbody = document.getElementById('purchasesBody');
            
            if (purchases.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="p-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-5xl mb-3">shopping_cart</span>
                            <p>Belum ada tagihan pembelian</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = purchases.map(purchase => `
                <tr class="border-b border-border-dark/50 hover:bg-surface-highlight/30">
                    <td class="p-4 text-orange-400 font-medium">${purchase.invoice_number}</td>
                    <td class="p-4 text-white">${new Date(purchase.date).toLocaleDateString('id-ID')}</td>
                    <td class="p-4 text-white">${purchase.contact?.name || '-'}</td>
                    <td class="p-4 text-text-muted">${new Date(purchase.due_date).toLocaleDateString('id-ID')}</td>
                    <td class="p-4 text-white text-right font-mono font-bold">Rp ${Number(purchase.total).toLocaleString('id-ID')}</td>
                    <td class="p-4">${getStatusBadge(purchase.status)}</td>
                    <td class="p-4 text-right">
                        <button onclick="viewPurchase(${purchase.id})" class="text-text-muted hover:text-white p-1" title="Lihat">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                        <button onclick="deletePurchase(${purchase.id})" class="text-text-muted hover:text-accent-red p-1" title="Hapus">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function getStatusBadge(status) {
            const configs = {
                'Posted': { class: 'bg-primary/20 text-primary', icon: 'check_circle', label: 'Terposting' },
                'Paid': { class: 'bg-blue-500/20 text-blue-400', icon: 'paid', label: 'Lunas' },
                'Draft': { class: 'bg-gray-500/20 text-gray-400', icon: 'edit', label: 'Draft' },
            };
            const config = configs[status] || configs['Draft'];
            return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold ${config.class}">
                <span class="material-symbols-outlined text-[14px]">${config.icon}</span>${config.label}
            </span>`;
        }

        function viewPurchase(id) {
            window.location.href = `/purchases/${id}`;
        }

        async function deletePurchase(id) {
            if (!confirm('Yakin ingin menghapus tagihan ini? Stok persediaan akan dikembalikan.')) {
                return;
            }

            try {
                const response = await fetch(`/purchases/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                if (result.success) {
                    alert(result.message);
                    loadPurchases();
                } else {
                    alert(result.message || 'Gagal menghapus');
                }
            } catch (error) {
                alert('Terjadi kesalahan');
            }
        }

        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        document.getElementById('dateStart').value = firstDay.toISOString().split('T')[0];
        document.getElementById('dateEnd').value = today.toISOString().split('T')[0];

        document.getElementById('dateStart').addEventListener('change', loadPurchases);
        document.getElementById('dateEnd').addEventListener('change', loadPurchases);

        loadPurchases();
    </script>
    @endpush
</x-app-layout>
