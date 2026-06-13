@extends('reports.pdf.layout')

@section('title', 'Buku Besar - ' . ($account['name'] ?? 'Akun'))

@section('report-title')
    <h1>BUKU BESAR</h1>
    <h2>{{ $account['code'] }} - {{ $account['name'] }}</h2>
    <p class="period">Periode {{ \App\Helpers\ReportHelper::formatDate($period['start_date']) }} s/d {{ \App\Helpers\ReportHelper::formatDate($period['end_date']) }}</p>
@endsection

@section('content')
    <table class="financial-table">
        <thead>
            <tr>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 18%;">Referensi</th>
                <th style="width: 34%;">Keterangan</th>
                <th style="width: 12%; text-align: right;">Debit (Rp)</th>
                <th style="width: 12%; text-align: right;">Kredit (Rp)</th>
                <th style="width: 12%; text-align: right;">Saldo (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #f9f9f9; font-style: italic;">
                <td>{{ \App\Helpers\ReportHelper::formatDate($period['start_date']) }}</td>
                <td>-</td>
                <td>Saldo Awal</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: right; font-weight: bold;">
                    {{ \App\Helpers\ReportHelper::formatCurrency($beginning_balance) }}
                </td>
            </tr>
            @forelse($transactions ?? [] as $tx)
            <tr>
                <td>{{ \App\Helpers\ReportHelper::formatDate($tx['date']) }}</td>
                <td>{{ $tx['reference'] }}</td>
                <td>{{ $tx['description'] }}{{ $tx['memo'] ? ' ('.$tx['memo'].')' : '' }}</td>
                <td style="text-align: right;">
                    {{ $tx['debit'] != 0 ? \App\Helpers\ReportHelper::formatCurrency($tx['debit']) : '-' }}
                </td>
                <td style="text-align: right;">
                    {{ $tx['credit'] != 0 ? \App\Helpers\ReportHelper::formatCurrency($tx['credit']) : '-' }}
                </td>
                <td style="text-align: right;">
                    {{ \App\Helpers\ReportHelper::formatCurrency($tx['balance']) }}
                </td>
            </tr>
            @empty
            @endforelse
            <tr class="grand-total-row">
                <td>{{ \App\Helpers\ReportHelper::formatDate($period['end_date']) }}</td>
                <td>-</td>
                <td>Saldo Akhir</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: right;">
                    <strong>{{ \App\Helpers\ReportHelper::formatCurrency($ending_balance) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
@endsection
