@extends('reports.pdf.layout')

@section('report-title', 'LABA RUGI KOMPARATIF')
@section('report-subtitle', 'Analisis Perbandingan Periode')
@section('report-period')
    @foreach($periods as $index => $period)
        @if($index > 0) vs @endif
        {{ $period['label'] }}
    @endforeach
@endsection

@section('content')
<div class="report-section">
    <!-- Comparative Table -->
    <table class="report-table comparative-table">
        <thead>
            <tr>
                <th style="width: 30%">Nama Akun</th>
                @foreach($periods as $period)
                <th style="width: 15%; text-align: right">{{ $period['label'] }}</th>
                @endforeach
                <th style="width: 15%; text-align: right">Selisih</th>
                <th style="width: 10%; text-align: right">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['Pendapatan', 'Beban'] as $section)
                <!-- Section Header -->
                <tr class="section-header-row">
                    <td colspan="{{ count($periods) + 3 }}" class="bg-primary">
                        <strong>{{ strtoupper($section) }}</strong>
                    </td>
                </tr>

                <!-- Section Items -->
                @forelse($data['sections'][$section] ?? [] as $item)
                <tr>
                    <td>{{ $item['account_name'] }}</td>
                    @foreach($item['values'] as $value)
                    <td class="text-right">{{ App\Helpers\ReportHelper::formatCurrency($value) }}</td>
                    @endforeach
                    <td class="text-right variance-{{ $item['variance']['trend'] ?? 'stable' }}">
                        {{ App\Helpers\ReportHelper::formatCurrency($item['variance']['absolute'] ?? 0) }}
                        {{ App\Helpers\ReportHelper::getTrendIcon($item['variance']['percentage'] ?? 0) }}
                    </td>
                    <td class="text-right variance-{{ $item['variance']['trend'] ?? 'stable' }}">
                        <strong>{{ App\Helpers\ReportHelper::formatPercentage($item['variance']['percentage'] ?? 0) }}</strong>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($periods) + 3 }}" class="text-center text-muted">Tidak ada data</td>
                </tr>
                @endforelse

                <!-- Section Total -->
                @if(isset($data['totals'][$section]))
                <tr class="total-row">
                    <td><strong>Total {{ $section }}</strong></td>
                    @foreach($data['totals'][$section] as $total)
                    <td class="text-right"><strong>{{ App\Helpers\ReportHelper::formatCurrency($total) }}</strong></td>
                    @endforeach
                    <td class="text-right variance-{{ $data['totals_variance'][$section]['trend'] ?? 'stable' }}">
                        <strong>{{ App\Helpers\ReportHelper::formatCurrency($data['totals_variance'][$section]['absolute'] ?? 0) }}</strong>
                        {{ App\Helpers\ReportHelper::getTrendIcon($data['totals_variance'][$section]['percentage'] ?? 0) }}
                    </td>
                    <td class="text-right variance-{{ $data['totals_variance'][$section]['trend'] ?? 'stable' }}">
                        <strong>{{ App\Helpers\ReportHelper::formatPercentage($data['totals_variance'][$section]['percentage'] ?? 0) }}</strong>
                    </td>
                </tr>
                @endif
            @endforeach

            <!-- Net Profit/Loss Row -->
            <tr class="highlight-row" style="background: #f0f9f4; border-top: 2px solid #2C5F2D;">
                <td><strong>LABA (RUGI) BERSIH</strong></td>
                @php
                    $netProfit1 = ($data['totals']['Pendapatan'][0] ?? 0) - ($data['totals']['Beban'][0] ?? 0);
                    $netProfit2 = ($data['totals']['Pendapatan'][1] ?? 0) - ($data['totals']['Beban'][1] ?? 0);
                    $netVariance = App\Helpers\ReportHelper::calculateVariance($netProfit1, $netProfit2);
                @endphp
                <td class="text-right"><strong style="color: {{ $netProfit1 >= 0 ? '#2C5F2D' : '#B91C1C' }}">{{ App\Helpers\ReportHelper::formatCurrency($netProfit1) }}</strong></td>
                <td class="text-right"><strong style="color: {{ $netProfit2 >= 0 ? '#2C5F2D' : '#B91C1C' }}">{{ App\Helpers\ReportHelper::formatCurrency($netProfit2) }}</strong></td>
                <td class="text-right variance-{{ $netVariance['trend'] }}">
                    <strong>{{ App\Helpers\ReportHelper::formatCurrency($netVariance['absolute']) }}</strong>
                    {{ App\Helpers\ReportHelper::getTrendIcon($netVariance['percentage']) }}
                </td>
                <td class="text-right variance-{{ $netVariance['trend'] }}">
                    <strong>{{ App\Helpers\ReportHelper::formatPercentage($netVariance['percentage']) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Summary Box -->
    @if(isset($data['summary']))
    <div class="summary-box" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">
        <h4 style="margin: 0 0 10px 0; color: #2C5F2D; font-size: 11pt;">Ringkasan Analisis</h4>
        <table style="width: 100%; font-size: 9pt; border: none;">
            <tr>
                <td style="border: none; padding: 4px;">Total Akun Dianalisis:</td>
                <td style="border: none; padding: 4px; text-align: right;"><strong>{{ $data['summary']['total_accounts'] }}</strong></td>
                <td style="border: none; padding: 4px;">Akun Naik:</td>
                <td style="border: none; padding: 4px; text-align: right; color: #2C5F2D;"><strong>{{ $data['summary']['accounts_increased'] }}</strong></td>
            </tr>
            <tr>
                <td style="border: none; padding: 4px;">Akun Turun:</td>
                <td style="border: none; padding: 4px; text-align: right; color: #B91C1C;"><strong>{{ $data['summary']['accounts_decreased'] }}</strong></td>
                <td style="border: none; padding: 4px;">Akun Stabil:</td>
                <td style="border: none; padding: 4px; text-align: right; color: #6B7280;"><strong>{{ $data['summary']['accounts_stable'] }}</strong></td>
            </tr>
        </table>
    </div>
    @endif
</div>
@endsection
