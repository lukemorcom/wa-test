<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investment extends Model
{
    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }
}
