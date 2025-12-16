@extends('reports.pdf.layout')

@section('content')
<style>
    .equity-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .equity-table th, .equity-table td { border: 1px solid #ddd; padding: 10px; text-align: right; }
    .equity-table th { background-color: #2d2d2d; color: white; text-align: center; }
    .equity-table td:first-child { text-align: left; }
    .row-header { background-color: #f5f5f5; font-weight: bold; }
    .row-total { background-color: #d4edda; font-weight: bold; }
    .positive { color: #28a745; }
    .negative { color: #dc3545; }
    .amount { font-family: monospace; }
    .section-title { font-weight: bold; background-color: #e9ecef; }
</style>

<div class="report-title">LAPORAN PERUBAHAN EKUITAS</div>
<div class="report-subtitle">Statement of Changes in Equity</div>
<div class="report-period">
    Periode: {{ \Carbon\Carbon::parse($period['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end_date'])->format('d M Y') }}
</div>

<table class="equity-table">
    <thead>
        <tr>
            <th style="width: 40%;">Keterangan</th>
            <th style="width: 20%;">Modal Disetor</th>
            <th style="width: 20%;">Laba Ditahan</th>
            <th style="width: 20%;">Total Ekuitas</th>
        </tr>
    </thead>
    <tbody>
        <!-- Beginning Balance -->
        <tr class="row-header">
            <td>Saldo Awal ({{ \Carbon\Carbon::parse($period['start_date'])->format('d M Y') }})</td>
            <td class="amount">{{ App\Helpers\ReportHelper::formatCurrency($beginning_capital) }}</td>
            <td class="amount">{{ App\Helpers\ReportHelper::formatCurrency($beginning_retained) }}</td>
            <td class="amount" style="font-weight: bold;">{{ App\Helpers\ReportHelper::formatCurrency($beginning_equity) }}</td>
        </tr>

        <!-- Changes -->
        @if(count($changes) > 0)
            <tr class="section-title">
                <td colspan="4">Perubahan Selama Periode:</td>
            </tr>
            @foreach($changes as $change)
            <tr>
                <td style="padding-left: 20px;">{{ $change['description'] }}</td>
                <td class="amount {{ $change['amount'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $change['type'] === 'capital' ? App\Helpers\ReportHelper::formatCurrency($change['amount']) : '-' }}
                </td>
                <td class="amount {{ $change['amount'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $change['type'] === 'retained' ? App\Helpers\ReportHelper::formatCurrency($change['amount']) : '-' }}
                </td>
                <td class="amount {{ $change['amount'] >= 0 ? 'positive' : 'negative' }}">
                    {{ App\Helpers\ReportHelper::formatCurrency($change['amount']) }}
                </td>
            </tr>
            @endforeach
        @endif

        <!-- Net Income -->
        <tr>
            <td style="padding-left: 20px;">Laba (Rugi) Periode Berjalan</td>
            <td class="amount">-</td>
            <td class="amount {{ $net_income >= 0 ? 'positive' : 'negative' }}">
                {{ App\Helpers\ReportHelper::formatCurrency($net_income) }}
            </td>
            <td class="amount {{ $net_income >= 0 ? 'positive' : 'negative' }}">
                {{ App\Helpers\ReportHelper::formatCurrency($net_income) }}
            </td>
        </tr>

        <!-- Ending Balance -->
        <tr class="row-total">
            <td>Saldo Akhir ({{ \Carbon\Carbon::parse($period['end_date'])->format('d M Y') }})</td>
            <td class="amount">{{ App\Helpers\ReportHelper::formatCurrency($ending_capital) }}</td>
            <td class="amount">{{ App\Helpers\ReportHelper::formatCurrency($ending_retained) }}</td>
            <td class="amount" style="font-size: 14px;">{{ App\Helpers\ReportHelper::formatCurrency($ending_equity) }}</td>
        </tr>
    </tbody>
</table>

<!-- Summary Box -->
<div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;">
    <h4 style="margin: 0 0 10px 0; color: #333;">Ringkasan Perubahan Ekuitas</h4>
    <table style="width: 100%;">
        <tr>
            <td style="width: 50%; padding: 5px 0;">Modal Awal:</td>
            <td style="text-align: right; font-family: monospace;">{{ App\Helpers\ReportHelper::formatCurrency($beginning_equity) }}</td>
        </tr>
        <tr>
            <td style="padding: 5px 0;">Penambahan:</td>
            <td style="text-align: right; font-family: monospace; color: #28a745;">+ {{ App\Helpers\ReportHelper::formatCurrency(max(0, $net_income) + array_sum(array_map(fn($c) => max(0, $c['amount']), $changes))) }}</td>
        </tr>
        <tr>
            <td style="padding: 5px 0;">Pengurangan:</td>
            <td style="text-align: right; font-family: monospace; color: #dc3545;">- {{ App\Helpers\ReportHelper::formatCurrency(abs(min(0, $net_income)) + array_sum(array_map(fn($c) => abs(min(0, $c['amount'])), $changes))) }}</td>
        </tr>
        <tr style="border-top: 2px solid #333; font-weight: bold;">
            <td style="padding: 10px 0 5px 0;">Modal Akhir:</td>
            <td style="text-align: right; font-family: monospace; font-size: 16px; color: #28a745;">{{ App\Helpers\ReportHelper::formatCurrency($ending_equity) }}</td>
        </tr>
    </table>
</div>

@include('reports.pdf.partials.signature')
@endsection
