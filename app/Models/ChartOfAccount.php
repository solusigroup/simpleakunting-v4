<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'report_type',
        'normal_balance',
        'is_parent',
        'parent_id',
        'is_active',
        'is_system',
        'level',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'is_parent' => 'boolean',
    ];

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the parent account.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Get child accounts.
     */
    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    /**
     * Get all journal items for this account.
     */
    public function journalItems(): HasMany
    {
        return $this->hasMany(JournalItem::class, 'coa_id');
    }

    /**
     * Calculate balance for this account.
     */
    public function getBalance(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->journalItems()
            ->whereHas('journal', function ($q) use ($startDate, $endDate) {
                $q->where('is_posted', true);
                if ($startDate) {
                    $q->where('date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('date', '<=', $endDate);
                }
            });

        $debit = (clone $query)->sum('debit');
        $credit = (clone $query)->sum('credit');

        // Use the normal_balance field to determine calculation
        if ($this->normal_balance === 'DEBIT') {
            return $debit - $credit;
        }
        
        return $credit - $debit;
    }

    /**
     * Scope for filtering by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for detail accounts (can have transactions).
     */
    public function scopeDetail($query)
    {
        return $query->where('is_parent', false);
    }

    /**
     * Scope for parent/header accounts.
     */
    public function scopeHeaders($query)
    {
        return $query->where('is_parent', true);
    }

    /**
     * Scope for balance sheet accounts.
     */
    public function scopeNeraca($query)
    {
        return $query->where('report_type', 'NERACA');
    }

    /**
     * Scope for income statement accounts.
     */
    public function scopeLabaRugi($query)
    {
        return $query->where('report_type', 'LABARUGI');
    }

    /**
     * Check if this account can have transactions.
     */
    public function canHaveTransactions(): bool
    {
        return !$this->is_parent;
    }
}
