<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class PublicSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $settings = (array) $this->resource;

        $seo = $settings['seo'] ?? [];
        if (isset($seo['google_analytics_id'])) {
            unset($seo['google_analytics_id']);
        }

        return [
            'company' => array_merge(Arr::except($settings['company'] ?? [], ['logo']), [
                'logo_url' => ($val = $settings['company']['logo'] ?? null) ? Storage::disk('public')->url($val) : null,
            ]),
            'contact' => $settings['contact'] ?? [],
            'social' => $settings['social'] ?? [],
            'seo' => $seo,
            'appearance' => array_merge(Arr::except($settings['appearance'] ?? [], ['favicon']), [
                'favicon_url' => ($val = $settings['appearance']['favicon'] ?? null) ? Storage::disk('public')->url($val) : null,
            ]),
        ];
    }
}
