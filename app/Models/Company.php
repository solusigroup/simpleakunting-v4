<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'entity_type',
        'logo',
        'phone',
        'email',
        'npwp',
        'address',
        'fiscal_start',
        'director_name',
        'director_title',
        'secretary_name',
        'secretary_title',
        'staff_name',
        'staff_title',
        'enable_psak69',
        'business_sector',
    ];

    protected $casts = [
        'fiscal_start' => 'date',
        'enable_psak69' => 'boolean',
    ];

    /**
     * Get the owner of the company.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all users in this company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get business units for this company.
     */
    public function businessUnits(): HasMany
    {
        return $this->hasMany(BusinessUnit::class);
    }

    /**
     * Get chart of accounts for this company.
     */
    public function chartOfAccounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class);
    }

    /**
     * Get contacts for this company.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get journals for this company.
     */
    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    /**
     * Get inventories for this company.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get fixed assets for this company.
     */
    public function fixedAssets(): HasMany
    {
        return $this->hasMany(FixedAsset::class);
    }

    /**
     * Get invoices for this company.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Check if this is a BUMDesa entity.
     */
    public function isBumdesa(): bool
    {
        return $this->entity_type === 'BUMDesa';
    }

    /**
     * Check if this is a UMKM entity.
     */
    public function isUmkm(): bool
    {
        return $this->entity_type === 'UMKM';
    }

    /**
     * Check if company uses PSAK 69 (Biological Assets).
     */
    public function usesPsak69(): bool
    {
        return $this->enable_psak69 === true;
    }

    /**
     * Check if company is in agriculture sector.
     */
    public function isAgricultureSector(): bool
    {
        return in_array($this->business_sector, [
            'livestock',
            'plantation',
            'aquaculture',
            'forestry',
            'mixed_agriculture'
        ]);
    }

    /**
     * Get biological assets for this company (if PSAK 69 enabled).
     */
    public function biologicalAssets(): HasMany
    {
        return $this->hasMany(BiologicalAsset::class);
    }
}

