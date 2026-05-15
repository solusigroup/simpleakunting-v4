<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('journals.index') }}" class="text-white hover:text-primary transition flex-shrink-0">
                    <span class="material-symbols-outlined text-2xl">arrow_back</span>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-white font-display">Detail Jurnal</h2>
                    <p class="text-text-muted text-sm mt-1">{{ $journal->reference }} | {{ $journal->date->format('d M Y') }}</p>
                </div>
            </div>
            <div class="flex gap-2 print:hidden">
                <x-btn type="ghost" onclick="window.print()">
                    <span class="material-symbols-outlined text-xl">print</span>
                    Cetak
                </x-btn>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Journal Entry -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card">
                <div class="card-header justify-between">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">book</span>
                        Rincian Ayat Jurnal
                    </h3>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $journal->is_posted ? 'bg-primary/20 text-primary border border-primary/30' : 'bg-orange-500/20 text-orange-400 border border-orange-500/30' }}">
                        {{ $journal->is_posted ? 'Terposting' : 'Draft' }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-text-muted text-xs uppercase tracking-wider border-b border-border-dark/50 bg-surface-dark/50">
                                <th class="px-6 py-4 font-semibold">Akun</th>
                                <th class="px-6 py-4 font-semibold text-right">Debit</th>
                                <th class="px-6 py-4 font-semibold text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-dark/30">
                            @foreach($journal->items as $item)
                            <tr class="hover:bg-surface-highlight/30 transition">
                                <td class="px-6 py-4">
                                    <p class="text-white font-bold">{{ $item->account->name }}</p>
                                    <p class="text-text-muted text-xs">{{ $item->account->code }}</p>
                                    @if($item->memo)
                                        <p class="text-text-muted text-xs italic mt-1 italic opacity-70">"{{ $item->memo }}"</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-white">
                                    {{ $item->debit > 0 ? 'Rp ' . number_format($item->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono text-white">
                                    {{ $item->credit > 0 ? 'Rp ' . number_format($item->credit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-surface-dark/80 font-bold border-t border-border-dark">
                                <td class="px-6 py-4 text-white text-right uppercase tracking-widest text-xs">Total</td>
                                <td class="px-6 py-4 text-right text-primary font-mono">
                                    Rp {{ number_format($journal->items->sum('debit'), 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right text-primary font-mono">
                                    Rp {{ number_format($journal->items->sum('credit'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <div class="card p-6">
                <h4 class="text-xs font-bold text-text-muted uppercase tracking-widest mb-4">Informasi Transaksi</h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-[10px] text-text-muted uppercase mb-1">Sumber Data</p>
                        <p class="text-white font-bold flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-primary">source</span>
                            {{ strtoupper(str_replace('_', ' ', $journal->source)) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] text-text-muted uppercase mb-1">Keterangan</p>
                        <p class="text-white text-sm leading-relaxed">{{ $journal->description }}</p>
                    </div>
                    @if($journal->businessUnit)
                    <div>
                        <p class="text-[10px] text-text-muted uppercase mb-1">Unit Bisnis</p>
                        <p class="text-white font-bold">{{ $journal->businessUnit->name }}</p>
                    </div>
                    @endif
                    @if($journal->contact)
                    <div>
                        <p class="text-[10px] text-text-muted uppercase mb-1">Kontak Terkait</p>
                        <p class="text-white font-bold">{{ $journal->contact->name }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card p-6 border-l-4 border-primary">
                <div class="flex items-start gap-4">
                    <div class="bg-primary/20 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-primary">info</span>
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-sm mb-1">Catatan Akuntansi</h4>
                        <p class="text-text-muted text-xs leading-relaxed">
                            Jurnal ini dibuat secara otomatis oleh sistem melalui modul {{ str_replace('_', ' ', $journal->source) }}. 
                            @if(!$journal->is_posted)
                                Status masih <strong>Draft</strong> dan belum mempengaruhi Laporan Keuangan utama.
                            @else
                                Jurnal sudah <strong>Terposting</strong> dan masuk ke dalam Buku Besar.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
