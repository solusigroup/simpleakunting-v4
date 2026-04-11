<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class InternetCustomer extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'company_id',
        'customer_id',
        'name',
        'address',
        'phone',
        'email',
        'package_name',
        'monthly_rate',
        'billing_date',
        'status',
        'activated_at',
        'notes',
    ];

    protected $casts = [
        'monthly_rate' => 'decimal:2',
        'billing_date' => 'integer',
        'activated_at' => 'date',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(InternetBilling::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(InternetPayment::class, InternetBilling::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Total outstanding balance (unpaid billings).
     */
    public function getOutstandingBalanceAttribute(): float
    {
        return $this->billings()
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as total')
            ->value('total') ?? 0;
    }

    /**
     * Total paid amount across all billings.
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->billings()->sum('paid_amount');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithOutstandingBalance($query)
    {
        return $query->withCount(['billings as outstanding_total' => function ($q) {
            $q->whereIn('status', ['unpaid', 'partial', 'overdue'])
              ->selectRaw('COALESCE(SUM(amount - paid_amount), 0)');
        }]);
    }

    // ==========================================
    // HELPERS
    // ==========================================

    /**
     * Generate next customer ID for a company.
     */
    public static function generateCustomerId(int $companyId): string
    {
        $last = static::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->value('customer_id');

        if ($last) {
            preg_match('/(\d+)$/', $last, $matches);
            $next = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        } else {
            $next = 1;
        }

        return 'PLG-' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
