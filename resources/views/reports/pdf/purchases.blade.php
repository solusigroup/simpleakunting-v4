@extends('reports.pdf.layout')

@section('title', 'Laporan Pembelian - ' . ($company->name ?? 'Perusahaan'))

@section('report-title')
    <h1>LAPORAN PEMBELIAN</h1>
    <h2>Purchase Report</h2>
    <p class="period">Periode {{ \App\Helpers\ReportHelper::formatDate($period['start_date']) }} s/d {{ \App\Helpers\ReportHelper::formatDate($period['end_date']) }}</p>
@endsection

@section('content')
    <table class="financial-table">
        <thead>
            <tr>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 20%;">No. Transaksi</th>
                <th style="width: 30%;">Pemasok</th>
                <th style="width: 15%; text-align: right;">Pajak (Rp)</th>
                <th style="width: 20%; text-align: right;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices ?? [] as $invoice)
            <tr>
                <td>{{ \App\Helpers\ReportHelper::formatDate($invoice->date) }}</td>
                <td>{{ $invoice->number }}</td>
                <td>{{ $invoice->contact ? $invoice->contact->name : '-' }}</td>
                <td style="text-align: right;">{{ \App\Helpers\ReportHelper::formatCurrency($invoice->tax) }}</td>
                <td style="text-align: right;">{{ \App\Helpers\ReportHelper::formatCurrency($invoice->total) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px; color: #999;">Tidak ada data</td>
            </tr>
            @endforelse
            <tr class="grand-total-row">
                <td colspan="3"><strong>TOTAL</strong></td>
                <td class="amount" style="text-align: right;">
                    <strong>{{ \App\Helpers\ReportHelper::formatCurrency($summary['total_tax']) }}</strong>
                </td>
                <td class="amount" style="text-align: right;">
                    <strong>{{ \App\Helpers\ReportHelper::formatCurrency($summary['total_purchases']) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
@endsection
