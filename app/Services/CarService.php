<?php

namespace App\Services;

use App\Models\Car;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CarService
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Return paginated published cars with optional filters.
     * Filters: category (slug), search (name), featured (bool), per_page, page.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getAllPublished(array $filters = []): LengthAwarePaginator
    {
        $category = $filters['category'] ?? null;
        $search = $filters['search'] ?? null;
        $featured = $filters['featured'] ?? null;
        $perPage = (int) ($filters['per_page'] ?? 12);

        $cacheKey = "cars:list:{$category}:{$search}:{$featured}:{$perPage}:".request()->get('page', 1);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($category, $search, $featured, $perPage): LengthAwarePaginator {
            $query = Car::published()->ordered()->with(['category', 'features']);

            if ($category) {
                $query->inCategory($category);
            }

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            if ($featured !== null) {
                $query->where('is_featured', (bool) $featured);
            }

            return $query->paginate($perPage);
        });
    }

    /**
     * Find a single published car by slug. Throws ModelNotFoundException if not found.
     */
    public function findBySlug(string $slug): Car
    {
        $cacheKey = "cars:slug:{$slug}";

        return Cache::remember($cacheKey, self::CACHE_TTL, fn () => Car::published()
            ->with(['category', 'features'])
            ->where('slug', $slug)
            ->firstOrFail()
        );
    }

    /**
     * Return featured published cars up to a given limit. Cached separately.
     *
     * @return Collection<int, Car>
     */
    public function getFeatured(int $limit = 8): Collection
    {
        $cacheKey = "cars:featured:{$limit}";

        return Cache::remember($cacheKey, self::CACHE_TTL, fn () => Car::published()
            ->featured()
            ->ordered()
            ->with(['category'])
            ->limit($limit)
            ->get()
        );
    }

    /**
     * Clear all car-related cache keys.
     * Called by ClearCarCache listener after any CarSaved or CarDeleted event.
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}
