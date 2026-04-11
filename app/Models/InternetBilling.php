<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternetBilling extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_id',
        'internet_customer_id',
        'journal_id',
        'billing_number',
        'period_month',
        'period_year',
        'amount',
        'paid_amount',
        'status',
        'due_date',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(InternetCustomer::class, 'internet_customer_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InternetPayment::class, 'internet_billing_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getRemainingAmountAttribute(): float
    {
        return (float)$this->amount - (float)$this->paid_amount;
    }

    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return ($months[$this->period_month] ?? '') . ' ' . $this->period_year;
    }

    // ==========================================
    // METHODS
    // ==========================================

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return !$this->isPaid() && $this->due_date->isPast();
    }

    /**
     * Update status based on paid_amount.
     */
    public function refreshStatus(): void
    {
        if ($this->paid_amount >= $this->amount) {
            $this->status = 'paid';
            $this->paid_at = $this->paid_at ?? now();
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date->isPast()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'unpaid';
        }
        $this->save();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial', 'overdue']);
    }

    public function scopeForPeriod($query, int $month, int $year)
    {
        return $query->where('period_month', $month)->where('period_year', $year);
    }

    /**
     * Generate billing number.
     */
    public static function generateBillingNumber(int $companyId, int $month, int $year): string
    {
        $count = static::where('company_id', $companyId)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->count();

        return sprintf('BIL-%04d%02d-%04d', $year, $month, $count + 1);
    }
}
