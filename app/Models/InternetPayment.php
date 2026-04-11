<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternetPayment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_id',
        'internet_billing_id',
        'journal_id',
        'payment_number',
        'amount',
        'payment_date',
        'payment_method',
        'cash_bank_account_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(InternetBilling::class, 'internet_billing_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function cashBankAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'cash_bank_account_id');
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Generate payment number.
     */
    public static function generatePaymentNumber(int $companyId): string
    {
        $count = static::where('company_id', $companyId)
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->count();

        return sprintf('PAY-%s-%04d', now()->format('Ymd'), $count + 1);
    }
}
