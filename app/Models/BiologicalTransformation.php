<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiologicalTransformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'biological_asset_id',
        'transformation_type',
        'transaction_date',
        'quantity_change',
        'description',
        'journal_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity_change' => 'decimal:2',
    ];

    /**
     * Get the biological asset that owns this transformation.
     */
    public function biologicalAsset(): BelongsTo
    {
        return $this->belongsTo(BiologicalAsset::class);
    }

    /**
     * Get the journal entry associated with this transformation.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Check if this is a growth transformation.
     */
    public function isGrowth(): bool
    {
        return $this->transformation_type === 'growth';
    }

    /**
     * Check if this is a harvest transformation.
     */
    public function isHarvest(): bool
    {
        return $this->transformation_type === 'harvest';
    }

    /**
     * Check if this is a death transformation.
     */
    public function isDeath(): bool
    {
        return $this->transformation_type === 'death';
    }

    /**
     * Check if this is a procreation transformation.
     */
    public function isProcreation(): bool
    {
        return $this->transformation_type === 'procreation';
    }

    /**
     * Scope for filtering by transformation type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('transformation_type', $type);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Get transformation type label in Indonesian.
     */
    public function getTypeLabel(): string
    {
        return match($this->transformation_type) {
            'growth' => 'Pertumbuhan',
            'degeneration' => 'Degenerasi',
            'production' => 'Produksi',
            'procreation' => 'Prokreasi',
            'death' => 'Kematian',
            'harvest' => 'Panen',
            default => '-',
        };
    }
}
