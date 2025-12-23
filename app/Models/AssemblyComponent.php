<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssemblyComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'assembly_id',
        'component_id',
        'quantity',
        'unit',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
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
     * Get the component (raw material/part).
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'component_id');
    }

    /**
     * Calculate total cost for this component.
     */
    public function getTotalCost(): float
    {
        return $this->quantity * $this->component->cost;
    }

    /**
     * Check if component is in stock.
     */
    public function isInStock(int $assemblyQuantity = 1): bool
    {
        $requiredQty = $this->quantity * $assemblyQuantity;
        return $this->component->stock >= $requiredQty;
    }
}
