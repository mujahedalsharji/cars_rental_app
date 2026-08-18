<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class FaqService
{
    private const CACHE_KEY = 'faqs:active';

    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Return all active FAQs ordered by sort_order. Cached.
     *
     * @return Collection<int, Faq>
     */
    public function getActive(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => Faq::active()->ordered()->get());
    }

    /**
     * Clear the FAQs cache (called after any Filament save/delete).
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
