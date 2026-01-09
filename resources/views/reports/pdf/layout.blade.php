<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Laporan Keuangan')</title>
    <style>
        @page {
            margin: 15mm 15mm 20mm 15mm;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10pt;
            color: #000000;
            line-height: 1.4;
        }
        
        /* Simple Header */
        .header-section {
            width: 100%;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #000000;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .header-table td {
            vertical-align: middle;
        }
        
        .logo-cell {
            width: 80px;
        }
        
        .logo-cell img {
            max-height: 50px;
            max-width: 70px;
        }
        
        .company-info {
            text-align: center;
            padding-left: 10px;
        }
        
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .company-address {
            font-size: 9pt;
            color: #333333;
        }
        
        /* Report Title - Simple */
        .report-title {
            text-align: center;
            margin: 15px 0;
        }
        
        .report-title h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        
        .report-title .period {
            font-size: 10pt;
            margin: 0;
        }
        
        /* Simple Table */
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9pt;
        }
        
        .financial-table thead th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
            background-color: #f5f5f5;
        }
        
        .financial-table thead th.amount-col {
            text-align: right;
        }
        
        .financial-table tbody td {
            padding: 4px 6px;
            border-bottom: 0.5px solid #cccccc;
        }
        
        .financial-table tbody td.code {
            width: 12%;
            font-family: 'Courier New', monospace;
        }
        
        .financial-table tbody td.account-name {
            width: 58%;
        }
        
        .financial-table tbody td.amount {
            width: 30%;
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        /* Section Headers */
        .section-header {
            background-color: #eeeeee !important;
        }
        
        .section-header td {
            font-weight: bold;
            font-size: 10pt;
            padding: 6px !important;
        }
        
        /* Subtotal Rows */
        .subtotal-row td {
            font-weight: bold;
            border-top: 1px solid #999999;
            padding: 5px 6px !important;
        }
        
        /* Grand Total */
        .grand-total-row {
            background-color: #eeeeee !important;
        }
        
        .grand-total-row td {
            font-weight: bold;
            font-size: 10pt;
            padding: 8px 6px !important;
            border-top: 2px solid #000000;
            border-bottom: 2px solid #000000;
        }
        
        /* Negative Values */
        .negative {
            color: #cc0000;
        }
        
        /* Simple Signature Section */
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-location {
            margin-bottom: 20px;
            font-size: 10pt;
            text-align: right;
        }
        
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px 20px;
        }
        
        .signature-title {
            font-size: 9pt;
            margin-bottom: 60px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000000;
            width: 70%;
            margin: 0 auto 5px auto;
        }
        
        .signature-name {
            font-size: 10pt;
            font-weight: bold;
        }
        
        .signature-position {
            font-size: 9pt;
        }
        
        /* Footer - Minimal */
        .report-footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #666666;
            padding-top: 5px;
        }
        
        /* Comparative Tables */
        .comparative-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9pt;
        }
        
        .comparative-table thead th {
            padding: 6px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000000;
            background-color: #f5f5f5;
        }
        
        .comparative-table tbody td {
            padding: 4px 6px;
            border: 0.5px solid #cccccc;
        }
        
        .comparative-table tbody td.variance-positive {
            color: #006600;
        }
        
        .comparative-table tbody td.variance-negative {
            color: #cc0000;
        }
    </style>
</head>
<body>
    {{-- Simple Header with Logo --}}
    <div class="header-section">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if(!empty($company->logo) && file_exists(public_path('storage/' . $company->logo)))
                        <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name }}">
                    @else
                        <div style="width: 50px; height: 50px; background-color: #333333; border-radius: 5px; text-align: center; line-height: 50px; color: white; font-size: 20pt; font-weight: bold;">
                            {{ substr($company->name, 0, 1) }}
                        </div>
                    @endif
                </td>
                <td class="company-info">
                    <div class="company-name">{{ $company->name ?? 'Nama Perusahaan' }}</div>
                    <div class="company-address">
                        @if($company->address){{ $company->address }}@endif
                        @if($company->phone || $company->email)
                            <br>
                            @if($company->phone)Telp: {{ $company->phone }}@endif
                            @if($company->phone && $company->email) | @endif
                            @if($company->email)Email: {{ $company->email }}@endif
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    {{-- Report Title --}}
    <div class="report-title">
        @yield('report-title')
    </div>
    
    {{-- Report Content (Table) --}}
    <div class="report-content">
        @yield('content')
    </div>
    
    @yield('summary')
    
    {{-- Simple Signature Section --}}
    <div class="signature-section">
        <div class="signature-location">
            {{ $company->address ? explode(',', $company->address)[0] : 'Tempat' }}, {{ now()->translatedFormat('d F Y') }}
        </div>
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-title">Mengetahui,</div>
                    <div class="signature-line"></div>
                    <div class="signature-name">(...............................)</div>
                    <div class="signature-position">Direktur</div>
                </td>
                <td>
                    <div class="signature-title">Dibuat oleh,</div>
                    <div class="signature-line"></div>
                    <div class="signature-name">(...............................)</div>
                    <div class="signature-position">Bagian Keuangan</div>
                </td>
            </tr>
        </table>
    </div>
    
    {{-- Minimal Footer --}}
    <div class="report-footer">
        Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
