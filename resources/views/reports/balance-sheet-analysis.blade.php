<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white font-display flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-3xl">troubleshoot</span>
                    Analisa Keseimbangan Neraca
                </h2>
                <p class="text-text-muted text-sm mt-1">Diagnosis Keseimbangan Aset vs Kewajiban + Ekuitas</p>
            </div>
            <div class="flex items-center gap-3">
                <x-btn type="secondary" onclick="window.print()">
                    <span class="material-symbols-outlined text-xl">print</span>
                    Cetak
                </x-btn>
            </div>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="mb-6 p-4 rounded-xl border border-border-dark bg-surface-dark/30">
        <form action="{{ route('reports.balance-sheet-analysis') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm text-text-muted mb-2">Per Tanggal</label>
                <input type="date" name="end_date" value="{{ $report_date }}" 
                       class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
            </div>
            @if(auth()->user()->company?->isBumdesa())
            <div class="flex-1 min-w-[240px]">
                <label class="block text-sm text-text-muted mb-2">Unit Usaha</label>
                <select id="unitFilter" name="unit_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-2 text-white focus:border-primary focus:outline-none">
                    <option value="">Konsolidasi (Semua Unit)</option>
                </select>
            </div>
            @endif
            <x-btn type="primary" type="submit" class="h-10">Jalankan Analisis</x-btn>
        </form>
    </div>

    <!-- Status Overview Card -->
    <div class="p-6 mb-6 rounded-2xl border {{ $is_balanced ? 'border-primary/30 bg-primary/5' : 'border-accent-red/30 bg-accent-red/5' }} flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-3xl {{ $is_balanced ? 'text-primary' : 'text-accent-red' }}">
                    {{ $is_balanced ? 'check_circle' : 'error' }}
                </span>
                <div>
                    <h3 class="text-xl font-bold text-white">
                        Status Neraca: {{ $is_balanced ? 'SEIMBANG' : 'TIDAK SEIMBANG' }}
                    </h3>
                    <p class="text-sm text-text-muted mt-1">
                        {{ $is_balanced 
                            ? 'Laporan posisi keuangan Anda seimbang sempurna. Selisih antara Aktiva dan Pasiva adalah nol.' 
                            : 'Terdeteksi perbedaan nilai antara Aktiva (Aset) dan Pasiva (Kewajiban + Ekuitas). Ikuti langkah pemeriksaan di bawah untuk mencari penyebabnya.' }}
                    </p>
                </div>
            </div>
        </div>
        <div class="flex flex-col items-end bg-surface-dark/45 p-4 rounded-xl border border-border-dark min-w-[220px]">
            <span class="text-xs text-text-muted">Selisih Aktiva & Pasiva</span>
            <span class="text-2xl font-bold {{ $is_balanced ? 'text-primary' : 'text-accent-red' }} font-mono mt-1">
                Rp {{ number_format($difference, 2, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <span class="text-text-muted text-xs uppercase tracking-wider font-semibold">Total Aset (Aktiva)</span>
            <p class="text-2xl font-bold text-white mt-1">Rp {{ number_format($total_assets, 2, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30">
            <span class="text-text-muted text-xs uppercase tracking-wider font-semibold">Total Kewajiban & Ekuitas (Pasiva)</span>
            <p class="text-2xl font-bold text-white mt-1">Rp {{ number_format($total_liabilities + $total_equity, 2, ',', '.') }}</p>
            <div class="flex justify-between text-xs text-text-muted mt-2 border-t border-border-dark/50 pt-2">
                <span>Kewajiban: Rp {{ number_format($total_liabilities, 0, ',', '.') }}</span>
                <span>Ekuitas: Rp {{ number_format($total_equity, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="p-4 rounded-xl border border-border-dark bg-surface-dark/30 flex items-center justify-between">
            <div>
                <span class="text-text-muted text-xs uppercase tracking-wider font-semibold">Kategori Pemeriksaan</span>
                <p class="text-2xl font-bold text-white mt-1">
                    @php
                        $issueCount = ($unbalanced_journals->count() > 0 ? 1 : 0) + 
                                     ($opening_difference > 0.01 ? 1 : 0) + 
                                     ($orphaned_journal_items->count() > 0 ? 1 : 0) + 
                                     ($invalid_account_types->count() > 0 ? 1 : 0);
                    @endphp
                    {{ 4 - $issueCount }} <span class="text-sm font-normal text-text-muted">/ 4 Lolos</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $issueCount === 0 ? 'bg-primary/10 text-primary' : 'bg-yellow-500/10 text-yellow-400' }}">
                <span class="material-symbols-outlined text-2xl">
                    {{ $issueCount === 0 ? 'verified' : 'warning_amber' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Diagnostic Tabs -->
    <div x-data="{ activeTab: 'journals' }" class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
        <!-- Tab Headers -->
        <div class="flex border-b border-border-dark bg-surface-dark/60 overflow-x-auto">
            <button @click="activeTab = 'journals'"
                    :class="activeTab === 'journals' ? 'border-primary text-primary bg-surface-dark/30' : 'border-transparent text-text-muted hover:text-white'"
                    class="px-6 py-4 border-b-2 font-semibold text-sm transition flex items-center gap-2 whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">receipt_long</span>
                Jurnal Tidak Seimbang
                @if($unbalanced_journals->count() > 0)
                    <span class="px-2 py-0.5 text-xs bg-accent-red/20 text-accent-red rounded-full font-bold">
                        {{ $unbalanced_journals->count() }}
                    </span>
                @endif
            </button>
            <button @click="activeTab = 'opening'"
                    :class="activeTab === 'opening' ? 'border-primary text-primary bg-surface-dark/30' : 'border-transparent text-text-muted hover:text-white'"
                    class="px-6 py-4 border-b-2 font-semibold text-sm transition flex items-center gap-2 whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">account_balance</span>
                Saldo Awal COA
                @if($opening_difference > 0.01)
                    <span class="px-2 py-0.5 text-xs bg-accent-red/20 text-accent-red rounded-full font-bold">!</span>
                @endif
            </button>
            <button @click="activeTab = 'orphaned'"
                    :class="activeTab === 'orphaned' ? 'border-primary text-primary bg-surface-dark/30' : 'border-transparent text-text-muted hover:text-white'"
                    class="px-6 py-4 border-b-2 font-semibold text-sm transition flex items-center gap-2 whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">broken_image</span>
                Transaksi Yatim (Orphan)
                @if($orphaned_journal_items->count() > 0)
                    <span class="px-2 py-0.5 text-xs bg-accent-red/20 text-accent-red rounded-full font-bold">
                        {{ $orphaned_journal_items->count() }}
                    </span>
                @endif
            </button>
            <button @click="activeTab = 'coa'"
                    :class="activeTab === 'coa' ? 'border-primary text-primary bg-surface-dark/30' : 'border-transparent text-text-muted hover:text-white'"
                    class="px-6 py-4 border-b-2 font-semibold text-sm transition flex items-center gap-2 whitespace-nowrap">
                <span class="material-symbols-outlined text-sm">settings</span>
                Konfigurasi Tipe Akun
                @if($invalid_account_types->count() > 0)
                    <span class="px-2 py-0.5 text-xs bg-accent-red/20 text-accent-red rounded-full font-bold">
                        {{ $invalid_account_types->count() }}
                    </span>
                @endif
            </button>
        </div>

        <!-- Tab Body -->
        <div class="p-6">
            <!-- Tab 1: Unbalanced Journals -->
            <div x-show="activeTab === 'journals'" x-transition>
                <div class="mb-4">
                    <h4 class="text-white font-bold text-base flex items-center gap-2">
                        Audit Jurnal Umum Terposting
                    </h4>
                    <p class="text-xs text-text-muted mt-1">Pemeriksaan apakah ada jurnal terposting yang memiliki total Debit berbeda dengan total Kredit.</p>
                </div>

                @if($unbalanced_journals->count() === 0)
                    <div class="flex flex-col items-center justify-center p-8 text-center bg-primary/5 rounded-xl border border-primary/20">
                        <span class="material-symbols-outlined text-primary text-4xl mb-2">check_circle</span>
                        <h5 class="text-white font-bold">Semua Jurnal Seimbang</h5>
                        <p class="text-xs text-text-muted mt-1">Tidak ada jurnal terposting yang mengalami ketidakseimbangan debit/kredit.</p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-border-dark">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-dark border-b border-border-dark text-white text-xs font-semibold uppercase">
                                    <th class="p-4">Tanggal</th>
                                    <th class="p-4">Ref / No Jurnal</th>
                                    <th class="p-4">Deskripsi</th>
                                    <th class="p-4 text-right">Debit</th>
                                    <th class="p-4 text-right">Kredit</th>
                                    <th class="p-4 text-right">Selisih</th>
                                    <th class="p-4 text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-dark/50 text-white text-sm">
                                @foreach($unbalanced_journals as $journal)
                                    <tr class="hover:bg-surface-dark/40 transition">
                                        <td class="p-4">{{ $journal->date }}</td>
                                        <td class="p-4 font-mono">{{ $journal->reference }}</td>
                                        <td class="p-4 text-text-muted max-w-xs truncate">{{ $journal->description }}</td>
                                        <td class="p-4 text-right">Rp {{ number_format($journal->total_debit, 2, ',', '.') }}</td>
                                        <td class="p-4 text-right">Rp {{ number_format($journal->total_credit, 2, ',', '.') }}</td>
                                        <td class="p-4 text-right text-accent-red font-mono font-semibold">Rp {{ number_format($journal->difference, 2, ',', '.') }}</td>
                                        <td class="p-4 text-center">
                                            <a href="{{ route('journals.index', ['date_start' => $journal->date, 'date_end' => $journal->date]) }}" 
                                               class="px-3 py-1 bg-surface-highlight hover:bg-primary hover:text-background-dark text-xs font-semibold rounded-lg transition inline-flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs">edit_note</span>
                                                Koreksi Jurnal
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Tab 2: COA Opening Balances -->
            <div x-show="activeTab === 'opening'" x-transition>
                <div class="mb-4">
                    <h4 class="text-white font-bold text-base">Audit Saldo Awal Akun (COA)</h4>
                    <p class="text-xs text-text-muted mt-1">Saldo awal semua akun dengan normal balance DEBIT harus sama nilainya dengan akun normal balance KREDIT.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 rounded-xl bg-surface-dark/50 border border-border-dark">
                        <span class="text-text-muted text-xs">Total Saldo Awal Debit</span>
                        <p class="text-lg font-bold text-white mt-1">Rp {{ number_format($debit_opening_total, 2, ',', '.') }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-surface-dark/50 border border-border-dark">
                        <span class="text-text-muted text-xs">Total Saldo Awal Kredit</span>
                        <p class="text-lg font-bold text-white mt-1">Rp {{ number_format($credit_opening_total, 2, ',', '.') }}</p>
                    </div>
                    <div class="p-4 rounded-xl {{ $opening_difference > 0.01 ? 'bg-accent-red/10 border-accent-red/30 border' : 'bg-surface-dark/50 border-border-dark border' }}">
                        <span class="text-text-muted text-xs">Selisih Saldo Awal</span>
                        <p class="text-lg font-bold {{ $opening_difference > 0.01 ? 'text-accent-red' : 'text-primary' }} mt-1">
                            Rp {{ number_format($opening_difference, 2, ',', '.') }}
                        </p>
                    </div>
                </div>

                @if($opening_difference > 0.01)
                    <div class="mb-6 p-4 rounded-xl bg-accent-red/10 border border-accent-red/30 flex items-center gap-3">
                        <span class="material-symbols-outlined text-accent-red">warning</span>
                        <p class="text-accent-red text-xs font-semibold">
                            Peringatan: Saldo awal tidak seimbang! Perbedaan ini akan langsung menyebabkan Neraca menjadi tidak seimbang. Silakan sesuaikan kembali saldo awal akun-akun Anda di menu Chart of Accounts.
                        </p>
                    </div>
                @endif

                <div class="flex justify-between items-center mb-3">
                    <h5 class="text-white font-bold text-sm">Daftar Akun yang Memiliki Saldo Awal</h5>
                    <a href="{{ route('accounts.index') }}" class="text-xs text-primary hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs">edit</span>
                        Edit Saldo Awal di COA
                    </a>
                </div>

                @if(count($opening_accounts) === 0)
                    <div class="p-8 text-center bg-surface-dark/20 rounded-xl border border-border-dark/50 text-text-muted">
                        Tidak ada akun yang memiliki saldo awal terdaftar.
                    </div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-border-dark">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-dark border-b border-border-dark text-white text-xs font-semibold uppercase">
                                    <th class="p-4">Kode Akun</th>
                                    <th class="p-4">Nama Akun</th>
                                    <th class="p-4">Normal Balance</th>
                                    <th class="p-4 text-right">Saldo Awal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-dark/50 text-white text-sm">
                                @foreach($opening_accounts as $account)
                                    <tr class="hover:bg-surface-dark/40 transition">
                                        <td class="p-4 font-mono">{{ $account['code'] }}</td>
                                        <td class="p-4">{{ $account['name'] }}</td>
                                        <td class="p-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $account['normal_balance'] === 'DEBIT' ? 'bg-blue-500/20 text-blue-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                                                {{ $account['normal_balance'] }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-right">Rp {{ number_format($account['opening_balance'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Tab 3: Orphaned Journal Items -->
            <div x-show="activeTab === 'orphaned'" x-transition>
                <div class="mb-4">
                    <h4 class="text-white font-bold text-base">Audit Transaksi Yatim (Orphaned Journal Items)</h4>
                    <p class="text-xs text-text-muted mt-1">Pemeriksaan apakah ada baris transaksi jurnal yang tidak terhubung dengan akun Chart of Accounts (COA) yang sah.</p>
                </div>

                @if($orphaned_journal_items->count() === 0)
                    <div class="flex flex-col items-center justify-center p-8 text-center bg-primary/5 rounded-xl border border-primary/20">
                        <span class="material-symbols-outlined text-primary text-4xl mb-2">check_circle</span>
                        <h5 class="text-white font-bold">Semua Transaksi Valid</h5>
                        <p class="text-xs text-text-muted mt-1">Tidak ditemukan baris jurnal yang terputus dari COA.</p>
                    </div>
                @else
                    <div class="mb-4 p-4 rounded-xl bg-accent-red/10 border border-accent-red/30 flex items-center gap-3">
                        <span class="material-symbols-outlined text-accent-red">warning</span>
                        <p class="text-accent-red text-xs font-semibold">
                            Ditemukan transaksi yang tidak terasosiasi dengan akun COA yang valid. Transaksi ini tidak dapat masuk ke buku besar manapun sehingga merusak keseimbangan Neraca.
                        </p>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-border-dark">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-dark border-b border-border-dark text-white text-xs font-semibold uppercase">
                                    <th class="p-4">Tanggal Jurnal</th>
                                    <th class="p-4">Ref Jurnal</th>
                                    <th class="p-4">Deskripsi Jurnal</th>
                                    <th class="p-4 text-right">Debit</th>
                                    <th class="p-4 text-right">Kredit</th>
                                    <th class="p-4 text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-dark/50 text-white text-sm">
                                @foreach($orphaned_journal_items as $item)
                                    <tr class="hover:bg-surface-dark/40 transition">
                                        <td class="p-4">{{ $item->journal->date->format('Y-m-d') }}</td>
                                        <td class="p-4 font-mono">{{ $item->journal->reference }}</td>
                                        <td class="p-4 text-text-muted max-w-xs truncate">{{ $item->journal->description }}</td>
                                        <td class="p-4 text-right">Rp {{ number_format($item->debit, 2, ',', '.') }}</td>
                                        <td class="p-4 text-right">Rp {{ number_format($item->credit, 2, ',', '.') }}</td>
                                        <td class="p-4 text-center">
                                            <a href="{{ route('journals.index', ['date_start' => $item->journal->date->format('Y-m-d'), 'date_end' => $item->journal->date->format('Y-m-d')]) }}" 
                                               class="px-3 py-1 bg-surface-highlight hover:bg-primary hover:text-background-dark text-xs font-semibold rounded-lg transition inline-flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs">edit_note</span>
                                                Koreksi Jurnal
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Tab 4: COA Configurations -->
            <div x-show="activeTab === 'coa'" x-transition>
                <div class="mb-4">
                    <h4 class="text-white font-bold text-base">Audit Konfigurasi Tipe Laporan Akun</h4>
                    <p class="text-xs text-text-muted mt-1">Pemeriksaan kebenaran pemetaan tipe akun ke tipe laporan keuangan. Akun Neraca (Aset, Kewajiban, Ekuitas) harus dipetakan ke NERACA, sedangkan Pendapatan dan Beban harus ke LABARUGI.</p>
                </div>

                @if($invalid_account_types->count() === 0)
                    <div class="flex flex-col items-center justify-center p-8 text-center bg-primary/5 rounded-xl border border-primary/20">
                        <span class="material-symbols-outlined text-primary text-4xl mb-2">check_circle</span>
                        <h5 class="text-white font-bold">Semua Tipe Akun Benar</h5>
                        <p class="text-xs text-text-muted mt-1">Semua COA telah dipetakan ke laporan keuangan yang sesuai.</p>
                    </div>
                @else
                    <div class="mb-4 p-4 rounded-xl bg-accent-red/10 border border-accent-red/30 flex items-center gap-3">
                        <span class="material-symbols-outlined text-accent-red">warning</span>
                        <p class="text-accent-red text-xs font-semibold">
                            Peringatan: Ditemukan akun dengan pemetaan tipe laporan yang salah. Hal ini menyebabkan saldo transaksi salah masuk ke laporan keuangan dan menggagalkan penutupan laba periode berjalan.
                        </p>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-border-dark">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-dark border-b border-border-dark text-white text-xs font-semibold uppercase">
                                    <th class="p-4">Kode Akun</th>
                                    <th class="p-4">Nama Akun</th>
                                    <th class="p-4">Tipe Akun</th>
                                    <th class="p-4">Tipe Laporan Saat Ini</th>
                                    <th class="p-4">Seharusnya</th>
                                    <th class="p-4 text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border-dark/50 text-white text-sm">
                                @foreach($invalid_account_types as $acc)
                                    <tr class="hover:bg-surface-dark/40 transition">
                                        <td class="p-4 font-mono text-primary">{{ $acc->code }}</td>
                                        <td class="p-4">{{ $acc->name }}</td>
                                        <td class="p-4">{{ $acc->type }}</td>
                                        <td class="p-4 text-accent-red font-semibold">{{ $acc->report_type }}</td>
                                        <td class="p-4 text-primary font-semibold">
                                            {{ in_array($acc->type, ['Asset', 'Liability', 'Equity']) ? 'NERACA' : 'LABARUGI' }}
                                        </td>
                                        <td class="p-4 text-center">
                                            <a href="{{ route('accounts.index') }}" 
                                               class="px-3 py-1 bg-surface-highlight hover:bg-primary hover:text-background-dark text-xs font-semibold rounded-lg transition inline-flex items-center gap-1">
                                                <span class="material-symbols-outlined text-xs">edit</span>
                                                Koreksi Akun
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        async function loadUnits() {
            const unitFilter = document.getElementById('unitFilter');
            if (!unitFilter) return;
            
            const response = await fetch('/units', { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            if (result.success && result.data) {
                const currentUnitId = '{{ $unit_id ?? "" }}';
                result.data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.name;
                    if (unit.id == currentUnitId) {
                        option.selected = true;
                    }
                    unitFilter.appendChild(option);
                });
            }
        }
        loadUnits();
    </script>
    @endpush
</x-app-layout>
