<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Car extends Model implements HasMedia
{
    use InteractsWithMedia;

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'brand',
        'model',
        'year',
        'color',
        'description',
        'specifications',
        'price_daily',
        'price_weekly',
        'price_monthly',
        'currency',
        'is_published',
        'is_featured',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'specifications' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'year' => 'integer',
            'sort_order' => 'integer',
            'price_daily' => 'decimal:2',
            'price_weekly' => 'decimal:2',
            'price_monthly' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Car $car): void {
            if (blank($car->slug)) {
                $car->slug = static::generateUniqueSlug($car->name);
            }
        });
    }

    /**
     * Generate a unique slug by suffixing -2, -3, etc. on collisions.
     */
    protected static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    // ─── Media ────────────────────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('car_images')
            ->useDisk('public');
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(CarFeature::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** @param  Builder<Car>  $query */
    public function scopePublished($query): void
    {
        $query->where('is_published', true);
    }

    /** @param  Builder<Car>  $query */
    public function scopeFeatured($query): void
    {
        $query->where('is_featured', true);
    }

    /** @param  Builder<Car>  $query */
    public function scopeOrdered($query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<Car>  $query
     */
    public function scopeInCategory($query, string $slug): void
    {
        $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
    }
}
