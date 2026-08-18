<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class BannerService
{
    private const CACHE_KEY = 'banners:active';

    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Return all active banners ordered by sort_order. Cached.
     *
     * @return Collection<int, Banner>
     */
    public function getActive(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => Banner::active()->ordered()->get());
    }

    /**
     * Clear the banners cache (called after any Filament save/delete).
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
