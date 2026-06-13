@extends('reports.pdf.layout')

@section('title', 'Neraca Saldo - ' . ($company->name ?? 'Perusahaan'))

@section('report-title')
    <h1>NERACA SALDO</h1>
    <h2>Trial Balance</h2>
    <p class="period">Per {{ \App\Helpers\ReportHelper::formatDate($endDate) }}</p>
@endsection

@section('content')
    <table class="financial-table">
        <thead>
            <tr>
                <th style="width: 20%;">Kode Akun</th>
                <th style="width: 40%;">Nama Akun</th>
                <th style="width: 20%; text-align: right;">Debit (Rp)</th>
                <th style="width: 20%; text-align: right;">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accounts ?? [] as $item)
            <tr>
                <td class="code">{{ $item['account_code'] }}</td>
                <td class="account-name">{{ $item['account_name'] }}</td>
                <td class="amount" style="text-align: right;">
                    {{ $item['debit'] != 0 ? \App\Helpers\ReportHelper::formatCurrency($item['debit']) : '-' }}
                </td>
                <td class="amount" style="text-align: right;">
                    {{ $item['credit'] != 0 ? \App\Helpers\ReportHelper::formatCurrency($item['credit']) : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px; color: #999;">Tidak ada data</td>
            </tr>
            @endforelse
            <tr class="grand-total-row">
                <td colspan="2"><strong>TOTAL</strong></td>
                <td class="amount" style="text-align: right;">
                    <strong>{{ \App\Helpers\ReportHelper::formatCurrency($total_debit) }}</strong>
                </td>
                <td class="amount" style="text-align: right;">
                    <strong>{{ \App\Helpers\ReportHelper::formatCurrency($total_credit) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
@endsection
