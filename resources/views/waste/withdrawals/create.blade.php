<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('waste.index') }}" class="text-white hover:text-primary transition flex-shrink-0">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Penarikan Tabungan</h2>
                <p class="text-text-muted text-sm mt-1">Cairkan tabungan sampah nasabah menjadi uang tunai</p>
            </div>
        </div>
    </x-slot>

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 p-8">
            <form action="{{ route('waste.withdrawals.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Nasabah</label>
                        <select name="waste_collector_id" id="waste_collector_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                            <option value="">Pilih Nasabah</option>
                            @foreach($collectors as $collector)
                                <option value="{{ $collector->id }}" data-balance="{{ $collector->balance }}" {{ request('collector_id') == $collector->id ? 'selected' : '' }}>
                                    {{ $collector->collector_number }} - {{ $collector->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-primary text-[10px] font-bold mt-1" id="balance-hint"></small>
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Tanggal Penarikan</label>
                        <input type="date" name="date" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Jumlah Penarikan (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-text-muted text-sm">Rp</span>
                            <input type="number" name="amount" id="amount" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none pl-12" placeholder="0" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Sumber Dana (Kas/Bank)</label>
                        <select name="cash_account_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                            <option value="">Pilih Akun Kas/Bank</option>
                            @foreach($accounts->filter(fn($a) => str_starts_with($a->code, '1.1.1') || str_starts_with($a->code, '1.1.2')) as $account)
                                <option value="{{ $account->id }}" {{ auth()->user()->company->waste_cash_account_id == $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-sm text-text-muted mb-2">Catatan (Opsional)</label>
                    <textarea name="note" rows="3" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" placeholder="Keterangan tambahan..."></textarea>
                </div>

                <div class="flex gap-4">
                    <x-btn href="{{ route('waste.index') }}" type="secondary" class="flex-1 justify-center py-4">Batal</x-btn>
                    <x-btn type="primary" class="flex-1 justify-center py-4">Konfirmasi Penarikan</x-btn>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const collectorSelect = document.getElementById('waste_collector_id');
            const balanceHint = document.getElementById('balance-hint');

            function updateBalanceHint() {
                const selected = collectorSelect.options[collectorSelect.selectedIndex];
                if (selected && selected.value) {
                    const balance = parseFloat(selected.getAttribute('data-balance'));
                    balanceHint.textContent = 'Saldo tersedia: Rp ' + new Intl.NumberFormat('id-ID').format(balance);
                } else {
                    balanceHint.textContent = '';
                }
            }

            collectorSelect.addEventListener('change', updateBalanceHint);
            if (collectorSelect.value) updateBalanceHint();
        });
    </script>
    @endpush
</x-app-layout>
