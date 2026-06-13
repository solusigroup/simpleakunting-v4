@extends('reports.excel.layout')

@section('content')
<table>
    <tr>
        <th colspan="{{ count($periods) + 3 }}" style="font-weight: bold; font-size: 14pt; text-align: center;">{{ strtoupper($company->name ?? 'Perusahaan') }}</th>
    </tr>
    <tr>
        <th colspan="{{ count($periods) + 3 }}" style="font-weight: bold; font-size: 16pt; text-align: center;">LABA RUGI KOMPARATIF</th>
    </tr>
    <tr>
        <th colspan="{{ count($periods) + 3 }}" style="font-size: 11pt; text-align: center;">
            @foreach($periods as $index => $period)
                @if($index > 0) vs @endif
                {{ $period['label'] }}
            @endforeach
        </th>
    </tr>
    <tr></tr>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff;">Nama Akun</th>
            @foreach($periods as $period)
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff; text-align: right;">{{ $period['label'] }}</th>
            @endforeach
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff; text-align: right;">Selisih (Absolut)</th>
            <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff; text-align: right;">%</th>
        </tr>
    </thead>
    <tbody>
        @foreach(['Pendapatan', 'Beban'] as $section)
            {{-- Section Header --}}
            <tr style="font-weight: bold; background-color: #e2e8f0;">
                <td colspan="{{ count($periods) + 3 }}">{{ strtoupper($section) }}</td>
            </tr>

            {{-- Section Items --}}
            @if(isset($data['sections'][$section]) && !empty($data['sections'][$section]))
                @foreach($data['sections'][$section] as $item)
                <tr>
                    <td>{{ $item['account_name'] }}</td>
                    @foreach($item['values'] as $value)
                    <td style="text-align: right;">{{ $value }}</td>
                    @endforeach
                    <td style="text-align: right;">{{ $item['variance']['absolute'] ?? 0 }}</td>
                    <td style="text-align: right;">{{ ($item['variance']['percentage'] ?? 0) / 100 }}</td>
                </tr>
                @endforeach
            @endif

            {{-- Section Total --}}
            @if(isset($data['totals'][$section]))
            <tr style="font-weight: bold; background-color: #cbd5e1;">
                <td>Total {{ $section }}</td>
                @foreach($data['totals'][$section] as $total)
                <td style="text-align: right;">{{ $total }}</td>
                @endforeach
                <td style="text-align: right;">{{ $data['totals_variance'][$section]['absolute'] ?? 0 }}</td>
                <td style="text-align: right;">{{ ($data['totals_variance'][$section]['percentage'] ?? 0) / 100 }}</td>
            </tr>
            @endif
        @endforeach

        {{-- Laba (Rugi) Bersih --}}
        @php
            $netProfit1 = ($data['totals']['Pendapatan'][0] ?? 0) - ($data['totals']['Beban'][0] ?? 0);
            $netProfit2 = ($data['totals']['Pendapatan'][1] ?? 0) - ($data['totals']['Beban'][1] ?? 0);
            $netVariance = App\Helpers\ReportHelper::calculateVariance($netProfit1, $netProfit2);
        @endphp
        <tr style="font-weight: bold; background-color: #94a3b8; color: #ffffff;">
            <td>LABA (RUGI) BERSIH</td>
            <td style="text-align: right;">{{ $netProfit1 }}</td>
            <td style="text-align: right;">{{ $netProfit2 }}</td>
            <td style="text-align: right;">{{ $netVariance['absolute'] }}</td>
            <td style="text-align: right;">{{ $netVariance['percentage'] / 100 }}</td>
        </tr>
    </tbody>
</table>
@endsection
