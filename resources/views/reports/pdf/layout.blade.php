<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Laporan Keuangan')</title>
    <style>
        @page {
            margin: 20mm 15mm 25mm 15mm;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10pt;
            color: #333333;
            line-height: 1.4;
        }
        
        /* Header Styles */
        .header-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333333;
        }
        
        .header-logo {
            display: table-cell;
            width: 100px;
            vertical-align: middle;
        }
        
        .header-logo img {
            max-height: 60px;
            max-width: 90px;
        }
        
        .header-info {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            padding-left: 20px;
        }
        
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 3px;
        }
        
        .company-details {
            font-size: 9pt;
            color: #666666;
            line-height: 1.3;
        }
        
       /* Report Title */
        .report-title {
            text-align: center;
            margin: 25px 0;
            padding: 15px 0;
            background-color: #F5F5F5;
        }
        
        .report-title h1 {
            font-size: 18pt;
            font-weight: bold;
            color: #2C5F2D;
            margin: 0 0 8px 0;
            letter-spacing: 1px;
        }
        
        .report-title h2 {
            font-size: 12pt;
            font-weight: normal;
            color: #333333;
            margin: 0 0 5px 0;
        }
        
        .report-title .period {
            font-size: 11pt;
            font-weight: bold;
            color: #555555;
            margin: 0;
        }
        
        /* Table Styles */
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 9.5pt;
        }
        
        .financial-table thead tr {
            background-color: #2C5F2D;
            color: white;
        }
        
        .financial-table thead th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2C5F2D;
        }
        
        .financial-table thead th.amount-col {
            text-align: right;
        }
        
        .financial-table tbody tr:nth-child(even) {
            background-color: #F9F9F9;
        }
        
        .financial-table tbody td {
            padding: 6px 8px;
            border: 0.5px solid #CCCCCC;
        }
        
        .financial-table tbody td.code {
            width: 15%;
            font-family: 'Courier New', monospace;
        }
        
        .financial-table tbody td.account-name {
            width: 55%;
        }
        
        .financial-table tbody td.amount {
            width: 30%;
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        /* Section Headers */
        .section-header {
            background-color: #E8F5E9 !important;
            border-top: 2px solid #2C5F2D !important;
        }
        
        .section-header td {
            font-weight: bold;
            font-size: 11pt;
            padding: 8px !important;
            color: #2C5F2D;
            letter-spacing: 0.5px;
        }
        
        /* Subtotal Rows */
        .subtotal-row {
            background-color: #F5F5F5 !important;
            border-top: 1px solid #999999 !important;
        }
        
        .subtotal-row td {
            font-weight: bold;
            font-style: italic;
            padding: 7px 8px !important;
        }
        
        /* Grand Total */
        .grand-total-row {
            background-color: #2C5F2D !important;
            color: white !important;
            border: 2px solid #2C5F2D !important;
        }
        
        .grand-total-row td {
            font-weight: bold;
            font-size: 11pt;
            padding: 10px 8px !important;
            color: white !important;
        }
        
        /* Negative Values */
        .negative {
            color: #D32F2F;
        }
        
        /* Signature Section */
        .signature-section {
            margin-top: 40px;
            padding-top: 20px;
        }
        
        .signature-location {
            margin-bottom: 20px;
            font-size: 10pt;
        }
        
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 15px;
            border: 1px solid #999999;
        }
        
        .signature-role {
            font-size: 10pt;
            font-weight: normal;
            margin-bottom: 5px;
        }
        
        .signature-title {
            font-size: 9pt;
            color: #666666;
            margin-bottom: 50px;
        }
        
        .signature-line {
            border-bottom: 1px solid #333333;
            width: 60%;
            margin: 0 auto 5px auto;
        }
        
        .signature-name {
            font-size: 10pt;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .signature-position {
            font-size: 9pt;
            color: #666666;
        }
        
        /* Footer */
        .report-footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #999999;
            border-top: 0.5pt solid #CCCCCC;
            padding-top: 8px;
        }
        
        /* Page Numbers */
        .page-number:before {
            content: counter(page);
        }
        
        .page-total:before {
            content: counter(pages);
        }
        
        /* Comparative Tables */
        .comparative-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 9pt;
        }
        
        .comparative-table thead tr.header-row-1 th {
            background-color: #2C5F2D;
            color: white;
            padding: 8px;
            border: 1px solid #2C5F2D;
            text-align: center;
        }
        
        .comparative-table thead tr.header-row-2 th {
            background-color: #4CAF50;
            color: white;
            padding: 6px;
            border: 1px solid #4CAF50;
            font-size: 8.5pt;
        }
        
        .comparative-table tbody td {
            padding: 5px 6px;
            border: 0.5px solid #CCCCCC;
        }
        
        .comparative-table tbody td.variance-positive {
            color: #2C5F2D;
            font-weight: bold;
        }
        
        .comparative-table tbody td.variance-negative {
            color: #D32F2F;
            font-weight: bold;
        }
        
        /* Summary Box */
        .summary-box {
            border: 1px solid #999999;
            padding: 15px;
            margin: 25px 0;
            background-color: #F9F9F9;
        }
        
        .summary-box h3 {
            font-size: 11pt;
            margin: 0 0 10px 0;
            color: #2C5F2D;
            border-bottom: 1px solid #CCCCCC;
            padding-bottom: 5px;
        }
        
        .summary-box p {
            margin: 5px 0;
            font-size: 9.5pt;
        }
    </style>
</head>
<body>
    @include('reports.pdf.partials.header')
    
    <div class="report-title">
        @yield('report-title')
    </div>
    
    <div class="report-content">
        @yield('content')
    </div>
    
    @yield('summary')
    
    @include('reports.pdf.partials.signature')
    
    <div class="report-footer">
        Halaman <span class="page-number"></span> dari <span class="page-total"></span> 
        &nbsp;|&nbsp; 
        Dicetak: {{ $timestamp ?? now()->format('d M Y H:i') }} WIB
        <br>
        <small>Laporan ini dibuat oleh Simple Akunting V4</small>
    </div>
</body>
</html>
