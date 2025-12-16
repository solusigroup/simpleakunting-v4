@extends('reports.pdf.layout')

@section('report-title', 'LAPORAN ARUS KAS')
@section('report-subtitle', 'Metode Langsung')
@section('report-period')
    Periode: 
    @if($period['start_date'])
        {{ \Carbon\Carbon::parse($period['start_date'])->format('d F Y') }} s/d 
    @endif
    {{ \Carbon\Carbon::parse($period['end_date'])->format('d F Y') }}
@endsection

@section('content')
<div class="report-section">
    <!-- Beginning Balance -->
    <div style="padding: 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; margin-bottom: 20px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; font-weight: bold;">Saldo Kas Awal</td>
                <td style="border: none; text-align: right; font-weight: bold; font-size: 12pt;">
                    {{ App\Helpers\ReportHelper::formatCurrency($beginning_balance) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Operating Activities -->
    <div class="section-header bg-primary">
        <h3>AKTIVITAS OPERASIONAL</h3>
    </div>
    <table class="report-table">
        <tbody>
            <tr>
                <td style="width: 70%">Penerimaan Kas dari Operasional</td>
                <td class="text-right" style="color: #2C5F2D; width: 30%">
                    {{ App\Helpers\ReportHelper::formatCurrency($operating['inflow']) }}
                </td>
            </tr>
            <tr>
                <td>Pengeluaran Kas untuk Operasional</td>
                <td class="text-right" style="color: #B91C1C;">
                    ({{ App\Helpers\ReportHelper::formatCurrency($operating['outflow']) }})
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td><strong>Arus Kas Bersih - Operasional</strong></td>
                <td class="text-right" style="color: {{ $operating['net'] >= 0 ? '#2C5F2D' : '#B91C1C' }}">
                    <strong>{{ App\Helpers\ReportHelper::formatCurrency($operating['net']) }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Investing Activities -->
    <div class="section-header" style="background: #3B82F6; margin-top: 20px;">
        <h3>AKTIVITAS INVESTASI</h3>
    </div>
    <table class="report-table">
        <tbody>
            <tr>
                <td style="width: 70%">Penerimaan dari Investasi</td>
                <td class="text-right" style="color: #2C5F2D; width: 30%">
                    {{ App\Helpers\ReportHelper::formatCurrency($investing['inflow']) }}
                </td>
            </tr>
            <tr>
                <td>Pengeluaran untuk Investasi</td>
                <td class="text-right" style="color: #B91C1C;">
                    ({{ App\Helpers\ReportHelper::formatCurrency($investing['outflow']) }})
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td><strong>Arus Kas Bersih - Investasi</strong></td>
                <td class="text-right" style="color: {{ $investing['net'] >= 0 ? '#2C5F2D' : '#B91C1C' }}">
                    <strong>{{ App\Helpers\ReportHelper::formatCurrency($investing['net']) }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Financing Activities -->
    <div class="section-header" style="background: #8B5CF6; margin-top: 20px;">
        <h3>AKTIVITAS PENDANAAN</h3>
    </div>
    <table class="report-table">
        <tbody>
            <tr>
                <td style="width: 70%">Penerimaan dari Pendanaan</td>
                <td class="text-right" style="color: #2C5F2D; width: 30%">
                    {{ App\Helpers\ReportHelper::formatCurrency($financing['inflow']) }}
                </td>
            </tr>
            <tr>
                <td>Pengeluaran untuk Pendanaan</td>
                <td class="text-right" style="color: #B91C1C;">
                    ({{ App\Helpers\ReportHelper::formatCurrency($financing['outflow']) }})
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td><strong>Arus Kas Bersih - Pendanaan</strong></td>
                <td class="text-right" style="color: {{ $financing['net'] >= 0 ? '#2C5F2D' : '#B91C1C' }}">
                    <strong>{{ App\Helpers\ReportHelper::formatCurrency($financing['net']) }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Net Change -->
    <div style="padding: 12px; background: #f0f9f4; border: 1px solid #2C5F2D; border-radius: 6px; margin-top: 20px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; font-weight: bold; font-size: 11pt;">Perubahan Bersih Kas</td>
                <td style="border: none; text-align: right; font-weight: bold; font-size: 13pt; color: {{ $net_change >= 0 ? '#2C5F2D' : '#B91C1C' }}">
                    {{ App\Helpers\ReportHelper::formatCurrency($net_change) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Ending Balance -->
    <div style="padding: 15px; background: #2C5F2D; color: white; border-radius: 6px; margin-top: 20px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; font-weight: bold; font-size: 12pt; color: white;">Saldo Kas Akhir</td>
                <td style="border: none; text-align: right; font-weight: bold; font-size: 14pt; color: white;">
                    {{ App\Helpers\ReportHelper::formatCurrency($ending_balance) }}
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
