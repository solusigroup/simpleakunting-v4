<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'name',
        'code',
        'phone',
        'email',
        'address',
        'npwp',
        'is_active',
    ];

    protected $casts = [
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
     * Get invoices for this contact.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Scope for customers.
     */
    public function scopeCustomers($query)
    {
        return $query->whereIn('type', ['Customer', 'Both']);
    }

    /**
     * Scope for suppliers.
     */
    public function scopeSuppliers($query)
    {
        return $query->whereIn('type', ['Supplier', 'Both']);
    }

    /**
     * Scope for active contacts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
