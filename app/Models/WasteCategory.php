<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteCategory extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'buy_price',
        'sell_price',
        'is_active',
    ];

    public function deposits(): HasMany
    {
        return $this->hasMany(WasteDeposit::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(WasteSale::class);
    }
}
