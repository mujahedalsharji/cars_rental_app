<?php

use App\Models\Car;
use App\Models\Category;

test('public pages render the Arabic website', function (string $routeName) {
    $this->get(route($routeName))
        ->assertSuccessful()
        ->assertSee('فخامة مسافر');
})->with([
    'home' => 'home',
    'fleet' => 'cars.index',
    'about' => 'about',
    'faq' => 'faq.index',
    'contact' => 'contact',
    'booking' => 'booking',
]);

test('published cars appear in the fleet and have a public detail page', function () {
    $category = Category::factory()->create();
    $car = Car::factory()->for($category)->published()->create([
        'name' => 'BMW 7 Series',
        'price_daily' => 900,
        'currency' => 'SAR',
    ]);

    $this->get(route('cars.index'))
        ->assertSuccessful()
        ->assertSee($car->name);

    $this->get(route('cars.show', $car->slug))
        ->assertSuccessful()
        ->assertSee($car->name)
        ->assertSee('900');
});

test('the services hub links to dedicated service pages', function () {
    $this->get(route('services'))
        ->assertSuccessful()
        ->assertSee(route('services.show', 'car-with-driver'), false)
        ->assertSee(route('services.show', 'jeddah-airport-to-makkah'), false)
        ->assertSee(route('services.show', 'makkah-to-madinah'), false)
        ->assertSee(route('services.show', 'hourly-private-driver'), false)
        ->assertSee('alt="أبراج البيت في مكة المكرمة"', false)
        ->assertSee('مصادر الصور وتراخيصها');
});

test('dedicated service pages have unique Arabic metadata and canonical URLs', function (string $service, string $title) {
    config()->set('app.url', 'https://fakhamahmosafer.com');

    $this->get(route('services.show', $service))
        ->assertSuccessful()
        ->assertSee($title)
        ->assertSee(
            '<link rel="canonical" href="https://fakhamahmosafer.com/services/'.$service.'">',
            false,
        );
})->with([
    'car with driver' => ['car-with-driver', 'سيارة مع سائق في مكة ومدن المملكة'],
    'airport transfer' => ['jeddah-airport-to-makkah', 'توصيل من مطار جدة إلى مكة'],
    'intercity trip' => ['makkah-to-madinah', 'رحلات من مكة إلى المدينة المنورة'],
    'hourly driver' => ['hourly-private-driver', 'خدمة سائق خاص بالساعة'],
]);

test('public pages have canonical URLs on the configured production domain', function () {
    config()->set('app.url', 'https://fakhamahmosafer.com');

    $this->get('/cars?category=suv&page=2&utm_source=google')
        ->assertSuccessful()
        ->assertSee('<link rel="canonical" href="https://fakhamahmosafer.com/cars">', false)
        ->assertDontSee('utm_source=google', false)
        ->assertDontSee('category=suv', false);
});

test('car detail canonical URLs retain the car path', function () {
    config()->set('app.url', 'https://fakhamahmosafer.com');
    $car = Car::factory()->published()->create([
        'slug' => 'bmw-7-series-g70-2023',
    ]);

    $this->get('/cars/bmw-7-series-g70-2023?utm_campaign=launch')
        ->assertSuccessful()
        ->assertSee('<link rel="canonical" href="https://fakhamahmosafer.com/cars/bmw-7-series-g70-2023">', false)
        ->assertDontSee('utm_campaign=launch', false);
});

test('unpublished cars are not publicly accessible', function () {
    $car = Car::factory()->create();

    $this->get(route('cars.show', $car->slug))->assertNotFound();
});

test('an invalid booking car falls back to the general booking form', function () {
    $this->get(route('booking', ['car' => 'missing-car']))
        ->assertSuccessful()
        ->assertSee('تفاصيل الطلب');
});

test('contact form validates required fields', function () {
    $this->from(route('contact'))
        ->post(route('contact.submit'), [])
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors(['name', 'email', 'message']);
});

test('contact form accepts a valid message', function () {
    $this->from(route('contact'))
        ->post(route('contact.submit'), [
            'name' => 'ضيف المملكة',
            'email' => 'guest@example.com',
            'phone' => '+966500000000',
            'message' => 'أرغب في معرفة تفاصيل الحجز.',
        ])
        ->assertRedirect(route('contact'))
        ->assertSessionHas('success');
});
