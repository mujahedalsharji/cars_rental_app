<?php

namespace App\Observers;

use App\Models\CarFeature;
use App\Services\CarService;

class CarFeatureObserver
{
    public function __construct(private CarService $carService) {}

    public function saved(CarFeature $carFeature): void
    {
        $this->carService->clearCache();
    }

    public function deleted(CarFeature $carFeature): void
    {
        $this->carService->clearCache();
    }
}
