<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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

    protected function feature(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => Str::squish($value),
        );
    }
}
