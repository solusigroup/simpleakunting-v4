<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Budget extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_id',
        'coa_id',
        'business_unit_id',
        'period_type',
        'period_year',
        'period_month',
        'period_quarter',
        'amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_year' => 'integer',
        'period_month' => 'integer',
        'period_quarter' => 'integer',
    ];

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the chart of account.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }

    /**
     * Get the business unit.
     */
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    /**
     * Get the creator.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get actual amount for this budget period.
     */
    public function getActual(): float
    {
        $startDate = $this->getPeriodStartDate();
        $endDate = $this->getPeriodEndDate();
        
        return $this->account->getBalance($startDate, $endDate);
    }

    /**
     * Get variance (Budget - Actual).
     */
    public function getVariance(): float
    {
        return $this->amount - abs($this->getActual());
    }

    /**
     * Get variance percentage.
     */
    public function getVariancePercent(): float
    {
        if ($this->amount == 0) {
            return 0;
        }
        
        return ($this->getVariance() / $this->amount) * 100;
    }

    /**
     * Check if over budget.
     */
    public function isOverBudget(): bool
    {
        return $this->getVariance() < 0;
    }

    /**
     * Get period start date.
     */
    public function getPeriodStartDate(): string
    {
        $year = $this->period_year;
        
        switch ($this->period_type) {
            case 'MONTHLY':
                return Carbon::create($year, $this->period_month, 1)->startOfMonth()->format('Y-m-d');
            case 'QUARTERLY':
                $month = (($this->period_quarter - 1) * 3) + 1;
                return Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
            case 'YEARLY':
            default:
                return Carbon::create($year, 1, 1)->startOfYear()->format('Y-m-d');
        }
    }

    /**
     * Get period end date.
     */
    public function getPeriodEndDate(): string
    {
        $year = $this->period_year;
        
        switch ($this->period_type) {
            case 'MONTHLY':
                return Carbon::create($year, $this->period_month, 1)->endOfMonth()->format('Y-m-d');
            case 'QUARTERLY':
                $month = $this->period_quarter * 3;
                return Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
            case 'YEARLY':
            default:
                return Carbon::create($year, 12, 31)->endOfYear()->format('Y-m-d');
        }
    }

    /**
     * Get human-readable period label.
     */
    public function getPeriodLabelAttribute(): string
    {
        $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        switch ($this->period_type) {
            case 'MONTHLY':
                return $monthNames[$this->period_month] . ' ' . $this->period_year;
            case 'QUARTERLY':
                return 'Q' . $this->period_quarter . ' ' . $this->period_year;
            case 'YEARLY':
            default:
                return 'Tahun ' . $this->period_year;
        }
    }

    /**
     * Scope for specific year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('period_year', $year);
    }

    /**
     * Scope for specific period type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('period_type', $type);
    }

    /**
     * Scope for specific account.
     */
    public function scopeForAccount($query, int $coaId)
    {
        return $query->where('coa_id', $coaId);
    }
}
