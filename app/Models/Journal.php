<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'business_unit_id',
        'date',
        'reference',
        'description',
        'source',
        'is_posted',
    ];

    protected $casts = [
        'date' => 'date',
        'is_posted' => 'boolean',
    ];

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the business unit.
     */
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    /**
     * Get journal items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(JournalItem::class);
    }

    /**
     * Check if journal is balanced.
     */
    public function isBalanced(): bool
    {
        $totals = $this->items()->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();
        return abs($totals->total_debit - $totals->total_credit) < 0.01;
    }

    /**
     * Get total debit.
     */
    public function getTotalDebit(): float
    {
        return $this->items()->sum('debit');
    }

    /**
     * Get total credit.
     */
    public function getTotalCredit(): float
    {
        return $this->items()->sum('credit');
    }

    /**
     * Post the journal.
     */
    public function post(): bool
    {
        if (!$this->isBalanced()) {
            return false;
        }
        
        $this->is_posted = true;
        return $this->save();
    }

    /**
     * Scope for posted journals.
     */
    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }
}
