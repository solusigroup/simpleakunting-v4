@extends('reports.excel.layout')

@section('content')
<table>
    <tr>
        <th colspan="2" style="font-weight: bold; font-size: 14pt; text-align: center;">{{ strtoupper($company->name ?? 'Perusahaan') }}</th>
    </tr>
    <tr>
        <th colspan="2" style="font-weight: bold; font-size: 16pt; text-align: center;">LABA RUGI</th>
    </tr>
    <tr>
        <th colspan="2" style="font-size: 11pt; text-align: center;">
            Periode: 
            @if($startDate)
                {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d 
            @endif
            {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
        </th>
    </tr>
    @if(isset($unit) && $unit)
    <tr>
        <th colspan="2" style="font-size: 10pt; text-align: center;">Unit Usaha: {{ $unit->name }}</th>
    </tr>
    @endif
    <tr></tr>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff;">Nama Akun</th>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff; text-align: right;">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        {{-- PENDAPATAN --}}
        <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td colspan="2">PENDAPATAN</td>
        </tr>
        @if(isset($sections['Pendapatan']) && !empty($sections['Pendapatan']))
            @foreach($sections['Pendapatan'] as $item)
            <tr>
                <td>{{ $item['account_code'] }} - {{ $item['account_name'] }}</td>
                <td style="text-align: right;">{{ $item['balance'] }}</td>
            </tr>
            @endforeach
        @endif
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td>Total Pendapatan</td>
            <td style="text-align: right;">{{ $totalRevenue }}</td>
        </tr>

        {{-- BEBAN --}}
        <tr style="font-weight: bold; background-color: #e2e8f0;">
            <td colspan="2">BEBAN</td>
        </tr>
        @if(isset($sections['Beban']) && !empty($sections['Beban']))
            @foreach($sections['Beban'] as $item)
            <tr>
                <td>{{ $item['account_code'] }} - {{ $item['account_name'] }}</td>
                <td style="text-align: right;">{{ $item['balance'] }}</td>
            </tr>
            @endforeach
        @endif
        <tr style="font-weight: bold; background-color: #cbd5e1;">
            <td>Total Beban</td>
            <td style="text-align: right;">{{ $totalExpense }}</td>
        </tr>

        {{-- LABA RUGI BERSIH --}}
        <tr style="font-weight: bold; background-color: #94a3b8; color: #ffffff;">
            <td>{{ $netProfit >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</td>
            <td style="text-align: right;">{{ $netProfit }}</td>
        </tr>
    </tbody>
</table>
@endsection
