<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investor extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'share_percentage',
        'basis',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'share_percentage' => 'decimal:2',
    ];

    /**
     * Get the company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get sharing records for this investor.
     */
    public function sharings(): HasMany
    {
        return $this->hasMany(InvestorSharing::class);
    }
}
