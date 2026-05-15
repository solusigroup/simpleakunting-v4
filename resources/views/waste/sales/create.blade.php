<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('waste.index') }}" class="text-white hover:text-primary transition flex-shrink-0">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Jual Stok Sampah</h2>
                <p class="text-text-muted text-sm mt-1">Rekam penjualan stok sampah ke pihak ketiga / agregator</p>
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
            <form action="{{ route('waste.sales.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Jenis Sampah</label>
                        <select name="waste_category_id" id="waste_category_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                            <option value="">Pilih Stok Tersedia</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    data-price="{{ $category->sell_price }}" 
                                    data-unit="{{ $category->unit }}"
                                    data-stock="{{ $category->stock }}"
                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} (Stok: {{ number_format($category->stock, 2) }} {{ $category->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Penerimaan Kas Ke Akun</label>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Berat Dijual</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="weight" id="weight" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none pr-12" placeholder="0.00" required>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-text-muted text-sm" id="unit-label">-</span>
                        </div>
                        <small class="text-primary text-[10px] font-bold mt-1" id="stock-hint"></small>
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Harga Jual Per Satuan</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-text-muted text-sm">Rp</span>
                            <input type="number" name="price_at_time" id="price_at_time" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none pl-12" placeholder="0" required>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Tanggal Penjualan</label>
                        <input type="date" name="date" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Nama Pembeli / Agregator</label>
                        <input type="text" name="buyer_name" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" placeholder="Contoh: UD Maju Jaya">
                    </div>
                </div>

                <!-- Total Display -->
                <div class="rounded-2xl bg-green-500/10 p-6 border border-green-500/20 mb-8 flex justify-between items-center">
                    <p class="text-green-400 font-medium">Total Penerimaan Penjualan</p>
                    <h3 class="text-2xl font-bold text-green-400" id="total-amount-display">Rp 0</h3>
                </div>

                <div class="mb-8">
                    <label class="block text-sm text-text-muted mb-2">Catatan (Opsional)</label>
                    <textarea name="note" rows="3" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" placeholder="Keterangan tambahan..."></textarea>
                </div>

                <div class="flex gap-4">
                    <x-btn href="{{ route('waste.index') }}" type="secondary" class="flex-1 justify-center py-4">Batal</x-btn>
                    <x-btn type="primary" class="flex-1 justify-center py-4 bg-green-600 hover:bg-green-700">Konfirmasi Penjualan</x-btn>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('waste_category_id');
            const weightInput = document.getElementById('weight');
            const priceInput = document.getElementById('price_at_time');
            const unitLabel = document.getElementById('unit-label');
            const stockHint = document.getElementById('stock-hint');
            const totalDisplay = document.getElementById('total-amount-display');

            function updateFields() {
                const selected = categorySelect.options[categorySelect.selectedIndex];
                if (!selected || !selected.value) {
                    unitLabel.textContent = '-';
                    stockHint.textContent = '';
                    return;
                }

                const price = parseFloat(selected.getAttribute('data-price'));
                const unit = selected.getAttribute('data-unit');
                const stock = parseFloat(selected.getAttribute('data-stock'));

                unitLabel.textContent = unit;
                stockHint.textContent = 'Maksimal stok: ' + stock + ' ' + unit;
                if (!priceInput.value) priceInput.value = price;
                
                calculateTotal();
            }

            function calculateTotal() {
                const price = parseFloat(priceInput.value) || 0;
                const weight = parseFloat(weightInput.value) || 0;
                const total = price * weight;
                totalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            }

            categorySelect.addEventListener('change', updateFields);
            weightInput.addEventListener('input', calculateTotal);
            priceInput.addEventListener('input', calculateTotal);

            if (categorySelect.value) updateFields();
        });
    </script>
    @endpush
</x-app-layout>
