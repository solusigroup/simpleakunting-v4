@extends('reports.excel.layout')

@section('content')
<table>
    <tr>
        <th colspan="3" style="font-weight: bold; font-size: 14pt; text-align: center;">{{ strtoupper($company->name ?? 'Perusahaan') }}</th>
    </tr>
    <tr>
        <th colspan="3" style="font-weight: bold; font-size: 16pt; text-align: center;">NERACA</th>
    </tr>
    <tr>
        <th colspan="3" style="font-size: 11pt; text-align: center;">Per {{ \App\Helpers\ReportHelper::formatDate($endDate) }}</th>
    </tr>
    @if(isset($unit) && $unit)
    <tr>
        <th colspan="3" style="font-size: 10pt; text-align: center;">Unit Usaha: {{ $unit->name }}</th>
    </tr>
    @endif
    <tr></tr>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff;">Kode Akun</th>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff;">Nama Akun</th>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff; text-align: right;">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        {{-- ASET --}}
        <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td colspan="3">ASET</td>
        </tr>
        @if(isset($data['sections']['Aset']) && !empty($data['sections']['Aset']))
            @foreach($data['sections']['Aset'] as $item)
            <tr>
                <td>{{ $item['account_code'] ?? '' }}</td>
                <td>{{ $item['account_name'] ?? '' }}</td>
                <td style="text-align: right;">{{ $item['balance'] ?? 0 }}</td>
            </tr>
            @endforeach
        @endif
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td colspan="2">Total Aset</td>
            <td style="text-align: right;">{{ $data['totals']['Aset'] ?? 0 }}</td>
        </tr>

        {{-- KEWAJIBAN --}}
        <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td colspan="3">KEWAJIBAN</td>
        </tr>
        @if(isset($data['sections']['Kewajiban']) && !empty($data['sections']['Kewajiban']))
            @foreach($data['sections']['Kewajiban'] as $item)
            <tr>
                <td>{{ $item['account_code'] ?? '' }}</td>
                <td>{{ $item['account_name'] ?? '' }}</td>
                <td style="text-align: right;">{{ $item['balance'] ?? 0 }}</td>
            </tr>
            @endforeach
        @endif
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td colspan="2">Total Kewajiban</td>
            <td style="text-align: right;">{{ $data['totals']['Kewajiban'] ?? 0 }}</td>
        </tr>

        {{-- EKUITAS --}}
        <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td colspan="3">EKUITAS</td>
        </tr>
        @if(isset($data['sections']['Ekuitas']) && !empty($data['sections']['Ekuitas']))
            @foreach($data['sections']['Ekuitas'] as $item)
            <tr>
                <td>{{ $item['account_code'] ?? '' }}</td>
                <td>{{ $item['account_name'] ?? '' }}</td>
                <td style="text-align: right;">{{ $item['balance'] ?? 0 }}</td>
            </tr>
            @endforeach
        @endif
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td colspan="2">Total Ekuitas</td>
            <td style="text-align: right;">{{ $data['totals']['Ekuitas'] ?? 0 }}</td>
        </tr>

        {{-- TOTAL PASIVA --}}
        <tr style="font-weight: bold; background-color: #94a3b8; color: #ffffff;">
            <td colspan="2">Total Kewajiban & Ekuitas</td>
            <td style="text-align: right;">{{ ($data['totals']['Kewajiban'] ?? 0) + ($data['totals']['Ekuitas'] ?? 0) }}</td>
        </tr>
    </tbody>
</table>
@endsection
