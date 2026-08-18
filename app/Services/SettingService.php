<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const CACHE_KEY = 'settings:all';

    private const CACHE_TTL = 86400; // 24 hours

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
            $grouped[$row['settings_group']][$key] = $this->cast($row['value'], $row['type']);
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
        Setting::where('key', $key)->update(['value' => $value]);
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
            Setting::where('key', $key)->update(['value' => $value]);
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
}
