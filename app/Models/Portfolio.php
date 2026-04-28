<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Asset;

class Portfolio extends Model
{
    protected $fillable = [
        'user_id',
        'asset_id',
        'amount',
        'avg_buy_price'
    ];

    //Relationship
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
