<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'image_url' => $this->image ? Storage::url($this->image) : null,
            'cta' => ($this->cta_text || $this->cta_url) ? [
                'text' => $this->cta_text,
                'url' => $this->cta_url,
            ] : null,
        ];
    }
}
