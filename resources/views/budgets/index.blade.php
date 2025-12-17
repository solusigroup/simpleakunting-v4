<x-app-layout>
    <x-slot name="title">Anggaran</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary text-2xl">account_balance_wallet</span>
                <div>
                    <h1 class="text-2xl font-bold">Anggaran (Budget)</h1>
                    <p class="text-sm text-text-muted">Kelola anggaran per akun dan periode</p>
                </div>
            </div>
            <button onclick="openCreateModal()" class="bg-primary hover:bg-primary-dark text-background-dark font-medium px-4 py-2.5 rounded-xl transition flex items-center gap-2">
                <span class="material-symbols-outlined">add</span>
                Tambah Budget
            </button>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-border-dark">
            <form method="GET" action="{{ route('budgets.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Tahun</label>
                    <select name="year" class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Tipe Periode</label>
                    <select name="period_type" class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Semua</option>
                        <option value="MONTHLY" {{ request('period_type') == 'MONTHLY' ? 'selected' : '' }}>Bulanan</option>
                        <option value="QUARTERLY" {{ request('period_type') == 'QUARTERLY' ? 'selected' : '' }}>Kuartalan</option>
                        <option value="YEARLY" {{ request('period_type') == 'YEARLY' ? 'selected' : '' }}>Tahunan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Akun</label>
                    <select name="coa_id" class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Semua Akun</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ request('coa_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->code }} - {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-background-dark font-medium px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">filter_list</span>
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Budgets Table -->
        <div class="bg-surface-dark rounded-2xl border border-border-dark overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-dark">
                            <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Akun</th>
                            <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Periode</th>
                            <th class="text-right text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Budget</th>
                            <th class="text-right text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Realisasi</th>
                            <th class="text-right text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Selisih</th>
                            <th class="text-center text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Status</th>
                            <th class="text-center text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark">
                        @forelse($budgets as $budget)
                        @php
                            $actual = abs($budget->getActual());
                            $variance = $budget->getVariance();
                            $isOver = $budget->isOverBudget();
                        @endphp
                        <tr class="hover:bg-surface-highlight transition">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-white">{{ $budget->account->name }}</div>
                                <div class="text-xs text-text-muted">{{ $budget->account->code }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-white">{{ $budget->period_label }}</td>
                            <td class="px-6 py-4 text-sm text-right text-white font-mono">Rp {{ number_format($budget->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm text-right text-white font-mono">Rp {{ number_format($actual, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm text-right font-mono {{ $isOver ? 'text-red-400' : 'text-green-400' }}">
                                {{ $isOver ? '-' : '+' }} Rp {{ number_format(abs($variance), 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($isOver)
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-red-500/20 text-red-400">Over Budget</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-green-500/20 text-green-400">On Track</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="editBudget({{ $budget->id }}, {{ $budget->amount }}, '{{ $budget->notes }}')" 
                                            class="text-blue-400 hover:text-blue-300 transition">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button onclick="deleteBudget({{ $budget->id }})" 
                                            class="text-red-400 hover:text-red-300 transition">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-text-muted">
                                <span class="material-symbols-outlined text-4xl mb-2">account_balance_wallet</span>
                                <p>Belum ada data anggaran untuk tahun {{ $year }}</p>
                                <button onclick="openCreateModal()" class="mt-4 text-primary hover:underline">Tambah Budget Pertama</button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeCreateModal()">
        <div class="bg-surface-dark rounded-2xl border border-border-dark w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-border-dark">
                <h3 class="text-lg font-semibold">Tambah Budget</h3>
                <button onclick="closeCreateModal()" class="text-text-muted hover:text-white transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="createForm" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Akun <span class="text-red-400">*</span></label>
                    <select name="coa_id" required class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Pilih Akun</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Tipe Periode <span class="text-red-400">*</span></label>
                        <select name="period_type" id="periodType" required onchange="togglePeriodFields()" class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="MONTHLY">Bulanan</option>
                            <option value="QUARTERLY">Kuartalan</option>
                            <option value="YEARLY">Tahunan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Tahun <span class="text-red-400">*</span></label>
                        <select name="period_year" required class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="monthField">
                    <label class="block text-sm font-medium text-text-muted mb-2">Bulan</label>
                    <select name="period_month" class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>

                <div id="quarterField" class="hidden">
                    <label class="block text-sm font-medium text-text-muted mb-2">Kuartal</label>
                    <select name="period_quarter" class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="1">Q1 (Jan-Mar)</option>
                        <option value="2">Q2 (Apr-Jun)</option>
                        <option value="3">Q3 (Jul-Sep)</option>
                        <option value="4">Q4 (Okt-Des)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Jumlah Anggaran <span class="text-red-400">*</span></label>
                    <input type="number" name="amount" required min="0" step="0.01" placeholder="0"
                           class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan opsional..."
                              class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                </div>

                <div id="createError" class="text-red-400 text-sm hidden"></div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2.5 rounded-xl border border-border-dark text-white hover:bg-surface-highlight transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-background-dark font-medium transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeEditModal()">
        <div class="bg-surface-dark rounded-2xl border border-border-dark w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-border-dark">
                <h3 class="text-lg font-semibold">Edit Budget</h3>
                <button onclick="closeEditModal()" class="text-text-muted hover:text-white transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="editForm" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" id="editBudgetId" name="budget_id">

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Jumlah Anggaran <span class="text-red-400">*</span></label>
                    <input type="number" id="editAmount" name="amount" required min="0" step="0.01"
                           class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Catatan</label>
                    <textarea id="editNotes" name="notes" rows="2"
                              class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                </div>

                <div id="editError" class="text-red-400 text-sm hidden"></div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl border border-border-dark text-white hover:bg-surface-highlight transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-dark text-background-dark font-medium transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function togglePeriodFields() {
            const type = document.getElementById('periodType').value;
            document.getElementById('monthField').classList.toggle('hidden', type !== 'MONTHLY');
            document.getElementById('quarterField').classList.toggle('hidden', type !== 'QUARTERLY');
        }

        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
            document.getElementById('createModal').classList.add('flex');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
            document.getElementById('createModal').classList.remove('flex');
            document.getElementById('createForm').reset();
            document.getElementById('createError').classList.add('hidden');
        }

        function editBudget(id, amount, notes) {
            document.getElementById('editBudgetId').value = id;
            document.getElementById('editAmount').value = amount;
            document.getElementById('editNotes').value = notes || '';
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }

        async function deleteBudget(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus budget ini?')) return;
            
            try {
                const response = await fetch(`/budgets/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    alert('Gagal menghapus budget');
                }
            } catch (error) {
                alert('Terjadi kesalahan');
            }
        }

        // Create form submit
        document.getElementById('createForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const errorDiv = document.getElementById('createError');
            
            try {
                const response = await fetch('{{ route("budgets.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(Object.fromEntries(formData)),
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    location.reload();
                } else {
                    errorDiv.textContent = Object.values(data.errors).flat().join(', ');
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Terjadi kesalahan';
                errorDiv.classList.remove('hidden');
            }
        });

        // Edit form submit
        document.getElementById('editForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('editBudgetId').value;
            const formData = new FormData(this);
            const errorDiv = document.getElementById('editError');
            
            try {
                const response = await fetch(`/budgets/${id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(Object.fromEntries(formData)),
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    location.reload();
                } else {
                    errorDiv.textContent = Object.values(data.errors).flat().join(', ');
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Terjadi kesalahan';
                errorDiv.classList.remove('hidden');
            }
        });
    </script>
    @endpush
</x-app-layout>
