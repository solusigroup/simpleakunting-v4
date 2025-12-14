<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'coa_id',
        'accum_coa_id',
        'code',
        'name',
        'acquisition_date',
        'acquisition_cost',
        'salvage_value',
        'useful_life_months',
        'depreciation_method',
        'accumulated_depreciation',
        'is_active',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the asset account.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    /**
     * Get the accumulated depreciation account.
     */
    public function accumulatedAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'accum_coa_id');
    }

    /**
     * Calculate monthly depreciation (straight line).
     */
    public function getMonthlyDepreciation(): float
    {
        if ($this->depreciation_method === 'straight_line') {
            $depreciableAmount = $this->acquisition_cost - $this->salvage_value;
            return $depreciableAmount / $this->useful_life_months;
        }
        
        // Declining balance
        $rate = 2 / $this->useful_life_months;
        $bookValue = $this->acquisition_cost - $this->accumulated_depreciation;
        return max($bookValue * $rate, 0);
    }

    /**
     * Get book value.
     */
    public function getBookValue(): float
    {
        return $this->acquisition_cost - $this->accumulated_depreciation;
    }

    /**
     * Calculate remaining useful life in months.
     */
    public function getRemainingLife(): int
    {
        $monthsUsed = Carbon::parse($this->acquisition_date)->diffInMonths(now());
        return max($this->useful_life_months - $monthsUsed, 0);
    }

    /**
     * Scope for active assets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
