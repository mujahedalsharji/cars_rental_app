<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarFeature extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'car_id',
        'feature',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
