<div class="signature-section">
    <div class="signature-location">
        <strong>Dibuat di:</strong> {{ $city ?? \App\Helpers\ReportHelper::extractCity($company->address ?? '') }}<br>
        <strong>Tanggal:</strong> {{ \App\Helpers\ReportHelper::formatDate($date ?? now(), 'd F Y') }}
    </div>
    
    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-role">Mengetahui,</div>
                <div class="signature-title">{{ $company->director_title ?? 'Direktur Utama' }}</div>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $company->director_name ?? '(____________________)' }}</div>
            </td>
            <td>
                <div class="signature-role">Menyetujui,</div>
                <div class="signature-title">{{ $company->secretary_title ?? 'Kepala Keuangan' }}</div>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $company->secretary_name ?? '(____________________)' }}</div>
            </td>
        </tr>
    </table>
</div>
