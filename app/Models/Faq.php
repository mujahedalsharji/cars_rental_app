<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'question',
        'answer',
        'category',
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

    /** @param  Builder<Faq>  $query */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /** @param  Builder<Faq>  $query */
    public function scopeOrdered($query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
