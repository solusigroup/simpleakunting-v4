@php
    $title = 'Bagi Hasil Investor';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-white">Bagi Hasil Investor</h2>
                <p class="text-text-muted text-sm mt-1">Kalkulasi dan pembagian laba untuk investor</p>
            </div>
            
            <form method="GET" action="{{ route('reports.investor-sharing') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl border border-border-dark bg-surface-dark/30">
                    <span class="text-text-muted text-sm">Periode:</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent border-0 text-white text-sm focus:ring-0 w-36">
                    <span class="text-text-muted">-</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent border-0 text-white text-sm focus:ring-0 w-36">
                </div>
                
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-background-dark font-bold hover:bg-[#2ec56a] transition flex items-center gap-2">
                    <span class="material-symbols-outlined">refresh</span>
                    Hitung Ulang
                </button>
            </form>
        </div>
    </x-slot>

    <div class="space-y-8">
        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-400">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="p-4 rounded-xl bg-green-500/20 border border-green-500/30 text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-400">
                {{ session('error') }}
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-symbols-outlined text-6xl text-orange-400">equalizer</span>
                </div>
                <div class="relative z-10">
                    <p class="text-text-muted text-sm uppercase tracking-wider mb-1">Total Laba Kotor (Gross)</p>
                    <h3 class="text-3xl font-bold text-white font-mono">Rp {{ number_format($grossProfit, 2, ',', '.') }}</h3>
                    <p class="text-xs text-text-muted mt-2">Pendapatan - HPP (Kode 5xxx)</p>
                </div>
            </div>
            
            <div class="p-6 rounded-2xl border border-border-dark bg-surface-dark/30 relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-symbols-outlined text-6xl text-blue-400">account_balance_wallet</span>
                </div>
                <div class="relative z-10">
                    <p class="text-text-muted text-sm uppercase tracking-wider mb-1">Total Laba Bersih (Net)</p>
                    <h3 class="text-3xl font-bold text-white font-mono">Rp {{ number_format($netProfit, 2, ',', '.') }}</h3>
                    <p class="text-xs text-text-muted mt-2">Seluruh Pendapatan - Seluruh Beban</p>
                </div>
            </div>
        </div>

        <!-- Calculation Table -->
        <div class="bg-surface-dark rounded-2xl border border-border-dark overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-background-dark/30 flex justify-between items-center">
                <h3 class="font-bold text-white">Rincian Pembagian Investor</h3>
                <span class="text-xs text-text-muted">Berdasarkan data master investor aktif</span>
            </div>
            
            <form action="{{ route('reports.investor-sharing.post') }}" method="POST">
                @csrf
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <input type="hidden" name="unit_id" value="{{ request('unit_id') }}">

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-background-dark/10">
                                <th class="px-6 py-4 text-sm font-semibold text-text-muted uppercase">Investor</th>
                                <th class="px-6 py-4 text-sm font-semibold text-text-muted uppercase text-center">Basis</th>
                                <th class="px-6 py-4 text-sm font-semibold text-text-muted uppercase text-right">Basis Amount</th>
                                <th class="px-6 py-4 text-sm font-semibold text-text-muted uppercase text-center">Share</th>
                                <th class="px-6 py-4 text-sm font-semibold text-text-muted uppercase text-right">Nominal Bagi Hasil</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark">
                            @php $totalSharing = 0; @endphp
                            @forelse($calculations as $index => $calc)
                                @php $totalSharing += $calc['share_amount']; @endphp
                                <tr class="hover:bg-surface-highlight/20 transition">
                                    <td class="px-6 py-4">
                                        <div class="text-white font-medium">{{ $calc['investor']->name }}</div>
                                        <input type="hidden" name="investors[{{ $index }}][id]" value="{{ $calc['investor']->id }}">
                                        <input type="hidden" name="investors[{{ $index }}][basis_amount]" value="{{ $calc['basis_amount'] }}">
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $calc['investor']->basis == 'GROSS_PROFIT' ? 'bg-orange-500/10 text-orange-400' : 'bg-blue-500/10 text-blue-400' }}">
                                            {{ $calc['investor']->basis == 'GROSS_PROFIT' ? 'Gross' : 'Net' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-text-muted font-mono">
                                        Rp {{ number_format($calc['basis_amount'], 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-white">
                                        {{ number_format($calc['investor']->share_percentage, 2) }}%
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <input type="number" name="investors[{{ $index }}][amount]" 
                                               value="{{ round($calc['share_amount'], 2) }}" step="0.01"
                                               class="w-40 px-3 py-2 rounded-lg bg-background-dark border border-border-dark text-white text-right font-mono focus:border-primary focus:ring-1 focus:ring-primary">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-text-muted">
                                        Belum ada investor aktif yang terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($calculations) > 0)
                        <tfoot>
                            <tr class="bg-background-dark/30">
                                <td colspan="4" class="px-6 py-4 text-right font-bold text-white uppercase tracking-wider">Total Pembagian</td>
                                <td class="px-6 py-4 text-right font-bold text-primary text-xl font-mono">
                                    Rp {{ number_format($totalSharing, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                @if(count($calculations) > 0)
                <div class="p-6 bg-background-dark/20 border-t border-border-dark">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="max-w-md">
                            <h4 class="text-white font-bold mb-2">Konfirmasi Posting Jurnal</h4>
                            <p class="text-sm text-text-muted">
                                Mengklik tombol di bawah akan membuat **Jurnal Umum** otomatis berdasarkan pengaturan akun di Profil Perusahaan. Pastikan nominal sudah benar sebelum posting.
                            </p>
                            @php
                                $coaSet = $company->investor_sharing_debit_coa_id && $company->investor_sharing_credit_coa_id;
                            @endphp
                            @if(!$coaSet)
                                <div class="mt-3 p-3 rounded-lg bg-red-500/10 border border-red-500/30 flex items-center gap-3 text-red-400 text-xs">
                                    <span class="material-symbols-outlined text-sm">warning</span>
                                    <span>
                                        <strong>Akun Belum Diatur:</strong> Anda harus memilih Akun Debet & Kredit di 
                                        <a href="{{ route('company.settings') }}" class="underline font-bold hover:text-white">Pengaturan Perusahaan</a> 
                                        sebelem dapat melakukan posting jurnal.
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex flex-col items-end gap-2">
                            <button type="submit" 
                                    @if(!$coaSet) disabled title="Atur COA di Pengaturan Perusahaan" @endif
                                    class="px-10 py-4 bg-primary text-background-dark font-bold rounded-2xl hover:bg-[#2ec56a] transition shadow-lg shadow-primary/20 flex items-center gap-3 disabled:opacity-30 disabled:cursor-not-allowed"
                                    onclick="return confirm('Apakah Anda yakin ingin memposting hasil perhitungan ini ke Jurnal Umum?')">
                                <span class="material-symbols-outlined">description</span>
                                Posting Jurnal Otomatis
                            </button>
                            @if(!$coaSet)
                                <p class="text-[10px] text-red-500 animate-pulse font-bold">Tombol Non-aktif: Pengaturan Akun Diperlukan</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </form>
        </div>

        <!-- Instructions -->
        <div class="p-6 rounded-2xl bg-surface-dark border border-border-dark flex gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-primary">info</span>
            </div>
            <div>
                <h4 class="text-white font-bold mb-1">Cara Kerja Perhitungan</h4>
                <div class="text-sm text-text-muted space-y-2">
                    <p>1. **Gross Profit (Laba Kotor)** dihitung dari jumlah seluruh pendapatan (kode 4xxx) dikurangi biaya pokok pendapatan/HPP (kode 5xxx).</p>
                    <p>2. **Net Profit (Laba Bersih)** dihitung dari jumlah seluruh pendapatan dikurangi seluruh beban operasional (kode 6xxx).</p>
                    <p>3. Anda dapat menyesuaikan angka "Nominal Bagi Hasil" secara manual jika diperlukan sebelum melakukan konfirmasi posting.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
