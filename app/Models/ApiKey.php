<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'exchange',
        'api_key',
        'api_secret',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
    ];
}
