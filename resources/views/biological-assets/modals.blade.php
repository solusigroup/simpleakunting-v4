<!-- Valuation Modal -->
<div id="valuationModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between sticky top-0 bg-background-dark">
                <div>
                    <h3 class="text-lg font-bold text-white">Penilaian Nilai Wajar</h3>
                    <p class="text-sm text-text-muted" id="valuationAssetName"></p>
                </div>
                <button onclick="closeModal()" class="text-text-muted hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="valuationForm" class="p-6 space-y-4">
                <input type="hidden" id="valuationAssetId">
                
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Tanggal Penilaian *</label>
                    <input type="date" id="valuation_date" required
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Nilai Wajar Saat Ini *</label>
                    <input type="number" id="valuation_current_fair_value" required min="0"
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Biaya untuk Menjual *</label>
                    <input type="number" id="valuation_cost_to_sell" required min="0" value="0"
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Metode Penilaian</label>
                    <input type="text" id="valuation_method_input" placeholder="Market price, DCF, Independent appraisal, dll"
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Catatan Penilaian</label>
                    <textarea id="valuation_notes" rows="3"
                              class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary resize-none"></textarea>
                </div>

                <div class="p-4 bg-primary/10 border border-primary/30 rounded-xl">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" id="create_valuation_journal" checked class="form-checkbox rounded bg-surface-dark border-border-dark text-primary focus:ring-primary">
                        <div>
                            <span class="text-white font-medium">Buat Jurnal Otomatis</span>
                            <p class="text-xs text-text-muted">Jurnal penyesuaian nilai wajar akan dibuat secara otomatis</p>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <x-btn type="secondary" onclick="closeModal()">Batal</x-btn>
                    <x-btn type="primary" type="submit">Simpan Penilaian</x-btn>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transformation Modal -->
<div id="transformationModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between sticky top-0 bg-background-dark">
                <div>
                    <h3 class="text-lg font-bold text-white">Transformasi Biologis</h3>
                    <p class="text-sm text-text-muted" id="transformationAssetName"></p>
                </div>
                <button onclick="closeModal()" class="text-text-muted hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="transformationForm" class="p-6 space-y-4">
                <input type="hidden" id="transformationAssetId">
                
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Jenis Transformasi *</label>
                    <select id="transformation_type" required
                            class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        <option value="growth">Pertumbuhan</option>
                        <option value="degeneration">Degenerasi</option>
                        <option value="production">Produksi</option>
                        <option value="procreation">Prokreasi (Kelahiran)</option>
                        <option value="death">Kematian</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Tanggal Transaksi *</label>
                    <input type="date" id="transformation_date" required
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Perubahan Kuantitas *</label>
                    <input type="number" id="quantity_change" required step="0.01"
                           placeholder="Positif untuk penambahan, negatif untuk pengurangan"
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    <p class="text-xs text-text-muted mt-1">Gunakan angka negatif untuk pengurangan (contoh: -5 untuk kematian 5 ekor)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Deskripsi</label>
                    <textarea id="transformation_description" rows="3"
                              class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary resize-none"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <x-btn type="secondary" onclick="closeModal()">Batal</x-btn>
                    <x-btn type="primary" type="submit">Simpan Transformasi</x-btn>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Harvest Modal -->
<div id="harvestModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-background-dark rounded-2xl border border-border-dark w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-border-dark flex items-center justify-between sticky top-0 bg-background-dark">
                <div>
                    <h3 class="text-lg font-bold text-white">Panen Produk Agrikultur</h3>
                    <p class="text-sm text-text-muted" id="harvestAssetName"></p>
                </div>
                <button onclick="closeModal()" class="text-text-muted hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="harvestForm" class="p-6 space-y-4">
                <input type="hidden" id="harvestAssetId">
                
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Tanggal Panen *</label>
                    <input type="date" id="harvest_date" required
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Nama Produk *</label>
                    <input type="text" id="product_name" required placeholder="Contoh: Susu Sapi, Buah Kelapa Sawit"
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Kuantitas *</label>
                        <input type="number" id="harvest_quantity" required min="0" step="0.01"
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-muted mb-2">Satuan *</label>
                        <input type="text" id="harvest_unit" required
                               class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Nilai Wajar saat Panen *</label>
                    <input type="number" id="fair_value_at_harvest" required min="0"
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                    <p class="text-xs text-text-muted mt-1">Sesuai PSAK 69: Produk agrikultur diukur pada nilai wajar dikurangi biaya untuk menjual pada saat panen</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Biaya untuk Menjual *</label>
                    <input type="number" id="harvest_cost_to_sell" required min="0" value="0"
                           class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Akun Inventory/Produk *</label>
                    <select id="harvest_coa_id" required
                            class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary">
                        <option value="">Pilih Akun</option>
                        @foreach($assetAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Catatan</label>
                    <textarea id="harvest_notes" rows="2"
                              class="w-full px-4 py-3 rounded-xl bg-surface-dark border border-border-dark text-white focus:border-primary focus:ring-primary resize-none"></textarea>
                </div>

                <div class="p-4 bg-primary/10 border border-primary/30 rounded-xl">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" id="create_harvest_journal" checked class="form-checkbox rounded bg-surface-dark border-border-dark text-primary focus:ring-primary">
                        <div>
                            <span class="text-white font-medium">Buat Jurnal Otomatis</span>
                            <p class="text-xs text-text-muted">Jurnal panen akan dibuat secara otomatis</p>
                        </div>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <x-btn type="secondary" onclick="closeModal()">Batal</x-btn>
                    <x-btn type="primary" type="submit">Simpan Panen</x-btn>
                </div>
            </form>
        </div>
    </div>
</div>
