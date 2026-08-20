<?php

namespace App\Observers;

use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Support\Facades\Storage;

class BannerObserver
{
    public function __construct(private BannerService $bannerService) {}

    public function saved(Banner $banner): void
    {
        if ($banner->wasChanged('image')) {
            $this->deleteImage($banner->getOriginal('image'));
        }

        $this->bannerService->clearCache();
    }

    public function deleted(Banner $banner): void
    {
        $this->deleteImage($banner->image);
        $this->bannerService->clearCache();
    }

    private function deleteImage(?string $path): void
    {
        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }
    }
}
