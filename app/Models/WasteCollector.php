<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteCollector extends Model
{
    protected $fillable = [
        'collector_number',
        'name',
        'phone',
        'address',
        'balance',
    ];

    public function deposits(): HasMany
    {
        return $this->hasMany(WasteDeposit::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(WasteWithdrawal::class);
    }
}
