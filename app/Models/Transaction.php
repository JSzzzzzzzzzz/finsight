<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'asset_id',
        'type',
        'amount',
        'price',
        'total',
        'transaction_time'
    ];

    // RELATIONSHIPS
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}