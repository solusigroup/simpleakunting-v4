@extends('reports.pdf.layout')

@section('title', 'Neraca Komparatif - ' . ($company->name ?? 'Perusahaan'))

@section('report-title')
    <h1>NERACA KOMPARATIF</h1>
    <h2>Analisis Perbandingan Periode</h2>
    <p class="period" style="font-size: 10pt;">
        @foreach($periods as $index => $period)
            <strong>Periode {{ $index + 1 }}:</strong> {{ \App\Helpers\ReportHelper::formatDate($period['start_date']) }} - {{ \App\Helpers\ReportHelper::formatDate($period['end_date']) }}
            @if($index < count($periods) - 1) | @endif
        @endforeach
    </p>
@endsection

@section('content')
    <table class="comparative-table">
        <thead>
            <tr class="header-row-1">
                <th rowspan="2" style="width: 30%;">Nama Akun</th>
                @foreach($periods as $period)
                <th style="width: {{ 50 / count($periods) }}%;">{{ $period['label'] ?? 'Periode ' . ($loop->iteration) }}</th>
                @endforeach
                <th rowspan="2" style="width: 12%;">Selisih (Rp)</th>
                <th rowspan="2" style="width: 8%;">Perubahan<br>(%)</th>
            </tr>
        </thead>
        <tbody>
            {{-- ASET Section --}}
            <tr class="section-header">
                <td colspan="{{ 3 + count($periods) }}"><strong>ASET</strong></td>
            </tr>
            @if(isset($data['sections']['Aset']) && !empty($data['sections']['Aset']))
                @foreach($data['sections']['Aset'] as $item)
                <tr>
                    <td class="account-name">{{ $item['account_name'] ?? '' }}</td>
                    @foreach($item['values'] as $value)
                    <td class="amount">{{ \App\Helpers\ReportHelper::formatCurrency($value) }}</td>
                    @endforeach
                    <td class="amount variance-{{ $item['variance']['trend'] ?? 'stable' }}">
                        {{ \App\Helpers\ReportHelper::formatCurrency($item['variance']['absolute'] ?? 0) }}
                        {{ \App\Helpers\ReportHelper::getTrendIcon($item['variance']['percentage'] ?? 0) }}
                    </td>
                    <td class="amount variance-{{ $item['variance']['trend'] ?? 'stable' }}">
                        {{ \App\Helpers\ReportHelper::formatPercentage($item['variance']['percentage'] ?? 0) }}
                    </td>
                </tr>
                @endforeach
            @endif
            <tr class="grand-total-row">
                <td><strong>Total Aset</strong></td>
                @foreach($data['totals']['Aset'] as $total)
                <td class="amount"><strong>{{ \App\Helpers\ReportHelper::formatCurrency($total) }}</strong></td>
                @endforeach
                <td class="amount"><strong>{{ \App\Helpers\ReportHelper::formatCurrency($data['totals_variance']['Aset']['absolute'] ?? 0) }}</strong></td>
                <td class="amount"><strong>{{ \App\Helpers\ReportHelper::formatPercentage($data['totals_variance']['Aset']['percentage'] ?? 0) }}</strong></td>
            </tr>
            
            {{-- KEWAJIBAN Section --}}
            <tr class="section-header">
                <td colspan="{{ 3 + count($periods) }}"><strong>KEWAJIBAN</strong></td>
            </tr>
            @if(isset($data['sections']['Kewajiban']) && !empty($data['sections']['Kewajiban']))
                @foreach($data['sections']['Kewajiban'] as $item)
                <tr>
                    <td class="account-name">{{ $item['account_name'] ?? '' }}</td>
                    @foreach($item['values'] as $value)
                    <td class="amount">{{ \App\Helpers\ReportHelper::formatCurrency($value) }}</td>
                    @endforeach
                    <td class="amount variance-{{ $item['variance']['trend'] ?? 'stable' }}">
                        {{ \App\Helpers\ReportHelper::formatCurrency($item['variance']['absolute'] ?? 0) }}
                        {{ \App\Helpers\ReportHelper::getTrendIcon($item['variance']['percentage'] ?? 0) }}
                    </td>
                    <td class="amount variance-{{ $item['variance']['trend'] ?? 'stable' }}">
                        {{ \App\Helpers\ReportHelper::formatPercentage($item['variance']['percentage'] ?? 0) }}
                    </td>
                </tr>
                @endforeach
            @endif
            <tr class="grand-total-row">
                <td><strong>Total Kewajiban</strong></td>
                @foreach($data['totals']['Kewajiban'] as $total)
                <td class="amount"><strong>{{ \App\Helpers\ReportHelper::formatCurrency($total) }}</strong></td>
                @endforeach
                <td class="amount"><strong>{{ \App\Helpers\ReportHelper::formatCurrency($data['totals_variance']['Kewajiban']['absolute'] ?? 0) }}</strong></td>
                <td class="amount"><strong>{{ \App\Helpers\ReportHelper::formatPercentage($data['totals_variance']['Kewajiban']['percentage'] ?? 0) }}</strong></td>
            </tr>
            
            {{-- EKUITAS Section --}}
            <tr class="section-header">
                <td colspan="{{ 3 + count($periods) }}"><strong>EKUITAS</strong></td>
            </tr>
            @if(isset($data['sections']['Ekuitas']) && !empty($data['sections']['Ekuitas']))
                @foreach($data['sections']['Ekuitas'] as $item)
                <tr>
                    <td class="account-name">{{ $item['account_name'] ?? '' }}</td>
                    @foreach($item['values'] as $value)
                    <td class="amount">{{ \App\Helpers\ReportHelper::formatCurrency($value) }}</td>
                    @endforeach
                    <td class="amount variance-{{ $item['variance']['trend'] ?? 'stable' }}">
                        {{ \App\Helpers\ReportHelper::formatCurrency($item['variance']['absolute'] ?? 0) }}
                        {{ \App\Helpers\ReportHelper::getTrendIcon($item['variance']['percentage'] ?? 0) }}
                    </td>
                    <td class="amount variance-{{ $item['variance']['trend'] ?? 'stable' }}">
                        {{ \App\Helpers\ReportHelper::formatPercentage($item['variance']['percentage'] ?? 0) }}
                    </td>
                </tr>
                @endforeach
            @endif
            <tr class="grand-total-row">
                <td><strong>Total Ekuitas</strong></td>
                @foreach($data['totals']['Ekuitas'] as $total)
                <td class="amount"><strong>{{ \App\Helpers\ReportHelper::formatCurrency($total) }}</strong></td>
                @endforeach
                <td class="amount"><strong>{{ \App\Helpers\ReportHelper::formatCurrency($data['totals_variance']['Ekuitas']['absolute'] ?? 0) }}</strong></td>
                <td class="amount"><strong>{{ \App\Helpers\ReportHelper::formatPercentage($data['totals_variance']['Ekuitas']['percentage'] ?? 0) }}</strong></td>
            </tr>
        </tbody>
    </table>
