<x-app-layout>
    <div class="max-w-md mx-auto my-8 p-8 bg-white text-black font-mono border border-gray-300 shadow-sm">
        <div class="text-center mb-6 border-b-2 border-dashed border-black pb-4">
            <h1 class="text-xl font-bold uppercase">{{ auth()->user()->company->name }}</h1>
            <p class="text-xs">{{ auth()->user()->company->address }}</p>
            <p class="text-xs">Telp: {{ auth()->user()->company->phone }}</p>
        </div>

        <div class="mb-6 text-center">
            <h2 class="text-lg font-bold border-y border-black py-1">STRUK PENARIKAN TABUNGAN</h2>
        </div>

        <div class="space-y-2 mb-8 text-sm">
            <div class="flex justify-between">
                <span>No. Transaksi</span>
                <span class="font-bold">{{ $withdrawal->withdrawal_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal</span>
                <span>{{ $withdrawal->date->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex justify-between border-t border-black/10 pt-2">
                <span>Nasabah</span>
                <span class="font-bold">{{ $withdrawal->collector->name }}</span>
            </div>
            <div class="flex justify-between">
                <span>ID Nasabah</span>
                <span>{{ $withdrawal->collector->collector_number }}</span>
            </div>
        </div>

        <div class="bg-gray-100 p-4 mb-8 text-center rounded">
            <p class="text-xs uppercase text-gray-600 mb-1">Jumlah Penarikan</p>
            <p class="text-2xl font-bold">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</p>
        </div>

        <div class="flex justify-between text-sm mb-12 italic">
            <div class="text-center">
                <p class="mb-12">Nasabah</p>
                <p class="">( ............ )</p>
            </div>
            <div class="text-center">
                <p class="mb-12">Petugas</p>
                <p class="">( {{ auth()->user()->name }} )</p>
            </div>
        </div>

        <div class="text-center border-t-2 border-dashed border-black pt-4">
            <p class="text-[10px]">Terima kasih telah menabung sampah.</p>
            <p class="text-[10px]">Simpan struk ini sebagai bukti sah penarikan.</p>
        </div>
    </div>

    @push('scripts')
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endpush

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .max-w-md, .max-w-md * {
                visibility: visible;
            }
            .max-w-md {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</x-app-layout>
