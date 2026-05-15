<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteWithdrawal extends Model
{
    protected $fillable = [
        'withdrawal_number',
        'waste_collector_id',
        'amount',
        'date',
        'journal_id',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function collector(): BelongsTo
    {
        return $this->belongsTo(WasteCollector::class, 'waste_collector_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