@endsection

@section('summary')
    @if(isset($data['summary']))
    <div class="summary-box">
        <h3>RINGKASAN ANALISIS KOMPARATIF</h3>
        <p><strong>Total Aset:</strong> {{ \App\Helpers\ReportHelper::formatPercentage($data['totals_variance']['Aset']['percentage'] ?? 0) }} ({{ $data['totals_variance']['Aset']['absolute'] >= 0 ? 'Naik' : 'Turun' }} {{ \App\Helpers\ReportHelper::formatCurrency(abs($data['totals_variance']['Aset']['absolute'] ?? 0)) }})</p>
        <p><strong>Total Kewajiban:</strong> {{ \App\Helpers\ReportHelper::formatPercentage($data['totals_variance']['Kewajiban']['percentage'] ?? 0) }} ({{ $data['totals_variance']['Kewajiban']['absolute'] >= 0 ? 'Naik' : 'Turun' }} {{ \App\Helpers\ReportHelper::formatCurrency(abs($data['totals_variance']['Kewajiban']['absolute'] ?? 0)) }})</p>
        <p><strong>Total Ekuitas:</strong> {{ \App\Helpers\ReportHelper::formatPercentage($data['totals_variance']['Ekuitas']['percentage'] ?? 0) }} ({{ $data['totals_variance']['Ekuitas']['absolute'] >= 0 ? 'Naik' : 'Turun' }} {{ \App\Helpers\ReportHelper::formatCurrency(abs($data['totals_variance']['Ekuitas']['absolute'] ?? 0)) }})</p>
        <hr style="margin: 10px 0; border: 0; border-top: 1px solid #CCCCCC;">
        <p><strong>Akun yang Meningkat:</strong> {{ $data['summary']['accounts_increased'] ?? 0 }} akun ({{ $data['summary']['total_accounts'] > 0 ? round(($data['summary']['accounts_increased'] ?? 0) / $data['summary']['total_accounts'] * 100) : 0 }}%)</p>
        <p><strong>Akun yang Menurun:</strong> {{ $data['summary']['accounts_decreased'] ?? 0 }} akun ({{ $data['summary']['total_accounts'] > 0 ? round(($data['summary']['accounts_decreased'] ?? 0) / $data['summary']['total_accounts'] * 100) : 0 }}%)</p>
        <p><strong>Akun Stabil:</strong> {{ $data['summary']['accounts_stable'] ?? 0 }} akun ({{ $data['summary']['total_accounts'] > 0 ? round(($data['summary']['accounts_stable'] ?? 0) / $data['summary']['total_accounts'] * 100) : 0 }}%)</p>
        <p style="margin-top: 10px;"><strong>Rata-rata Pertumbuhan:</strong> {{ \App\Helpers\ReportHelper::formatPercentage($data['summary']['avg_growth_rate'] ?? 0) }}</p>
    </div>
    @endif
@endsection
