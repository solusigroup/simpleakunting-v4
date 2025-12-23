<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Production extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_id',
        'production_number',
        'production_date',
        'assembly_id',
        'quantity',
        'unit',
        'total_material_cost',
        'labor_cost',
        'labor_coa_id',
        'overhead_cost',
        'overhead_coa_id',
        'total_cost',
        'unit_cost',
        'status',
        'journal_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'production_date' => 'date',
        'quantity' => 'decimal:2',
        'total_material_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'overhead_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the assembly (finished product).
     */
    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'assembly_id');
    }

    /**
     * Get the journal entry.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Get the user who created this production.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get production components used.
     */
    public function components(): HasMany
    {
        return $this->hasMany(ProductionComponent::class);
    }

    /**
     * Calculate total cost.
     */
    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->total_material_cost + $this->labor_cost + $this->overhead_cost;
        $this->unit_cost = $this->quantity > 0 ? $this->total_cost / $this->quantity : 0;
    }

    /**
     * Check if production is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if production is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if production is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Scope for completed productions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for in progress productions.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for draft productions.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('production_date', [$startDate, $endDate]);
    }

    /**
     * Get status label in Indonesian.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'in_progress' => 'Dalam Proses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => '-',
        };
    }
}
