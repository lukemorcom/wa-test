<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investor extends Model
{
    protected $fillable = [
        'investor_id',
        'name',
        'age',
    ];

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }
}
