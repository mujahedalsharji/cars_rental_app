<?php

use App\Models\Car;
use Carbon\CarbonImmutable;

test('the sitemap contains public pages and published cars', function () {
    $lastModified = CarbonImmutable::parse('2026-08-29 12:00:00');
    $publishedCar = Car::factory()->published()->create([
        'slug' => 'bmw-7-series-g70-2023',
        'updated_at' => $lastModified,
    ]);
    $unpublishedCar = Car::factory()->create([
        'slug' => 'unpublished-car',
    ]);

    $response = $this->get(route('sitemap'));

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(route('home'), false)
        ->assertSee(route('cars.index'), false)
        ->assertSee(route('services'), false)
        ->assertSee(route('cars.show', ['slug' => $publishedCar->slug]), false)
        ->assertSee($lastModified->toAtomString(), false)
        ->assertDontSee(route('cars.show', ['slug' => $unpublishedCar->slug]), false);

    expect(simplexml_load_string($response->getContent()))->not->toBeFalse();
});

test('robots file advertises the sitemap', function () {
    expect(file_get_contents(public_path('robots.txt')))
        ->toContain('Sitemap: https://fakhamahmosafer.com/sitemap.xml');
});
