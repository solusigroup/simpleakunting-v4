<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id',
        'component_id',
        'quantity_required',
        'quantity_used',
        'unit',
        'unit_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'quantity_required' => 'decimal:2',
        'quantity_used' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the production.
     */
    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    /**
     * Get the component (raw material).
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'component_id');
    }

    /**
     * Calculate total cost.
     */
    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->quantity_used * $this->unit_cost;
    }

    /**
     * Get variance (difference between required and used).
     */
    public function getVariance(): float
    {
        return $this->quantity_used - $this->quantity_required;
    }

    /**
     * Check if there's material waste.
     */
    public function hasWaste(): bool
    {
        return $this->quantity_used > $this->quantity_required;
    }

    /**
     * Check if there's material shortage.
     */
    public function hasShortage(): bool
    {
        return $this->quantity_used < $this->quantity_required;
    }
}
