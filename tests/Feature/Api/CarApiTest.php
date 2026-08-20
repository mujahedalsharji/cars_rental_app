<?php

use App\Models\Car;
use App\Models\Category;
use Illuminate\Support\Str;

test('the car list follows the public API contract', function () {
    $category = Category::factory()->create(['name' => 'Luxury']);

    Car::factory()->featured()->for($category)->create([
        'name' => 'Roadster',
        'brand' => 'BMW',
        'price_daily' => 200,
        'currency' => 'AED',
    ]);

    Car::factory()->for($category)->create([
        'name' => 'Unpublished BMW',
        'brand' => 'BMW',
    ]);

    $this->getJson('/api/cars?search=BMW&featured=true')
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.category.name', 'Luxury')
        ->assertJsonPath('data.0.cover_image', null)
        ->assertJsonMissingPath('data.0.price_daily')
        ->assertJsonMissingPath('data.0.currency')
        ->assertJsonMissingPath('data.0.is_published')
        ->assertJsonStructure([
            'success',
            'data',
            'meta' => ['current_page', 'per_page', 'total', 'last_page', 'from', 'to'],
            'links' => ['first', 'last', 'prev', 'next'],
        ]);
});

test('invalid car filters return the validation envelope', function (array $query) {
    $this->getJson('/api/cars?'.http_build_query($query))
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
})->with([
    'page below one' => [['page' => 0]],
    'page size above maximum' => [['per_page' => 101]],
    'unknown category' => [['category' => 'missing']],
    'search above maximum' => [['search' => Str::repeat('a', 101)]],
]);

test('inactive categories cannot be used as filters', function () {
    $category = Category::factory()->inactive()->create();

    $this->getJson('/api/cars?category='.$category->slug)
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

test('unpublished cars return the not found envelope', function () {
    $car = Car::factory()->create();

    $this->getJson('/api/cars/'.$car->slug)
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'NOT_FOUND')
        ->assertJsonMissingPath('exception');
});
