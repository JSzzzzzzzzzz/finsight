<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'total_value',
    ];

    protected $casts = [
        'date' => 'date',
        'total_value' => 'float',
    ];
}
