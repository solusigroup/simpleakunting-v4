<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiologicalAsset extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'category',
        'asset_type',
        'maturity_status',
        'quantity',
        'unit',
        'acquisition_date',
        'acquisition_cost',
        'current_fair_value',
        'cost_to_sell',
        'carrying_amount',
        'valuation_method',
        'valuation_date',
        'location',
        'notes',
        'coa_id',
        'fair_value_gain_loss_coa_id',
        'is_active',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'valuation_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'current_fair_value' => 'decimal:2',
        'cost_to_sell' => 'decimal:2',
        'carrying_amount' => 'decimal:2',
        'quantity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns the biological asset.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the chart of account for this biological asset.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    /**
     * Get the fair value gain/loss account.
     */
    public function fairValueAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'fair_value_gain_loss_coa_id');
    }

    /**
     * Get all transformations for this biological asset.
     */
    public function transformations(): HasMany
    {
        return $this->hasMany(BiologicalTransformation::class);
    }

    /**
     * Get all valuations for this biological asset.
     */
    public function valuations(): HasMany
    {
        return $this->hasMany(BiologicalValuation::class);
    }

    /**
     * Get all agricultural produce from this biological asset.
     */
    public function produce(): HasMany
    {
        return $this->hasMany(AgriculturalProduce::class);
    }

    /**
     * Calculate carrying amount (Fair Value - Cost to Sell).
     */
    public function calculateCarryingAmount(): float
    {
        if ($this->valuation_method === 'fair_value' && $this->current_fair_value) {
            return $this->current_fair_value - $this->cost_to_sell;
        }
        
        // For cost model, carrying amount = acquisition cost
        return $this->acquisition_cost;
    }

    /**
     * Update carrying amount based on current fair value.
     */
    public function updateCarryingAmount(): void
    {
        $this->carrying_amount = $this->calculateCarryingAmount();
        $this->save();
    }

    /**
     * Check if asset is mature.
     */
    public function isMature(): bool
    {
        return $this->maturity_status === 'mature';
    }

    /**
     * Check if asset is consumable (habis pakai).
     */
    public function isConsumable(): bool
    {
        return $this->asset_type === 'consumable';
    }

    /**
     * Check if asset is bearer (penghasil).
     */
    public function isBearer(): bool
    {
        return $this->asset_type === 'bearer';
    }

    /**
     * Get total quantity harvested.
     */
    public function getTotalHarvested(): float
    {
        return $this->transformations()
            ->where('transformation_type', 'harvest')
            ->sum('quantity_change');
    }

    /**
     * Get current book value per unit.
     */
    public function getUnitValue(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        return $this->carrying_amount / $this->quantity;
    }

    /**
     * Scope for active biological assets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for filtering by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for mature assets.
     */
    public function scopeMature($query)
    {
        return $query->where('maturity_status', 'mature');
    }

    /**
     * Scope for immature assets.
     */
    public function scopeImmature($query)
    {
        return $query->where('maturity_status', 'immature');
    }

    /**
     * Scope for consumable assets.
     */
    public function scopeConsumable($query)
    {
        return $query->where('asset_type', 'consumable');
    }

    /**
     * Scope for bearer assets.
     */
    public function scopeBearer($query)
    {
        return $query->where('asset_type', 'bearer');
    }

    /**
     * Scope for fair value method.
     */
    public function scopeFairValue($query)
    {
        return $query->where('valuation_method', 'fair_value');
    }

    /**
     * Scope for cost model.
     */
    public function scopeCostModel($query)
    {
        return $query->where('valuation_method', 'cost_model');
    }

    /**
     * Get category label in Indonesian.
     */
    public function getCategoryLabel(): string
    {
        return match($this->category) {
            'livestock' => 'Peternakan',
            'plantation' => 'Perkebunan',
            'aquaculture' => 'Perikanan/Budidaya',
            'forestry' => 'Kehutanan',
            default => 'Lainnya',
        };
    }

    /**
     * Get asset type label in Indonesian.
     */
    public function getAssetTypeLabel(): string
    {
        return match($this->asset_type) {
            'consumable' => 'Habis Pakai',
            'bearer' => 'Penghasil',
            default => '-',
        };
    }

    /**
     * Get maturity status label in Indonesian.
     */
    public function getMaturityStatusLabel(): string
    {
        return match($this->maturity_status) {
            'mature' => 'Dewasa/Produktif',
            'immature' => 'Belum Dewasa',
            default => '-',
        };
    }
}
