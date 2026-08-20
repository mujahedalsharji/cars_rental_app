<?php

use App\Models\Car;
use App\Models\Category;
use App\Services\CarService;
use App\Services\CategoryService;

test('category writes invalidate the active category cache', function () {
    Category::factory()->create();

    $service = app(CategoryService::class);

    expect($service->getActive())->toHaveCount(1);

    Category::factory()->create();

    expect($service->getActive())->toHaveCount(2);
});

test('car writes invalidate cached public lists', function () {
    $category = Category::factory()->create();
    Car::factory()->published()->for($category)->create();

    $service = app(CarService::class);

    expect($service->getAllPublished())->toHaveCount(1);

    Car::factory()->published()->for($category)->create();

    expect($service->getAllPublished())->toHaveCount(2);
});
