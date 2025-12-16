@extends('reports.pdf.layout')

@section('report-title', 'LABA RUGI')
@section('report-subtitle', 'Laporan Aktivitas Usaha')
@section('report-period')
    Periode: 
    @if($startDate)
        {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d 
    @endif
    {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
@endsection

@section('content')
<div class="report-section">
    <!-- Pendapatan -->
    <div class="section-header bg-primary">
        <h3>PENDAPATAN</h3>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 60%">Nama Akun</th>
                <th style="width: 40%; text-align: right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sections['Pendapatan'] ?? [] as $item)
            <tr>
                <td>{{ $item['account_code'] }} - {{ $item['account_name'] }}</td>
                <td class="text-right">{{ App\Helpers\ReportHelper::formatCurrency($item['balance']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="text-center text-muted">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td><strong>Total Pendapatan</strong></td>
                <td class="text-right"><strong>{{ App\Helpers\ReportHelper::formatCurrency($totalRevenue) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Beban -->
    <div class="section-header bg-danger" style="margin-top: 20px">
        <h3>BEBAN</h3>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 60%">Nama Akun</th>
                <th style="width: 40%; text-align: right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sections['Beban'] ?? [] as $item)
            <tr>
                <td>{{ $item['account_code'] }} - {{ $item['account_name'] }}</td>
                <td class="text-right">{{ App\Helpers\ReportHelper::formatCurrency($item['balance']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="text-center text-muted">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td><strong>Total Beban</strong></td>
                <td class="text-right"><strong>{{ App\Helpers\ReportHelper::formatCurrency($totalExpense) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Net Profit/Loss -->
    <div style="margin-top: 30px; padding: 15px; background: #f0f9f4; border: 2px solid #2C5F2D; border-radius: 8px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 60%; border: none; padding: 8px; font-size: 14pt; font-weight: bold; color: #2C5F2D;">
                    {{ $netProfit >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}
                </td>
                <td style="width: 40%; border: none; padding: 8px; text-align: right; font-size: 16pt; font-weight: bold; color: {{ $netProfit >= 0 ? '#2C5F2D' : '#B91C1C' }};">
                    {{ App\Helpers\ReportHelper::formatCurrency($netProfit) }}
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
