<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Pengaturan Internet</h2>
                <p class="text-text-muted text-sm mt-1">Konfigurasi akun akuntansi untuk modul internet</p>
            </div>
            <a href="{{ route('internet.index') }}" class="text-text-muted hover:text-white transition">
                <span class="material-symbols-outlined">arrow_back</span> Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-surface-dark/30 rounded-2xl border border-border-dark p-6">
            <h3 class="text-lg font-bold text-white mb-6">Pemetaan Akun (COA)</h3>
            
            <form id="settingsForm" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Akun Piutang Internet *</label>
                    <p class="text-xs text-text-muted mb-3">Akun yang akan didebit saat tagihan digenerate (e.g. Piutang Pelanggan Internet)</p>
                    <select id="receivableCoa" required class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        <option value="">Pilih Akun Piutang</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $company->internet_receivable_module_coa_id == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Akun Pendapatan Internet *</label>
                    <p class="text-xs text-text-muted mb-3">Akun yang akan dikredit saat tagihan digenerate (e.g. Pendapatan Jasa Internet)</p>
                    <select id="revenueCoa" required class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        <option value="">Pilih Akun Pendapatan</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $company->internet_revenue_module_coa_id == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 border-t border-border-dark">
                    <x-btn type="primary" type="submit" id="saveBtn">
                        <span class="material-symbols-outlined">save</span> Simpan Perubahan
                    </x-btn>
                </div>
            </form>
        </div>

        <div class="mt-6 p-4 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-300 text-sm">
            <div class="flex gap-3">
                <span class="material-symbols-outlined">info</span>
                <div>
                    <p class="font-bold mb-1">Penting</p>
                    <p>Pastikan Anda sudah memilih kedua akun di atas sebelum melakukan "Generate Billing". Perubahan pengaturan ini hanya akan berdampak pada jurnal yang dibuat setelah pengaturan disimpan.</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('settingsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            try {
                const res = await fetch('{{ route("internet.settings.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        internet_receivable_module_coa_id: document.getElementById('receivableCoa').value,
                        internet_revenue_module_coa_id: document.getElementById('revenueCoa').value,
                    })
                });

                const data = await res.json();
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            } catch (err) {
                alert('Gagal menyimpan pengaturan: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Simpan Perubahan';
            }
        });
    </script>
    @endpush
</x-app-layout>
