@extends('reports.pdf.layout')

@section('title', 'Neraca - ' . ($company->name ?? 'Perusahaan'))

@section('report-title')
    <h1>NERACA</h1>
    <h2>Laporan Posisi Keuangan</h2>
    <p class="period">Per {{ \App\Helpers\ReportHelper::formatDate($endDate) }}</p>
@endsection

@section('content')
    <table class="financial-table">
        <thead>
            <tr>
                <th style="width: 15%;">Kode</th>
                <th style="width: 55%;">Nama Akun</th>
                <th class="amount-col" style="width: 30%;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            {{-- ASET Section --}}
            <tr class="section-header">
                <td colspan="3">ASET</td>
            </tr>
            @if(isset($data['sections']['Aset']) && !empty($data['sections']['Aset']))
                @foreach($data['sections']['Aset'] as $item)
                <tr>
                    <td class="code">{{ $item['account_code'] ?? '' }}</td>
                    <td class="account-name">{{ $item['account_name'] ?? '' }}</td>
                    <td class="amount{{ $item['balance'] < 0 ? ' negative' : '' }}">
                        {{ \App\Helpers\ReportHelper::formatCurrency($item['balance'] ?? 0) }}
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px; color: #999;">Tidak ada data</td>
                </tr>
            @endif
            <tr class="grand-total-row">
                <td colspan="2"><strong>Total Aset</strong></td>
                <td class="amount">
                    <strong>{{ \App\Helpers\ReportHelper::formatCurrency($data['totals']['Aset'] ?? 0) }}</strong>
                </td>
            </tr>
            
            {{-- KEWAJIBAN Section --}}
            <tr class="section-header">
                <td colspan="3">KEWAJIBAN</td>
            </tr>
            @if(isset($data['sections']['Kewajiban']) && !empty($data['sections']['Kewajiban']))
                @foreach($data['sections']['Kewajiban'] as $item)
                <tr>
                    <td class="code">{{ $item['account_code'] ?? '' }}</td>
                    <td class="account-name">{{ $item['account_name'] ?? '' }}</td>
                    <td class="amount{{ $item['balance'] < 0 ? ' negative' : '' }}">
                        {{ \App\Helpers\ReportHelper::formatCurrency($item['balance'] ?? 0) }}
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px; color: #999;">Tidak ada data</td>
                </tr>
            @endif
            <tr class="grand-total-row">
                <td colspan="2"><strong>Total Kewajiban</strong></td>
                <td class="amount">
                    <strong>{{ \App\Helpers\ReportHelper::formatCurrency($data['totals']['Kewajiban'] ?? 0) }}</strong>
                </td>
            </tr>
            
            {{-- EKUITAS Section --}}
            <tr class="section-header">
                <td colspan="3">EKUITAS</td>
            </tr>
            @if(isset($data['sections']['Ekuitas']) && !empty($data['sections']['Ekuitas']))
                @foreach($data['sections']['Ekuitas'] as $item)
                <tr>
                    <td class="code">{{ $item['account_code'] ?? '' }}</td>
                    <td class="account-name">{{ $item['account_name'] ?? '' }}</td>
                    <td class="amount{{ $item['balance'] < 0 ? ' negative' : '' }}">
                        {{ \App\Helpers\ReportHelper::formatCurrency($item['balance'] ?? 0) }}
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px; color: #999;">Tidak ada data</td>
                </tr>
            @endif
            <tr class="grand-total-row">
                <td colspan="2"><strong>Total Ekuitas</strong></td>
                <td class="amount">
                    <strong>{{ \App\Helpers\ReportHelper::formatCurrency($data['totals']['Ekuitas'] ?? 0) }}</strong>
                </td>
            </tr>
            
            {{-- Total Kewajiban + Ekuitas --}}
            <tr class="subtotal-row">
                <td colspan="2"><strong>Total Kewajiban + Ekuitas</strong></td>
                <td class="amount">
                    <strong>{{ \App\Helpers\ReportHelper::formatCurrency(($data['totals']['Kewajiban'] ?? 0) + ($data['totals']['Ekuitas'] ?? 0)) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
    
    @if(isset($data['is_balanced']) && !$data['is_balanced'])
    <div style="background-color: #FFEBEE; border: 1px solid #D32F2F; padding: 10px; margin: 20px 0; text-align: center; color: #D32F2F; font-weight: bold;">
        ⚠️ PERHATIAN: Neraca tidak seimbang! Periksa kembali jurnal Anda.
    </div>
    @endif
@endsection
