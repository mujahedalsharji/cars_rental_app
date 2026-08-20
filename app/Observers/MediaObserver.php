<?php

namespace App\Observers;

use App\Models\Car;
use App\Services\CarService;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaObserver
{
    public function __construct(private CarService $carService) {}

    public function saved(Media $media): void
    {
        if ($this->isCarImage($media)) {
            $this->synchronizeCover($media->model_type, $media->model_id, $media->collection_name);
            $this->carService->clearCache();
        }
    }

    public function deleted(Media $media): void
    {
        if ($this->isCarImage($media)) {
            $this->synchronizeCover($media->model_type, $media->model_id, $media->collection_name);
            $this->carService->clearCache();
        }
    }

    private function isCarImage(Media $media): bool
    {
        return $media->model_type === Car::class && $media->collection_name === 'car_images';
    }

    private function synchronizeCover(string $modelType, int $modelId, string $collectionName): void
    {
        $mediaItems = Media::query()
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->where('collection_name', $collectionName)
            ->orderBy('order_column')
            ->orderBy('id')
            ->get();

        foreach ($mediaItems as $index => $mediaItem) {
            $isCover = $index === 0;

            if (! $mediaItem->hasCustomProperty('is_cover')
                || (bool) $mediaItem->getCustomProperty('is_cover') !== $isCover) {
                $mediaItem->setCustomProperty('is_cover', $isCover);
                $mediaItem->saveQuietly();
            }
        }
    }
}
