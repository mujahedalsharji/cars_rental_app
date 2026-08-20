<?php

namespace App\Observers;

use App\Models\Car;
use App\Services\CarService;

class CarObserver
{
    public function __construct(private CarService $carService) {}

    public function saved(Car $car): void
    {
        $this->carService->clearCache();
    }

    public function deleted(Car $car): void
    {
        $this->carService->clearCache();
    }
}
