<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradingPair extends Model
{
    protected $fillable = [
        'symbol',
        'source',
        'is_active',
    ];

    public function userTradingPairs()
    {
        return $this->hasMany(UserTradingPair::class);
    }
}
