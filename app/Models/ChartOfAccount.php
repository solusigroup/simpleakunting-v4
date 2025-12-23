<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory, Auditable;

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
        'account_category',
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
                    $q->whereDate('date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('date', '<=', $endDate);
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
     * Scope for filtering by account category.
     */
    public function scopeCategory($query, string|array $category)
    {
        if (is_array($category)) {
            return $query->whereIn('account_category', $category);
        }
        return $query->where('account_category', $category);
    }

    /**
     * Scope for cash and bank accounts with fallback to code pattern.
     */
    public function scopeCashBank($query)
    {
        return $query->where(function($q) {
            $q->where('account_category', 'cash_bank')
              ->orWhere(function($q2) {
                  // Fallback for accounts without category
                  $q2->whereNull('account_category')
                     ->where(function($q3) {
                         $q3->where('code', 'like', '1.1.1%')
                            ->orWhere('code', 'like', '1100%')
                            ->orWhere('name', 'like', '%Kas%')
                            ->orWhere('name', 'like', '%Bank%');
                     });
              });
        });
    }

    /**
     * Scope for inventory accounts with fallback to code pattern.
     */
    public function scopeInventoryAccounts($query)
    {
        return $query->where(function($q) {
            $q->where('account_category', 'inventory')
              ->orWhere(function($q2) {
                  // Fallback for accounts without category
                  $q2->whereNull('account_category')
                     ->where(function($q3) {
                         $q3->where('name', 'like', '%Persediaan%')
                            ->orWhere('name', 'like', '%Inventory%')
                            ->orWhere('code', 'like', '1.1.4%')
                            ->orWhere('code', 'like', '114%');
                     });
              });
        });
    }

    /**
     * Scope for fixed asset accounts with fallback to code pattern.
     */
    public function scopeFixedAssetAccounts($query)
    {
        return $query->where(function($q) {
            $q->where('account_category', 'fixed_asset')
              ->orWhere(function($q2) {
                  // Fallback for accounts without category
                  $q2->whereNull('account_category')
                     ->where(function($q3) {
                         $q3->where('code', 'like', '1.2%')
                            ->orWhere('code', 'like', '12%');
                     });
              });
        });
    }

    /**
     * Scope for accumulated depreciation accounts.
     */
    public function scopeAccumulatedDepreciation($query)
    {
        return $query->where(function($q) {
            $q->where('account_category', 'accumulated_depreciation')
              ->orWhere(function($q2) {
                  // Fallback for accounts without category
                  $q2->whereNull('account_category')
                     ->where(function($q3) {
                         $q3->where('name', 'like', '%Akumulasi%')
                            ->orWhere('name', 'like', '%Accumulated%');
                     });
              });
        });
    }

    /**
     * Check if account is current asset (for financial analysis).
     */
    public function isCurrentAsset(): bool
    {
        if ($this->account_category) {
            return in_array($this->account_category, [
                'cash_bank',
                'accounts_receivable',
                'other_receivable',
                'inventory',
                'prepaid_expense',
                'other_current_asset',
            ]);
        }
        
        // Fallback to code pattern
        return str_starts_with($this->code, '1.1') || str_starts_with($this->code, '11');
    }

    /**
     * Check if account is current liability (for financial analysis).
     */
    public function isCurrentLiability(): bool
    {
        if ($this->account_category) {
            return in_array($this->account_category, [
                'accounts_payable',
                'other_payable',
                'accrued_expense',
                'other_current_liability',
            ]);
        }
        
        // Fallback to code pattern
        return str_starts_with($this->code, '2.1') || str_starts_with($this->code, '21');
    }

    /**
     * Check if this account can have transactions.
     */
    public function canHaveTransactions(): bool
    {
        return !$this->is_parent;
    }

    /**
     * Scope for biological asset accounts.
     */
    public function scopeBiologicalAssets($query)
    {
        return $query->where(function($q) {
            $q->whereIn('account_category', [
                'biological_asset',
                'biological_asset_immature',
                'biological_asset_mature'
            ])
            ->orWhere(function($q2) {
                // Fallback for accounts without category
                $q2->whereNull('account_category')
                   ->where(function($q3) {
                       $q3->where('name', 'like', '%Aset Biologis%')
                          ->orWhere('name', 'like', '%Biological Asset%');
                   });
            });
        });
    }

    /**
     * Scope for agricultural produce accounts.
     */
    public function scopeAgriculturalProduce($query)
    {
        return $query->where(function($q) {
            $q->where('account_category', 'agricultural_produce')
              ->orWhere(function($q2) {
                  $q2->whereNull('account_category')
                     ->where(function($q3) {
                         $q3->where('name', 'like', '%Produk Agrikultur%')
                            ->orWhere('name', 'like', '%Agricultural Produce%');
                     });
              });
        });
    }

    /**
     * Scope for fair value gain/loss accounts.
     */
    public function scopeFairValueGainLoss($query)
    {
        return $query->where(function($q) {
            $q->where('account_category', 'fair_value_gain_loss')
              ->orWhere(function($q2) {
                  $q2->whereNull('account_category')
                     ->where(function($q3) {
                         $q3->where('name', 'like', '%Keuntungan%Nilai Wajar%')
                            ->orWhere('name', 'like', '%Kerugian%Nilai Wajar%')
                            ->orWhere('name', 'like', '%Fair Value%');
                     });
              });
        });
    }

    /**
     * Check if account is for biological assets.
     */
    public function isBiologicalAsset(): bool
    {
        if ($this->account_category) {
            return in_array($this->account_category, [
                'biological_asset',
                'biological_asset_immature',
                'biological_asset_mature'
            ]);
        }
        
        return false;
    }
}

