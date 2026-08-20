<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Storage;

class CategoryObserver
{
    public function __construct(private CategoryService $categoryService) {}

    public function saved(Category $category): void
    {
        if ($category->wasChanged('image')) {
            $this->deleteImage($category->getOriginal('image'));
        }

        $this->categoryService->clearCache();
    }

    public function deleted(Category $category): void
    {
        $this->deleteImage($category->image);
        $this->categoryService->clearCache();
    }

    private function deleteImage(?string $path): void
    {
        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }
    }
}
