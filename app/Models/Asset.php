<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Portfolio;

class Asset extends Model
{
    protected $fillable = ['symbol', 'name'];

    // Relationship
    public function portfolios()
    {
        return $this->hasMany(Portfolio::class);
    }
}
