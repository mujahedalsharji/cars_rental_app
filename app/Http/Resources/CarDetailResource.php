<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'is_featured' => $this->is_featured,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'description' => $this->description,
            'specifications' => $this->specifications,
            'features' => $this->features->pluck('feature')->toArray(),
            'images' => $this->getMedia('car_images')->map(fn ($media) => [
                'url' => $media->getUrl(),
                'is_cover' => (bool) ($media->custom_properties['is_cover'] ?? false),
                'order' => $media->order_column,
            ])->sortBy('order')->values(),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'created_at' => $this->created_at,
        ];
    }
}
