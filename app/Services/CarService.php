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
        $page = (int) ($filters['page'] ?? 1);

        $cacheKey = "cars:v{$this->cacheVersion()}:list:{$category}:{$search}:{$featured}:{$perPage}:{$page}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($category, $search, $featured, $perPage, $page): LengthAwarePaginator {
            $query = Car::published()->ordered()->with(['category', 'features']);

            if ($category) {
                $query->inCategory($category);
            }

            if ($search) {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            }

            if ($featured !== null) {
                $query->where('is_featured', (bool) $featured);
            }

            return $query->paginate(perPage: $perPage, page: $page);
        });
    }

    /**
     * Find a single published car by slug. Throws ModelNotFoundException if not found.
     */
    public function findBySlug(string $slug): Car
    {
        $cacheKey = "cars:v{$this->cacheVersion()}:slug:{$slug}";

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
        $cacheKey = "cars:v{$this->cacheVersion()}:featured:{$limit}";

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
        $this->cacheVersion();
        Cache::increment('cars:cache_version');
    }

    private function cacheVersion(): int
    {
        return (int) Cache::rememberForever('cars:cache_version', fn (): int => 1);
    }
}
