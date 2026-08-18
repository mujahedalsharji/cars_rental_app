<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    private const CACHE_KEY = 'categories:active';

    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Return all active categories ordered by sort_order. Cached.
     *
     * @return Collection<int, Category>
     */
    public function getActive(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => Category::active()->ordered()->get());
    }

    /**
     * Return a single active category by slug, or null if not found / inactive.
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->getActive()->firstWhere('slug', $slug);
    }

    /**
     * Clear the categories cache (called after any Filament save/delete).
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
