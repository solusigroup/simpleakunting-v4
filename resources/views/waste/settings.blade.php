<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('waste.index') }}" class="text-white hover:text-primary transition flex-shrink-0">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Pengaturan Akuntansi</h2>
                <p class="text-text-muted text-sm mt-1">Hubungkan modul Bank Sampah dengan Bagan Akun (COA)</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-300">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 p-8">
            <form action="{{ route('waste.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-8 border-b border-border-dark pb-4">
                    <h3 class="text-lg font-bold text-white">Pemetaan Akun Otomatis</h3>
                    <p class="text-text-muted text-sm">Sistem akan memposting jurnal secara otomatis menggunakan akun-akun di bawah ini.</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Akun Persediaan Sampah (Asset)</label>
                        <select name="waste_inventory_account_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                            <option value="">Pilih Akun Persediaan</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $company->waste_inventory_account_id == $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-text-muted mt-1 italic">Mencatat nilai stok sampah fisik.</p>
                    </div>

                    <div>
                        <label class="block text-sm text-text-muted mb-2">Akun Utang Tabungan Sampah (Liability)</label>
                        <select name="waste_liability_account_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                            <option value="">Pilih Akun Utang</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $company->waste_liability_account_id == $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-text-muted mt-1 italic">Mencatat kewajiban ke nasabah.</p>
                    </div>

                    <div>
                        <label class="block text-sm text-text-muted mb-2">Akun Pendapatan Penjualan Sampah (Revenue)</label>
                        <select name="waste_revenue_account_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                            <option value="">Pilih Akun Pendapatan</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $company->waste_revenue_account_id == $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-text-muted mb-2">Akun HPP Sampah (Expense)</label>
                        <select name="waste_cogs_account_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                            <option value="">Pilih Akun Beban/HPP</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $company->waste_cogs_account_id == $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-text-muted mb-2">Default Akun Kas Penarikan (Asset)</label>
                        <select name="waste_cash_account_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none">
                            <option value="">Pilih Akun Kas</option>
                            @foreach($accounts->filter(fn($a) => str_starts_with($a->code, '1.1.1') || str_starts_with($a->code, '1.1.2')) as $account)
                                <option value="{{ $account->id }}" {{ $company->waste_cash_account_id == $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-10">
                    <x-btn type="primary" class="w-full justify-center py-4 text-lg">Simpan Pengaturan Akun</x-btn>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
