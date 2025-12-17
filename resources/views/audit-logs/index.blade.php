<x-app-layout>
    <x-slot name="title">Audit Trail</x-slot>

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-2xl">history</span>
            <div>
                <h1 class="text-2xl font-bold">Audit Trail</h1>
                <p class="text-sm text-text-muted">Riwayat perubahan data</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-surface-dark rounded-2xl p-6 border border-border-dark">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Model Filter -->
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Jenis Data</label>
                    <select name="model" class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Semua</option>
                        @foreach($modelTypes as $type => $name)
                            <option value="{{ $type }}" {{ request('model') == $type ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter -->
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Aksi</label>
                    <select name="action" class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Semua</option>
                        <option value="CREATE" {{ request('action') == 'CREATE' ? 'selected' : '' }}>Dibuat</option>
                        <option value="UPDATE" {{ request('action') == 'UPDATE' ? 'selected' : '' }}>Diubah</option>
                        <option value="DELETE" {{ request('action') == 'DELETE' ? 'selected' : '' }}>Dihapus</option>
                    </select>
                </div>

                <!-- User Filter -->
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Pengguna</label>
                    <select name="user_id" class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Semua</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="w-full bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-muted mb-2">Sampai Tanggal</label>
                    <div class="flex gap-2">
                        <input type="date" name="to" value="{{ request('to') }}"
                               class="flex-1 bg-background-dark border border-border-dark rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        <button type="submit" class="bg-primary hover:bg-primary-dark text-background-dark font-medium px-4 py-2.5 rounded-xl transition">
                            <span class="material-symbols-outlined">filter_list</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-surface-dark rounded-2xl border border-border-dark overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-dark">
                            <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Waktu</th>
                            <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Pengguna</th>
                            <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Aksi</th>
                            <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Data</th>
                            <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">ID</th>
                            <th class="text-left text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">IP</th>
                            <th class="text-center text-xs font-medium text-text-muted uppercase tracking-wider px-6 py-4">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-dark">
                        @forelse($logs as $log)
                        <tr class="hover:bg-surface-highlight transition">
                            <td class="px-6 py-4 text-sm">
                                <div class="text-white">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-text-muted text-xs">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-white">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $actionColors = [
                                        'CREATE' => 'bg-green-500/20 text-green-400',
                                        'UPDATE' => 'bg-blue-500/20 text-blue-400',
                                        'DELETE' => 'bg-red-500/20 text-red-400',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $actionColors[$log->action] ?? 'bg-gray-500/20 text-gray-400' }}">
                                    {{ $log->action_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-white">{{ $log->model_name }}</td>
                            <td class="px-6 py-4 text-sm text-text-muted">#{{ $log->model_id }}</td>
                            <td class="px-6 py-4 text-sm text-text-muted">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="showAuditDetail({{ $log->id }})" 
                                        class="text-primary hover:text-primary-light transition">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-text-muted">
                                <span class="material-symbols-outlined text-4xl mb-2">history</span>
                                <p>Belum ada riwayat perubahan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-border-dark">
                {{ $logs->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) closeDetailModal()">
        <div class="bg-surface-dark rounded-2xl border border-border-dark w-full max-w-2xl max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-border-dark">
                <h3 class="text-lg font-semibold">Detail Perubahan</h3>
                <button onclick="closeDetailModal()" class="text-text-muted hover:text-white transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="detailContent" class="p-6 overflow-y-auto max-h-[70vh]">
                <!-- Content loaded via JS -->
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        async function showAuditDetail(id) {
            const modal = document.getElementById('detailModal');
            const content = document.getElementById('detailContent');
            
            content.innerHTML = '<div class="text-center py-8"><span class="material-symbols-outlined animate-spin text-4xl text-primary">refresh</span></div>';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            try {
                const response = await fetch(`/audit-logs/${id}`);
                const data = await response.json();
                
                let html = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-text-muted text-sm">Aksi</span>
                                <p class="font-medium">${data.action_name}</p>
                            </div>
                            <div>
                                <span class="text-text-muted text-sm">Data</span>
                                <p class="font-medium">${data.model_name} #${data.model_id}</p>
                            </div>
                            <div>
                                <span class="text-text-muted text-sm">Pengguna</span>
                                <p class="font-medium">${data.user}</p>
                            </div>
                            <div>
                                <span class="text-text-muted text-sm">Waktu</span>
                                <p class="font-medium">${data.created_at}</p>
                            </div>
                        </div>
                `;
                
                if (data.old_values && Object.keys(data.old_values).length > 0) {
                    html += `
                        <div class="mt-6">
                            <h4 class="font-semibold text-red-400 mb-2">Nilai Lama</h4>
                            <pre class="bg-background-dark rounded-xl p-4 text-sm overflow-x-auto">${JSON.stringify(data.old_values, null, 2)}</pre>
                        </div>
                    `;
                }
                
                if (data.new_values && Object.keys(data.new_values).length > 0) {
                    html += `
                        <div class="mt-4">
                            <h4 class="font-semibold text-green-400 mb-2">Nilai Baru</h4>
                            <pre class="bg-background-dark rounded-xl p-4 text-sm overflow-x-auto">${JSON.stringify(data.new_values, null, 2)}</pre>
                        </div>
                    `;
                }
                
                html += '</div>';
                content.innerHTML = html;
            } catch (error) {
                content.innerHTML = '<div class="text-center py-8 text-red-400">Gagal memuat detail</div>';
            }
        }
        
        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
    @endpush
</x-app-layout>
