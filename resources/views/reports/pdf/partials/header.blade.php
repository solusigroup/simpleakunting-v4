<div class="header-section">
    <div class="header-logo">
        @if(!empty($company->logo) && file_exists(public_path('storage/' . $company->logo)))
            <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name }}">
        @else
            <div style="width: 60px; height: 60px; background-color: #2C5F2D; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24pt; font-weight: bold;">
                {{ substr($company->name, 0, 1) }}
            </div>
        @endif
    </div>
    <div class="header-info">
        <div class="company-name">{{ $company->name ?? 'Nama Perusahaan' }}</div>
        <div class="company-details">
            @if($company->address)
                {{ $company->address }}<br>
            @endif
            @if($company->phone || $company->email)
                @if($company->phone)Telp: {{ $company->phone }}@endif
                @if($company->phone && $company->email) | @endif
                @if($company->email)Email: {{ $company->email }}@endif
                <br>
            @endif
            @if($company->npwp)
                NPWP: {{ $company->npwp }}
            @endif
        </div>
    </div>
</div>
