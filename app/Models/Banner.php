<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'cta_text',
        'cta_url',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** @param  Builder<Banner>  $query */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /** @param  Builder<Banner>  $query */
    public function scopeOrdered($query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
