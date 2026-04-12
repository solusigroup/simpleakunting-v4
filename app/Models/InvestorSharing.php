<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorSharing extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_id',
        'journal_id',
        'amount',
        'period_start',
        'period_end',
        'basis_amount',
        'note',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'amount' => 'decimal:2',
        'basis_amount' => 'decimal:2',
    ];

    /**
     * Get the investor.
     */
    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    /**
     * Get the related journal entry.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
