@extends('reports.excel.layout')

@section('content')
<table>
    <tr>
        <th colspan="4" style="font-weight: bold; font-size: 14pt; text-align: center;">{{ strtoupper($company->name ?? 'Perusahaan') }}</th>
    </tr>
    <tr>
        <th colspan="4" style="font-weight: bold; font-size: 16pt; text-align: center;">LAPORAN PERUBAHAN EKUITAS</th>
    </tr>
    <tr>
        <th colspan="4" style="font-size: 11pt; text-align: center;">
            Periode: {{ \Carbon\Carbon::parse($period['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end_date'])->format('d M Y') }}
        </th>
    </tr>
    @if(isset($unit) && $unit)
    <tr>
        <th colspan="4" style="font-size: 10pt; text-align: center;">Unit Usaha: {{ $unit->name }}</th>
    </tr>
    @endif
    <tr></tr>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff;">Keterangan</th>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff; text-align: right;">Modal Disetor</th>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff; text-align: right;">Laba Ditahan</th>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff; text-align: right;">Total Ekuitas</th>
        </tr>
    </thead>
    <tbody>
        <!-- Saldo Awal -->
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td>Saldo Awal ({{ \Carbon\Carbon::parse($period['start_date'])->format('d M Y') }})</td>
            <td style="text-align: right;">{{ $beginning_capital }}</td>
            <td style="text-align: right;">{{ $beginning_retained }}</td>
            <td style="text-align: right;">{{ $beginning_equity }}</td>
        </tr>

        <!-- Changes -->
        @if(count($changes) > 0)
            <tr style="font-weight: bold; background-color: #e2e8f0;">
                <td colspan="4">Perubahan Selama Periode:</td>
            </tr>
            @foreach($changes as $change)
            <tr>
                <td style="padding-left: 20px;">{{ $change['description'] }}</td>
                <td style="text-align: right;">{{ $change['type'] === 'capital' ? $change['amount'] : 0 }}</td>
                <td style="text-align: right;">{{ $change['type'] === 'retained' ? $change['amount'] : 0 }}</td>
                <td style="text-align: right;">{{ $change['amount'] }}</td>
            </tr>
            @endforeach
        @endif

        <!-- Net Income -->
        <tr>
            <td style="padding-left: 20px;">Laba (Rugi) Periode Berjalan</td>
            <td style="text-align: right;">0</td>
            <td style="text-align: right;">{{ $net_income }}</td>
            <td style="text-align: right;">{{ $net_income }}</td>
        </tr>

        <!-- Saldo Akhir -->
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td>Saldo Akhir ({{ \Carbon\Carbon::parse($period['end_date'])->format('d M Y') }})</td>
            <td style="text-align: right;">{{ $ending_capital }}</td>
            <td style="text-align: right;">{{ $ending_retained }}</td>
            <td style="text-align: right;">{{ $ending_equity }}</td>
        </tr>
    </tbody>
</table>
@endsection
