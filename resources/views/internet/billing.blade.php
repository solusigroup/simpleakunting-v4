<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Tagihan Internet</h2>
                <p class="text-text-muted text-sm mt-1">Kelola tagihan bulanan pelanggan internet</p>
            </div>
            <div class="flex gap-2">
                <x-btn type="primary" onclick="openGenerateModal()">
                    <span class="material-symbols-outlined text-xl">receipt_long</span>
                    Generate Billing
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Period Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="text-text-muted text-sm">Total Tagihan</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $periodStats->total_bills ?? 0 }}</div>
        </div>
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="text-text-muted text-sm">Jumlah Tagihan</div>
            <div class="text-2xl font-bold text-white mt-1">Rp {{ number_format($periodStats->total_amount ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="text-text-muted text-sm">Sudah Dibayar</div>
            <div class="text-2xl font-bold text-primary mt-1">Rp {{ number_format($periodStats->total_paid ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="p-5 rounded-2xl border border-border-dark bg-surface-dark/30">
            <div class="text-text-muted text-sm">Belum Dibayar</div>
            <div class="text-2xl font-bold text-orange-400 mt-1">Rp {{ number_format($periodStats->total_outstanding ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="flex items-center gap-4 mb-6 flex-wrap">
        <select name="month" class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-white text-sm">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$m] }}</option>
            @endfor
        </select>
        <input type="number" name="year" value="{{ $year }}" min="2020" max="2099" class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-white text-sm w-24">
        <select name="status" class="px-4 py-2 rounded-xl bg-surface-dark border border-border-dark text-white text-sm">
            <option value="">Semua Status</option>
            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Sebagian</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Jatuh Tempo</option>
        </select>
        <div class="flex items-center gap-2 px-4 py-2 rounded-xl border border-border-dark bg-surface-dark/30 min-w-[200px] flex-1 md:flex-initial">
            <span class="material-symbols-outlined text-text-muted text-lg">search</span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / ID pelanggan..." 
                   class="bg-transparent border-0 text-white text-sm p-0 focus:ring-0 placeholder-text-muted w-full">
        </div>
        <x-btn type="primary">
            <span class="material-symbols-outlined text-xl">filter_alt</span> Filter
        </x-btn>
    </form>

    <!-- Billing Table -->
    <div class="rounded-2xl border border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-surface-dark/50">
                        <th class="p-4 text-left text-xs font-bold text-text-muted uppercase">No. Tagihan</th>
                        <th class="p-4 text-left text-xs font-bold text-text-muted uppercase">Pelanggan</th>
                        <th class="p-4 text-left text-xs font-bold text-text-muted uppercase">Periode</th>
                        <th class="p-4 text-right text-xs font-bold text-text-muted uppercase">Tagihan</th>
                        <th class="p-4 text-right text-xs font-bold text-text-muted uppercase">Dibayar</th>
                        <th class="p-4 text-right text-xs font-bold text-text-muted uppercase">Sisa</th>
                        <th class="p-4 text-left text-xs font-bold text-text-muted uppercase">Jatuh Tempo</th>
                        <th class="p-4 text-center text-xs font-bold text-text-muted uppercase">Status</th>
                        <th class="p-4 text-center text-xs font-bold text-text-muted uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($billings as $billing)
                    <tr class="border-t border-border-dark/50 hover:bg-surface-dark/30 transition">
                        <td class="p-4 text-white font-mono text-sm">{{ $billing->billing_number }}</td>
                        <td class="p-4">
                            <div class="text-white font-medium">{{ $billing->customer->name ?? '-' }}</div>
                            <div class="text-text-muted text-xs">{{ $billing->customer->customer_id ?? '' }} • {{ $billing->customer->package_name ?? '' }}</div>
                        </td>
                        <td class="p-4 text-white">{{ $billing->period_label }}</td>
                        <td class="p-4 text-right text-white font-mono">Rp {{ number_format($billing->amount, 0, ',', '.') }}</td>
                        <td class="p-4 text-right text-primary font-mono">Rp {{ number_format($billing->paid_amount, 0, ',', '.') }}</td>
                        <td class="p-4 text-right font-mono {{ $billing->remaining_amount > 0 ? 'text-orange-400' : 'text-primary' }}">
                            Rp {{ number_format($billing->remaining_amount, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-white text-sm">{{ $billing->due_date->format('d/m/Y') }}</td>
                        <td class="p-4 text-center">
                            @if($billing->status == 'paid')
                                <span class="px-2 py-1 rounded text-xs font-medium bg-primary/20 text-primary">Lunas</span>
                            @elseif($billing->status == 'partial')
                                <span class="px-2 py-1 rounded text-xs font-medium bg-blue-500/20 text-blue-400">Sebagian</span>
                            @elseif($billing->status == 'overdue')
                                <span class="px-2 py-1 rounded text-xs font-medium bg-red-500/20 text-red-400">Jatuh Tempo</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs font-medium bg-orange-500/20 text-orange-400">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if(!$billing->isPaid())
                            <button onclick="openPaymentModal({{ $billing->id }}, '{{ $billing->billing_number }}', '{{ $billing->customer->name ?? '' }}', {{ $billing->remaining_amount }})" 
                                    class="px-3 py-1.5 rounded-lg bg-primary/20 text-primary text-xs font-medium hover:bg-primary/30 transition">
                                <span class="material-symbols-outlined text-sm align-middle">payments</span> Bayar
                            </button>
                            @else
                            <span class="text-text-muted text-xs">✓ Lunas</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-12 text-center text-text-muted">
                            <span class="material-symbols-outlined text-5xl mb-3">receipt_long</span>
                            <p>Belum ada tagihan untuk periode ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $billings->links() }}

    <!-- Generate Billing Modal -->
    <div id="generateModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeGenerateModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-md">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Generate Billing Bulanan</h3>
                    <button onclick="closeGenerateModal()" class="text-text-muted hover:text-white"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form id="generateForm" class="p-6 space-y-4">
                    <div class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-300 text-sm">
                        <span class="material-symbols-outlined text-sm align-middle">info</span>
                        Sistem akan membuat tagihan untuk semua pelanggan aktif dan otomatis membuat jurnal Piutang.
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Bulan</label>
                            <select id="genMonth" name="month" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][$m] }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-text-muted mb-2">Tahun</label>
                            <input type="number" id="genYear" name="year" value="{{ now()->year }}" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <x-btn type="secondary" type="button" onclick="closeGenerateModal()">Batal</x-btn>
                        <x-btn type="primary" type="submit">Generate Billing</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closePaymentModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-md">
                <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Catat Pembayaran</h3>
                    <button onclick="closePaymentModal()" class="text-text-muted hover:text-white"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form id="paymentForm" class="p-6 space-y-4">
                    <input type="hidden" id="payBillingId">
                    <div class="p-4 rounded-xl bg-surface-dark border border-border-dark">
                        <div class="text-text-muted text-sm">Tagihan</div>
                        <div class="text-white font-bold" id="payBillingInfo"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Jumlah Bayar (Rp) *</label>
                        <input type="number" id="payAmount" required min="1" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white font-mono focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Tanggal Bayar *</label>
                        <input type="date" id="payDate" required class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Metode Pembayaran</label>
                        <select id="payMethod" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Akun Kas/Bank Tujuan *</label>
                        <select id="payCashAccount" required class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            @foreach($cashBankAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Catatan</label>
                        <textarea id="payNotes" rows="2" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary resize-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <x-btn type="secondary" type="button" onclick="closePaymentModal()">Batal</x-btn>
                        <x-btn type="primary" type="submit">Simpan Pembayaran</x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Move functions to top level for global availability
        function openGenerateModal() {
            const modal = document.getElementById('generateModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeGenerateModal() {
            const modal = document.getElementById('generateModal');
            if (modal) modal.classList.add('hidden');
        }
        function openPaymentModal(billingId, billingNumber, customerName, remaining) {
            const modal = document.getElementById('paymentModal');
            if (!modal) return;
            document.getElementById('payBillingId').value = billingId;
            document.getElementById('payBillingInfo').textContent = `${billingNumber} - ${customerName}`;
            document.getElementById('payAmount').value = remaining;
            document.getElementById('payAmount').max = remaining;
            document.getElementById('payDate').value = new Date().toISOString().split('T')[0];
            modal.classList.remove('hidden');
        }
        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            if (modal) modal.classList.add('hidden');
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfMeta ? csrfMeta.content : '';

            // Generate Billing Form
            const genForm = document.getElementById('generateForm');
            if (genForm) {
                genForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const btn = this.querySelector('button[type="submit"]');
                    const initialText = btn ? btn.textContent : '';
                    if (btn) { btn.disabled = true; btn.textContent = 'Generating...'; }
                    
                    try {
                        const response = await fetch('{{ route('internet.billing.generate') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ 
                                month: document.getElementById('genMonth').value, 
                                year: document.getElementById('genYear').value 
                            })
                        });
                        const data = await response.json();
                        alert(data.message);
                        if (data.success) location.reload();
                    } catch (err) { alert('Terjadi kesalahan: ' + err.message); }
                    
                    if (btn) { btn.disabled = false; btn.textContent = initialText; }
                });
            }

            // Payment Form
            const payForm = document.getElementById('paymentForm');
            if (payForm) {
                payForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const btn = this.querySelector('button[type="submit"]');
                    const initialText = btn ? btn.textContent : '';
                    if (btn) { btn.disabled = true; btn.textContent = 'Menyimpan...'; }
                    
                    const billingId = document.getElementById('payBillingId').value;
                    try {
                        const response = await fetch(`/internet/billing/${billingId}/pay`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({
                                amount: document.getElementById('payAmount').value,
                                payment_date: document.getElementById('payDate').value,
                                payment_method: document.getElementById('payMethod').value,
                                cash_bank_account_id: document.getElementById('payCashAccount').value,
                                notes: document.getElementById('payNotes').value,
                            })
                        });
                        const result = await response.json();
                        alert(result.message);
                        if (result.success) location.reload();
                    } catch (err) { alert('Terjadi kesalahan: ' + err.message); }
                    
                    if (btn) { btn.disabled = false; btn.textContent = initialText; }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
