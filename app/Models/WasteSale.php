<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteSale extends Model
{
    protected $fillable = [
        'sale_number',
        'waste_category_id',
        'weight',
        'price_at_time',
        'total_amount',
        'date',
        'buyer_name',
        'journal_id',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class, 'waste_category_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
