<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-white font-display">Jurnal Penyesuaian</h2>
            <p class="text-text-muted text-sm mt-1">Buat jurnal penyesuaian akhir periode</p>
        </div>
    </x-slot>

    <!-- Adjustment Types -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <button onclick="selectType('prepaid')" class="p-4 rounded-xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition text-left group">
            <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center mb-3 group-hover:bg-primary/30">
                <span class="material-symbols-outlined text-primary">schedule</span>
            </div>
            <h4 class="text-white font-medium">Beban Dibayar Dimuka</h4>
            <p class="text-text-muted text-sm mt-1">Amortisasi prepaid expenses</p>
        </button>
        
        <button onclick="selectType('accrued')" class="p-4 rounded-xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition text-left group">
            <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center mb-3 group-hover:bg-blue-500/30">
                <span class="material-symbols-outlined text-blue-400">pending_actions</span>
            </div>
            <h4 class="text-white font-medium">Beban Akrual</h4>
            <p class="text-text-muted text-sm mt-1">Beban yang belum dibayar</p>
        </button>
        
        <button onclick="selectType('depreciation')" class="p-4 rounded-xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition text-left group">
            <div class="w-10 h-10 rounded-lg bg-orange-500/20 flex items-center justify-center mb-3 group-hover:bg-orange-500/30">
                <span class="material-symbols-outlined text-orange-400">trending_down</span>
            </div>
            <h4 class="text-white font-medium">Penyusutan</h4>
            <p class="text-text-muted text-sm mt-1">Beban penyusutan aset tetap</p>
        </button>
        
        <button onclick="selectType('manual')" class="p-4 rounded-xl border border-border-dark bg-surface-dark/30 hover:border-primary/50 transition text-left group">
            <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center mb-3 group-hover:bg-purple-500/30">
                <span class="material-symbols-outlined text-purple-400">edit_note</span>
            </div>
            <h4 class="text-white font-medium">Penyesuaian Lainnya</h4>
            <p class="text-text-muted text-sm mt-1">Jurnal penyesuaian manual</p>
        </button>
    </div>

    <!-- Adjustment Form -->
    <div id="adjustmentForm" class="hidden">
        <div class="rounded-2xl border border-border-dark bg-surface-dark/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-border-dark bg-surface-highlight/30 flex items-center justify-between">
                <h3 class="font-bold text-white" id="formTitle">Jurnal Penyesuaian</h3>
                <button onclick="hideForm()" class="text-text-muted hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form class="p-6 space-y-4" onsubmit="submitAdjustment(event)">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Tanggal</label>
                        <input type="date" id="adj_date" required value="{{ now()->format('Y-m-d') }}"
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Unit Usaha</label>
                        <select id="adj_unit_id" class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                            <option value="">Pilih Unit Usaha...</option>
                            @foreach($businessUnits ?? [] as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Referensi</label>
                        <input type="text" id="adj_reference" placeholder="ADJ-001"
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Keterangan</label>
                    <input type="text" id="adj_description" required
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                </div>

                <!-- Journal Lines -->
                <div class="border border-border-dark rounded-xl overflow-hidden">
                    <div class="bg-surface-highlight/50 px-4 py-3 flex items-center justify-between">
                        <span class="text-white font-medium">Detail Jurnal</span>
                        <button type="button" onclick="addLine()" class="text-primary text-sm hover:underline">+ Tambah Baris</button>
                    </div>
                    <div id="journalLines" class="divide-y divide-border-dark/50">
                        <!-- Lines will be added here -->
                    </div>
                    <div class="bg-surface-highlight/30 px-4 py-3 flex justify-between">
                        <span class="text-text-muted">Total</span>
                        <div class="flex gap-8">
                            <span class="text-white font-bold" id="totalDebit">Rp 0</span>
                            <span class="text-white font-bold" id="totalCredit">Rp 0</span>
                        </div>
                    </div>
                </div>

                <div id="balanceWarning" class="hidden p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                    ⚠️ Jurnal tidak balance! Total debit harus sama dengan total kredit.
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <x-btn type="secondary" onclick="hideForm()">Batal</x-btn>
                    <x-btn type="primary" type="submit">Simpan Jurnal</x-btn>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let lineCounter = 0;
        const accounts = @json($accounts ?? []);

        function selectType(type) {
            const titles = {
                'prepaid': 'Penyesuaian Beban Dibayar Dimuka',
                'accrued': 'Penyesuaian Beban Akrual',
                'depreciation': 'Penyesuaian Penyusutan Aset Tetap',
                'manual': 'Jurnal Penyesuaian Manual'
            };
            
            document.getElementById('formTitle').textContent = titles[type];
            document.getElementById('journalLines').innerHTML = '';
            lineCounter = 0;
            
            // Add default lines
            addLine();
            addLine();
            
            document.getElementById('adjustmentForm').classList.remove('hidden');
        }

        function hideForm() {
            document.getElementById('adjustmentForm').classList.add('hidden');
        }

        function addLine() {
            lineCounter++;
            const accountOptions = accounts.map(a => 
                `<option value="${a.id}">${a.code} - ${a.name}</option>`
            ).join('');

            const html = `
                <div class="px-4 py-3 flex gap-4 items-center" id="line_${lineCounter}">
                    <select class="flex-1 px-3 py-2 rounded-lg bg-surface-dark border border-border-dark text-white text-sm focus:border-primary focus:ring-primary" required>
                        <option value="">Pilih Akun</option>
                        ${accountOptions}
                    </select>
                    <input type="number" placeholder="Debit" min="0" onchange="updateTotals()"
                           class="w-32 px-3 py-2 rounded-lg bg-surface-dark border border-border-dark text-white text-sm focus:border-primary focus:ring-primary line-debit">
                    <input type="number" placeholder="Kredit" min="0" onchange="updateTotals()"
                           class="w-32 px-3 py-2 rounded-lg bg-surface-dark border border-border-dark text-white text-sm focus:border-primary focus:ring-primary line-credit">
                    <button type="button" onclick="removeLine(${lineCounter})" class="text-text-muted hover:text-red-400">
                        <span class="material-symbols-outlined text-xl">delete</span>
                    </button>
                </div>
            `;
            document.getElementById('journalLines').insertAdjacentHTML('beforeend', html);
        }

        function removeLine(id) {
            const line = document.getElementById(`line_${id}`);
            if (line && document.querySelectorAll('#journalLines > div').length > 2) {
                line.remove();
                updateTotals();
            }
        }

        function updateTotals() {
            let totalDebit = 0;
            let totalCredit = 0;
            
            document.querySelectorAll('.line-debit').forEach(el => {
                totalDebit += parseFloat(el.value) || 0;
            });
            document.querySelectorAll('.line-credit').forEach(el => {
                totalCredit += parseFloat(el.value) || 0;
            });
            
            document.getElementById('totalDebit').textContent = `Rp ${totalDebit.toLocaleString('id-ID')}`;
            document.getElementById('totalCredit').textContent = `Rp ${totalCredit.toLocaleString('id-ID')}`;
            
            const isBalanced = Math.abs(totalDebit - totalCredit) < 0.01;
            document.getElementById('balanceWarning').classList.toggle('hidden', isBalanced || (totalDebit === 0 && totalCredit === 0));
        }

        async function submitAdjustment(e) {
            e.preventDefault();
            
            let totalDebit = 0;
            let totalCredit = 0;
            document.querySelectorAll('.line-debit').forEach(el => totalDebit += parseFloat(el.value) || 0);
            document.querySelectorAll('.line-credit').forEach(el => totalCredit += parseFloat(el.value) || 0);
            
            if (Math.abs(totalDebit - totalCredit) > 0.01) {
                alert('Jurnal harus balance! Total debit harus sama dengan total kredit.');
                return;
            }
            
            alert('Jurnal penyesuaian berhasil disimpan!');
            hideForm();
        }
    </script>
    @endpush
</x-app-layout>
