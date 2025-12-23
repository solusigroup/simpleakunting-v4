<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_id',
        'coa_id',
        'code',
        'name',
        'category',
        'is_assembly',
        'description',
        'unit',
        'cost',
        'price',
        'stock',
        'min_stock',
        'is_active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_assembly' => 'boolean',
    ];

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the inventory account.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    /**
     * Calculate inventory value.
     */
    public function getValue(): float
    {
        return $this->stock * $this->cost;
    }

    /**
     * Check if stock is low.
     */
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    /**
     * Scope for active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    /**
     * Get assembly components (if this is an assembly item).
     */
    public function components()
    {
        return $this->hasMany(AssemblyComponent::class, 'assembly_id');
    }

    /**
     * Get assemblies that use this item as a component.
     */
    public function usedInAssemblies()
    {
        return $this->hasMany(AssemblyComponent::class, 'component_id');
    }

    /**
     * Get productions of this item.
     */
    public function productions()
    {
        return $this->hasMany(Production::class, 'assembly_id');
    }

    /**
     * Scope for finished goods.
     */
    public function scopeFinishedGoods($query)
    {
        return $query->where('category', 'finished_goods');
    }

    /**
     * Scope for raw materials.
     */
    public function scopeRawMaterials($query)
    {
        return $query->where('category', 'raw_materials');
    }

    /**
     * Scope for work in process.
     */
    public function scopeWorkInProcess($query)
    {
        return $query->where('category', 'work_in_process');
    }

    /**
     * Scope for supplies.
     */
    public function scopeSupplies($query)
    {
        return $query->where('category', 'supplies');
    }

    /**
     * Scope for assembly items.
     */
    public function scopeAssembly($query)
    {
        return $query->where('is_assembly', true);
    }

    /**
     * Get category label in Indonesian.
     */
    public function getCategoryLabel(): string
    {
        return match($this->category) {
            'finished_goods' => 'Barang Jadi/Dagangan',
            'raw_materials' => 'Bahan Baku',
            'work_in_process' => 'Barang Dalam Proses',
            'supplies' => 'Bahan Pembantu',
            default => '-',
        };
    }

    /**
     * Calculate total component cost for assembly.
     */
    public function getComponentsCost(): float
    {
        if (!$this->is_assembly) {
            return 0;
        }

        return $this->components->sum(function($component) {
            return $component->quantity * $component->component->cost;
        });
    }

    /**
     * Check if can be assembled (has components defined).
     */
    public function canBeAssembled(): bool
    {
        return $this->is_assembly && $this->components()->count() > 0;
    }

    /**
     * Check if has sufficient components in stock.
     */
    public function hasSufficientComponents(int $quantity = 1): bool
    {
        if (!$this->is_assembly) {
            return false;
        }

        foreach ($this->components as $component) {
            $requiredQty = $component->quantity * $quantity;
            if ($component->component->stock < $requiredQty) {
                return false;
            }
        }

        return true;
    }
}
