@extends('reports.pdf.layout')

@section('title', 'Laporan Jurnal - ' . ($company->name ?? 'Perusahaan'))

@section('report-title')
    <h1>LAPORAN JURNAL</h1>
    <h2>Daftar Transaksi Jurnal</h2>
    <p class="period">Periode {{ \App\Helpers\ReportHelper::formatDate($period['start_date']) }} s/d {{ \App\Helpers\ReportHelper::formatDate($period['end_date']) }}</p>
@endsection

@section('content')
    <table class="financial-table">
        <thead>
            <tr>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 18%;">No. Jurnal</th>
                <th style="width: 46%;">Akun & Keterangan</th>
                <th style="width: 12%; text-align: right;">Debit (Rp)</th>
                <th style="width: 12%; text-align: right;">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journals ?? [] as $journal)
                <tr style="background-color: #f3f4f6; font-weight: bold;">
                    <td>{{ \App\Helpers\ReportHelper::formatDate($journal->date) }}</td>
                    <td>{{ $journal->reference }}</td>
                    <td>
                        {{ $journal->description }}
                        @if($journal->businessUnit)
                            <span style="color: #666; font-size: 8pt; font-weight: normal;">(Unit: {{ $journal->businessUnit->name }})</span>
                        @endif
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach($journal->items as $item)
                <tr>
                    <td></td>
                    <td></td>
                    <td style="padding-left: 15px; {{ $item->credit > 0 ? 'padding-left: 35px;' : '' }}">
                        {{ $item->account->code }} - {{ $item->account->name }}
                        @if($item->memo)
                            <br><span style="color: #666; font-size: 8.5pt; font-style: italic;">{{ $item->memo }}</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        {{ $item->debit != 0 ? \App\Helpers\ReportHelper::formatCurrency($item->debit) : '-' }}
                    </td>
                    <td style="text-align: right;">
                        {{ $item->credit != 0 ? \App\Helpers\ReportHelper::formatCurrency($item->credit) : '-' }}
                    </td>
                </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #999;">Tidak ada data</td>
                </tr>
            @endforelse
            <tr class="grand-total-row">
                <td colspan="3"><strong>TOTAL</strong></td>
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
