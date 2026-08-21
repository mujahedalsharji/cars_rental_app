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
