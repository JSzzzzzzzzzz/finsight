<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTradingPair extends Model
{
    protected $fillable = [
        'user_id',
        'trading_pair_id',
    ];

    public function tradingPair()
    {
        return $this->belongsTo(TradingPair::class);
    }
}
