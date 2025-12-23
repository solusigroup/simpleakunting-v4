<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgriculturalProduce extends Model
{
    use HasFactory;

    protected $table = 'agricultural_produce';

    protected $fillable = [
        'company_id',
        'biological_asset_id',
        'harvest_date',
        'product_name',
        'quantity',
        'unit',
        'fair_value_at_harvest',
        'cost_to_sell',
        'carrying_amount',
        'inventory_id',
        'coa_id',
        'journal_id',
        'notes',
    ];

    protected $casts = [
        'harvest_date' => 'date',
        'quantity' => 'decimal:2',
        'fair_value_at_harvest' => 'decimal:2',
        'cost_to_sell' => 'decimal:2',
        'carrying_amount' => 'decimal:2',
    ];

    /**
     * Get the company that owns this produce.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the biological asset that produced this.
     */
    public function biologicalAsset(): BelongsTo
    {
        return $this->belongsTo(BiologicalAsset::class);
    }

    /**
     * Get the inventory item if converted.
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the chart of account for this produce.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    /**
     * Get the journal entry for harvest.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Calculate carrying amount (Fair Value - Cost to Sell).
     */
    public function calculateCarryingAmount(): float
    {
        return $this->fair_value_at_harvest - $this->cost_to_sell;
    }

    /**
     * Check if produce has been converted to inventory.
     */
    public function isConvertedToInventory(): bool
    {
        return $this->inventory_id !== null;
    }

    /**
     * Get unit value.
     */
    public function getUnitValue(): float
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        return $this->carrying_amount / $this->quantity;
    }

    /**
     * Scope for filtering by harvest date range.
     */
    public function scopeHarvestDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('harvest_date', [$startDate, $endDate]);
    }

    /**
     * Scope for unconverted produce.
     */
    public function scopeUnconverted($query)
    {
        return $query->whereNull('inventory_id');
    }

    /**
     * Scope for converted produce.
     */
    public function scopeConverted($query)
    {
        return $query->whereNotNull('inventory_id');
    }
}
