<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiologicalValuation extends Model
{
    use HasFactory;

    protected $fillable = [
        'biological_asset_id',
        'valuation_date',
        'previous_fair_value',
        'current_fair_value',
        'cost_to_sell',
        'fair_value_change',
        'valuation_method',
        'valuation_notes',
        'journal_id',
        'created_by',
    ];

    protected $casts = [
        'valuation_date' => 'date',
        'previous_fair_value' => 'decimal:2',
        'current_fair_value' => 'decimal:2',
        'cost_to_sell' => 'decimal:2',
        'fair_value_change' => 'decimal:2',
    ];

    /**
     * Get the biological asset that owns this valuation.
     */
    public function biologicalAsset(): BelongsTo
    {
        return $this->belongsTo(BiologicalAsset::class);
    }

    /**
     * Get the journal entry for fair value adjustment.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Get the user who created this valuation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if fair value increased.
     */
    public function isGain(): bool
    {
        return $this->fair_value_change > 0;
    }

    /**
     * Check if fair value decreased.
     */
    public function isLoss(): bool
    {
        return $this->fair_value_change < 0;
    }

    /**
     * Get carrying amount after valuation.
     */
    public function getCarryingAmount(): float
    {
        return $this->current_fair_value - $this->cost_to_sell;
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('valuation_date', [$startDate, $endDate]);
    }

    /**
     * Scope for gains only.
     */
    public function scopeGains($query)
    {
        return $query->where('fair_value_change', '>', 0);
    }

    /**
     * Scope for losses only.
     */
    public function scopeLosses($query)
    {
        return $query->where('fair_value_change', '<', 0);
    }
}
