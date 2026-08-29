<?php

use App\Models\Car;
use App\Support\ServiceCatalog;
use Carbon\CarbonImmutable;

test('the sitemap contains public pages and published cars', function () {
    config()->set('app.url', 'https://fakhamahmosafer.com');

    $lastModified = CarbonImmutable::parse('2026-08-29 12:00:00');
    $publishedCar = Car::factory()->published()->create([
        'slug' => 'bmw-7-series-g70-2023',
        'updated_at' => $lastModified,
    ]);
    $unpublishedCar = Car::factory()->create([
        'slug' => 'unpublished-car',
    ]);

    $response = $this
        ->withHeader('Host', 'www.fakhamahmosafer.com')
        ->get('/sitemap.xml');

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('https://fakhamahmosafer.com/', false)
        ->assertSee('https://fakhamahmosafer.com/cars', false)
        ->assertSee('https://fakhamahmosafer.com/services', false)
        ->assertSee('https://fakhamahmosafer.com/cars/'.$publishedCar->slug, false)
        ->assertSee($lastModified->toAtomString(), false)
        ->assertDontSee('https://fakhamahmosafer.com/cars/'.$unpublishedCar->slug, false)
        ->assertDontSee('https://www.fakhamahmosafer.com', false);

    foreach (app(ServiceCatalog::class)->slugs() as $service) {
        $response->assertSee('https://fakhamahmosafer.com/services/'.$service, false);
    }

    expect(simplexml_load_string($response->getContent()))->not->toBeFalse();
});

test('robots file advertises the sitemap', function () {
    expect(file_get_contents(public_path('robots.txt')))
        ->toContain('Sitemap: https://fakhamahmosafer.com/sitemap.xml');
});

test('apache redirects alternate hosts and http traffic to the canonical origin', function () {
    expect(file_get_contents(public_path('.htaccess')))
        ->toContain('RewriteCond %{HTTP_HOST} !^fakhamahmosafer\.com$ [NC]')
        ->toContain('RewriteRule ^ https://fakhamahmosafer.com%{REQUEST_URI} [R=301,L,NE]')
        ->toContain('RewriteCond %{HTTPS} !=on');
});
