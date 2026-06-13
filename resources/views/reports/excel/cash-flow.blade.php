@extends('reports.excel.layout')

@section('content')
<table>
    <tr>
        <th colspan="2" style="font-weight: bold; font-size: 14pt; text-align: center;">{{ strtoupper($company->name ?? 'Perusahaan') }}</th>
    </tr>
    <tr>
        <th colspan="2" style="font-weight: bold; font-size: 16pt; text-align: center;">LAPORAN ARUS KAS</th>
    </tr>
    <tr>
        <th colspan="2" style="font-size: 11pt; text-align: center;">
            Periode: 
            @if($period['start_date'])
                {{ \Carbon\Carbon::parse($period['start_date'])->format('d F Y') }} s/d 
            @endif
            {{ \Carbon\Carbon::parse($period['end_date'])->format('d F Y') }}
        </th>
    </tr>
    @if(isset($unit) && $unit)
    <tr>
        <th colspan="2" style="font-size: 10pt; text-align: center;">Unit Usaha: {{ $unit->name }}</th>
    </tr>
    @endif
    <tr></tr>
    <tbody>
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td>Saldo Kas Awal</td>
            <td style="text-align: right;">{{ $beginning_balance }}</td>
        </tr>

        {{-- OPERASIONAL --}}
        <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td colspan="2">AKTIVITAS OPERASIONAL</td>
        </tr>
        <tr>
            <td>Penerimaan Kas dari Operasional</td>
            <td style="text-align: right;">{{ $operating['inflow'] }}</td>
        </tr>
        <tr>
            <td>Pengeluaran Kas untuk Operasional</td>
            <td style="text-align: right;">{{ -$operating['outflow'] }}</td>
        </tr>
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td>Arus Kas Bersih - Operasional</td>
            <td style="text-align: right;">{{ $operating['net'] }}</td>
        </tr>

        {{-- INVESTASI --}}
        <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td colspan="2">AKTIVITAS INVESTASI</td>
        </tr>
        <tr>
            <td>Penerimaan dari Investasi</td>
            <td style="text-align: right;">{{ $investing['inflow'] }}</td>
        </tr>
        <tr>
            <td>Pengeluaran untuk Investasi</td>
            <td style="text-align: right;">{{ -$investing['outflow'] }}</td>
        </tr>
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td>Arus Kas Bersih - Investasi</td>
            <td style="text-align: right;">{{ $investing['net'] }}</td>
        </tr>

        {{-- PENDANAAN --}}
        <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td colspan="2">AKTIVITAS PENDANAAN</td>
        </tr>
        <tr>
            <td>Penerimaan dari Pendanaan</td>
            <td style="text-align: right;">{{ $financing['inflow'] }}</td>
        </tr>
        <tr>
            <td>Pengeluaran untuk Pendanaan</td>
            <td style="text-align: right;">{{ -$financing['outflow'] }}</td>
        </tr>
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td>Arus Kas Bersih - Pendanaan</td>
            <td style="text-align: right;">{{ $financing['net'] }}</td>
        </tr>

        {{-- PERUBAHAN BERSIH --}}
        <tr style="font-weight: bold; background-color: #94a3b8; color: #ffffff;">
            <td>Perubahan Bersih Kas</td>
            <td style="text-align: right;">{{ $net_change }}</td>
        </tr>

        {{-- SALDO KAS AKHIR --}}
        <tr style="font-weight: bold; background-color: #2C5F2D; color: #ffffff;">
            <td>Saldo Kas Akhir</td>
            <td style="text-align: right;">{{ $ending_balance }}</td>
        </tr>
    </tbody>
</table>
@endsection
