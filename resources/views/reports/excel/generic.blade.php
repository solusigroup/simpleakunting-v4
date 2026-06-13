@extends('reports.excel.layout')

@section('content')
<table>
    <tr>
        <th colspan="{{ count($headers) }}" style="font-weight: bold; font-size: 14pt; text-align: center;">{{ strtoupper($company->name ?? 'Perusahaan') }}</th>
    </tr>
    <tr>
        <th colspan="{{ count($headers) }}" style="font-weight: bold; font-size: 16pt; text-align: center;">{{ strtoupper($title) }}</th>
    </tr>
    @if(isset($subtitle) && $subtitle)
    <tr>
        <th colspan="{{ count($headers) }}" style="font-size: 11pt; text-align: center;">{{ $subtitle }}</th>
    </tr>
    @endif
    <tr></tr>
    <thead>
        <tr>
            @foreach($headers as $header)
                <th style="font-weight: bold; background-color: #2C5F2D; color: #ffffff;{{ isset($header['align']) ? ' text-align: ' . $header['align'] . ';' : '' }}">{{ $header['label'] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                @foreach($headers as $header)
                    @php
                        $key = $header['key'];
                        $val = data_get($row, $key);
                        $align = $header['align'] ?? 'left';
                        $style = '';
                        if (isset($row['_style'])) {
                            $style .= $row['_style'];
                        }
                    @endphp
                    <td style="text-align: {{ $align }};{{ $style }}">{{ $val }}</td>
                @endforeach
            </tr>
        @endforeach
        @if(isset($totals) && $totals)
            <tr style="font-weight: bold; background-color: #cbd5e1;">
                @foreach($headers as $header)
                    @php
                        $key = $header['key'];
                        $val = data_get($totals, $key, '');
                        $align = $header['align'] ?? 'left';
                    @endphp
                    <td style="text-align: {{ $align }};">{{ $val }}</td>
                @endforeach
            </tr>
        @endif
    </tbody>
</table>
@endsection
