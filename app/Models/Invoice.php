<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'contact_id',
        'journal_id',
        'business_unit_id',
        'type',
        'invoice_number',
        'date',
        'due_date',
        'subtotal',
        'tax',
        'discount',
        'total',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the contact.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the journal.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Get the business unit.
     */
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    /**
     * Get invoice items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Check if this is a sales invoice.
     */
    public function isSales(): bool
    {
        return $this->type === 'Sales';
    }

    /**
     * Check if this is a purchase invoice.
     */
    public function isPurchase(): bool
    {
        return $this->type === 'Purchase';
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'Paid' && $this->due_date->isPast();
    }

    /**
     * Scope for sales invoices.
     */
    public function scopeSales($query)
    {
        return $query->where('type', 'Sales');
    }

    /**
     * Scope for purchase invoices.
     */
    public function scopePurchases($query)
    {
        return $query->where('type', 'Purchase');
    }

    /**
     * Scope for posted invoices.
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'Posted');
    }
}
