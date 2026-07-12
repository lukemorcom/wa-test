<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investor extends Model
{
    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }
}
