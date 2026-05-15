<?php

namespace App\Exports;

use App\Models\WasteCollector;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WasteCollectorsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return WasteCollector::orderBy('balance', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID Nasabah',
            'Nama Nasabah',
            'No. Telepon',
            'Alamat',
            'Saldo Tabungan',
        ];
    }

    public function map($collector): array
    {
        return [
            $collector->collector_number,
            $collector->name,
            $collector->phone,
            $collector->address,
            $collector->balance,
        ];
    }
}
