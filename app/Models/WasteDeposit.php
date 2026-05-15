<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteDeposit extends Model
{
    protected $fillable = [
        'deposit_number',
        'waste_collector_id',
        'waste_category_id',
        'weight',
        'price_at_time',
        'total_amount',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class, 'waste_category_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
