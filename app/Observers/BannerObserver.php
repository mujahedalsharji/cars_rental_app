<?php

namespace App\Observers;

use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerObserver
{
    /**
     * Delete the associated image file from storage when a banner is hard-deleted.
     */
    public function deleted(Banner $banner): void
    {
        if ($banner->image !== null) {
            Storage::disk('public')->delete($banner->image);
        }
    }
}
