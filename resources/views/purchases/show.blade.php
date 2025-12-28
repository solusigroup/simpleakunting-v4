<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('purchases.index') }}" class="text-text-muted hover:text-white">
                        <span class="material-symbols-outlined">arrow_back</span>
                    </a>
                    <div>
                        <h2 class="text-2xl font-bold text-white font-display">Detail Pembelian</h2>
                        <p class="text-text-muted text-sm mt-1">{{ $invoice->invoice_number }}</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <x-btn type="ghost" onclick="window.print()">
                    <span class="material-symbols-outlined text-xl">print</span>
                    Cetak
                </x-btn>
                <x-btn type="danger" onclick="deleteInvoice({{ $invoice->id }})">
                    <span class="material-symbols-outlined text-xl">delete</span>
                    Hapus
                </x-btn>
            </div>
        </div>
    </x-slot>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Invoice Details Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">receipt_long</span>
                        Informasi Invoice
                    </h3>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold 
                        {{ $invoice->status === 'Posted' ? 'bg-primary/20 text-primary' : 
                           ($invoice->status === 'Paid' ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-500/20 text-gray-400') }}">
                        <span class="material-symbols-outlined text-[14px]">
                            {{ $invoice->status === 'Posted' ? 'check_circle' : 
                               ($invoice->status === 'Paid' ? 'paid' : 'edit') }}
                        </span>
                        {{ $invoice->status === 'Posted' ? 'Terposting' : 
                           ($invoice->status === 'Paid' ? 'Lunas' : 'Draft') }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-text-muted text-xs uppercase tracking-wider">Tanggal Invoice</label>
                            <p class="text-white font-medium">{{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</p>
                        </div>
                        <div>
                            <label class="text-text-muted text-xs uppercase tracking-wider">Jatuh Tempo</label>
                            <p class="text-white font-medium">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</p>
                        </div>
                        <div>
                            <label class="text-text-muted text-xs uppercase tracking-wider">Supplier</label>
                            <p class="text-white font-medium">{{ $invoice->contact->name ?? '-' }}</p>
                        </div>
                        @if($invoice->businessUnit)
                        <div>
                            <label class="text-text-muted text-xs uppercase tracking-wider">Unit Bisnis</label>
                            <p class="text-white font-medium">{{ $invoice->businessUnit->name }}</p>
                        </div>
                        @endif
                    </div>
                    @if($invoice->notes)
                    <div class="mt-4 pt-4 border-t border-border-dark">
                        <label class="text-text-muted text-xs uppercase tracking-wider">Catatan</label>
                        <p class="text-white mt-1">{{ $invoice->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">inventory_2</span>
                        Item Pembelian
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border-dark bg-surface-dark">
                                <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Deskripsi</th>
                                <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider">Akun</th>
                                <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Qty</th>
                                <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Harga</th>
                                <th class="p-4 text-xs font-bold text-text-muted uppercase tracking-wider text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach($invoice->items as $item)
                            <tr class="border-b border-border-dark/50">
                                <td class="p-4">
                                    <div class="text-white font-medium">{{ $item->description }}</div>
                                    @if($item->inventory)
                                    <div class="text-text-muted text-xs">{{ $item->inventory->code }} - {{ $item->inventory->name }}</div>
                                    @endif
                                </td>
                                <td class="p-4 text-text-muted">{{ $item->account->code ?? '' }} {{ $item->account->name ?? '-' }}</td>
                                <td class="p-4 text-white text-right font-mono">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                                <td class="p-4 text-white text-right font-mono">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="p-4 text-white text-right font-mono font-bold">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-surface-dark">
                                <td colspan="4" class="p-4 text-right text-white font-semibold">Subtotal</td>
                                <td class="p-4 text-right text-white font-mono font-bold">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @if($invoice->tax > 0)
                            <tr class="bg-surface-dark">
                                <td colspan="4" class="p-4 text-right text-text-muted">Pajak</td>
                                <td class="p-4 text-right text-white font-mono">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($invoice->discount > 0)
                            <tr class="bg-surface-dark">
                                <td colspan="4" class="p-4 text-right text-text-muted">Diskon</td>
                                <td class="p-4 text-right text-accent-red font-mono">-Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="bg-primary/10 border-t-2 border-primary">
                                <td colspan="4" class="p-4 text-right text-white font-bold text-lg">TOTAL</td>
                                <td class="p-4 text-right text-primary font-mono font-bold text-lg">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Supplier Info -->
            @if($invoice->contact)
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">business</span>
                        Supplier
                    </h3>
                </div>
                <div class="card-body space-y-3">
                    <div>
                        <label class="text-text-muted text-xs uppercase tracking-wider">Nama</label>
                        <p class="text-white font-medium">{{ $invoice->contact->name }}</p>
                    </div>
                    @if($invoice->contact->phone)
                    <div>
                        <label class="text-text-muted text-xs uppercase tracking-wider">Telepon</label>
                        <p class="text-white">{{ $invoice->contact->phone }}</p>
                    </div>
                    @endif
                    @if($invoice->contact->email)
                    <div>
                        <label class="text-text-muted text-xs uppercase tracking-wider">Email</label>
                        <p class="text-white">{{ $invoice->contact->email }}</p>
                    </div>
                    @endif
                    @if($invoice->contact->address)
                    <div>
                        <label class="text-text-muted text-xs uppercase tracking-wider">Alamat</label>
                        <p class="text-white">{{ $invoice->contact->address }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Journal Info -->
            @if($invoice->journal)
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">book</span>
                        Jurnal Otomatis
                    </h3>
                </div>
                <div class="card-body">
                    <div class="space-y-2 text-sm">
                        @foreach($invoice->journal->items as $journalItem)
                        <div class="flex justify-between items-center py-2 border-b border-border-dark/50 last:border-0">
                            <div class="flex-1">
                                <p class="text-white">{{ $journalItem->account->name ?? '-' }}</p>
                                <p class="text-text-muted text-xs">{{ $journalItem->account->code ?? '' }}</p>
                            </div>
                            <div class="text-right font-mono">
                                @if($journalItem->debit > 0)
                                <p class="text-blue-400">D: Rp {{ number_format($journalItem->debit, 0, ',', '.') }}</p>
                                @endif
                                @if($journalItem->credit > 0)
                                <p class="text-primary">K: Rp {{ number_format($journalItem->credit, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        async function deleteInvoice(id) {
            if (!confirm('Yakin ingin menghapus invoice ini? Stok persediaan akan dikembalikan.')) {
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
                    window.location.href = '{{ route('purchases.index') }}';
                } else {
                    alert(result.message || 'Gagal menghapus');
                }
            } catch (error) {
                alert('Terjadi kesalahan');
            }
        }
    </script>
    @endpush
</x-app-layout>
