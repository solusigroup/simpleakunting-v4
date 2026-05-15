<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('waste.index') }}" class="text-white hover:text-primary transition flex-shrink-0">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white font-display">Catat Setoran Sampah</h2>
                <p class="text-text-muted text-sm mt-1">Input setoran dari nasabah ke bank sampah</p>
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
            <form action="{{ route('waste.deposits.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Nasabah</label>
                        <select name="waste_collector_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                            <option value="">Pilih Nasabah</option>
                            @foreach($collectors as $collector)
                                <option value="{{ $collector->id }}" {{ request('collector_id') == $collector->id ? 'selected' : '' }}>
                                    {{ $collector->collector_number }} - {{ $collector->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Tanggal Setoran</label>
                        <input type="date" name="date" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Jenis Sampah</label>
                        <select name="waste_category_id" id="waste_category_id" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" required>
                            <option value="">Pilih Jenis Sampah</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-price="{{ $category->buy_price }}" data-unit="{{ $category->unit }}">
                                    {{ $category->name }} (Rp {{ number_format($category->buy_price, 0, ',', '.') }}/{{ $category->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-text-muted mb-2">Berat / Jumlah</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="weight" id="weight" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none pr-12" placeholder="0.00" required>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-text-muted text-sm" id="unit-label">-</span>
                        </div>
                    </div>
                </div>

                <!-- Total Display -->
                <div class="rounded-2xl bg-surface-highlight p-6 border border-border-dark mb-8 flex justify-between items-center">
                    <p class="text-text-muted font-medium">Total Tabungan (Rupiah)</p>
                    <h3 class="text-2xl font-bold text-primary" id="total-amount-display">Rp 0</h3>
                </div>

                <div class="mb-8">
                    <label class="block text-sm text-text-muted mb-2">Catatan (Opsional)</label>
                    <textarea name="note" rows="3" class="w-full bg-surface-highlight border border-border-dark rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none" placeholder="Keterangan tambahan..."></textarea>
                </div>

                <div class="flex gap-4">
                    <x-btn href="{{ route('waste.index') }}" type="secondary" class="flex-1 justify-center py-4">Batal</x-btn>
                    <x-btn type="primary" class="flex-1 justify-center py-4">Simpan Setoran & Posting Jurnal</x-btn>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('waste_category_id');
            const weightInput = document.getElementById('weight');
            const unitLabel = document.getElementById('unit-label');
            const totalDisplay = document.getElementById('total-amount-display');

            function calculateTotal() {
                const selected = categorySelect.options[categorySelect.selectedIndex];
                if (!selected || !selected.value) {
                    unitLabel.textContent = '-';
                    totalDisplay.textContent = 'Rp 0';
                    return;
                }

                const price = parseFloat(selected.getAttribute('data-price'));
                const unit = selected.getAttribute('data-unit');
                const weight = parseFloat(weightInput.value) || 0;

                unitLabel.textContent = unit;
                const total = price * weight;
                totalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            }

            categorySelect.addEventListener('change', calculateTotal);
            weightInput.addEventListener('input', calculateTotal);
        });
    </script>
    @endpush
</x-app-layout>
