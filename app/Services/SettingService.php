<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SettingService
{
    private const CACHE_KEY = 'settings:all';

    private const CACHE_TTL = 86400; // 24 hours

    /** @var list<string> */
    private const IMAGE_KEYS = ['company.logo', 'appearance.favicon'];

    /** @var array<string, string> */
    private const SETTING_TYPES = [
        'company.description' => 'text',
        'company.about_text' => 'text',
        'contact.address' => 'text',
        'seo.meta_description' => 'text',
        'system.maintenance_mode' => 'boolean',
    ];

    public function __construct(private MediaService $mediaService) {}

    /**
     * Get a single setting value by key, type-cast according to its `type` column.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->loadAll();

        if (! array_key_exists($key, $all)) {
            return $default;
        }

        return $this->cast($all[$key]['value'], $all[$key]['type']);
    }

    /**
     * Get all settings as a nested array grouped by settings_group.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        $flat = $this->loadAll();
        $grouped = [];

        foreach ($flat as $key => $row) {
            $shortKey = Str::after($key, '.');
            $grouped[$row['settings_group']][$shortKey] = $this->cast($row['value'], $row['type']);
        }

        return $grouped;
    }

    /**
     * Get all settings for a single group.
     *
     * @return array<string, mixed>
     */
    public function getGroup(string $group): array
    {
        return $this->all()[$group] ?? [];
    }

    /**
     * Update a single setting value and clear the cache.
     */
    public function set(string $key, mixed $value): void
    {
        $this->updateValue($key, $value);
        $this->clearCache();
    }

    /**
     * Update multiple settings at once and clear the cache once.
     *
     * @param  array<string, mixed>  $keyValues
     */
    public function setBulk(array $keyValues): void
    {
        foreach ($keyValues as $key => $value) {
            $this->updateValue($key, $value);
        }

        $this->clearCache();
    }

    /**
     * Clear the settings cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Load all settings from cache or database, keyed by `key` column.
     *
     * @return array<string, array{value: mixed, type: string, settings_group: string}>
     */
    private function loadAll(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): array {
            return Setting::all()->keyBy('key')->map(fn ($s) => [
                'value' => $s->value,
                'type' => $s->type,
                'settings_group' => $s->settings_group,
            ])->toArray();
        });
    }

    /**
     * Cast a raw value from the database to its correct PHP type.
     */
    private function cast(mixed $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'integer' => (int) $value,
            'boolean' => (bool) (int) $value,
            'json' => json_decode((string) $value, true),
            default => (string) $value,
        };
    }

    private function updateValue(string $key, mixed $value): void
    {
        $setting = Setting::query()->firstOrNew(['key' => $key]);

        if (! $setting->exists) {
            $setting->type = self::SETTING_TYPES[$key] ?? $this->inferType($value);
            $setting->settings_group = Str::before($key, '.');
        }

        $previousValue = $setting->value;
        $normalizedValue = $this->normalizeValue($key, $value);
        $setting->value = $normalizedValue;
        $setting->save();

        if (in_array($key, self::IMAGE_KEYS, true)
            && is_string($previousValue)
            && $previousValue !== ''
            && $previousValue !== $normalizedValue) {
            $this->mediaService->deleteImage($previousValue);
        }
    }

    private function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    private function normalizeValue(string $key, mixed $value): mixed
    {
        if (in_array($key, self::IMAGE_KEYS, true) && is_array($value)) {
            return Arr::first($value);
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return $value;
    }
}
