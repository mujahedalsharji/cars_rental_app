<?php

use App\Models\Car;
use App\Models\Category;
use App\Models\Setting;
use App\Services\SettingService;

/**
 * @return array<int, array<string, mixed>>
 */
function structuredDataNodes(string $content): array
{
    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $content, $matches);

    return collect($matches[1])
        ->map(fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR))
        ->flatMap(fn (array $document): array => $document['@graph'] ?? [$document])
        ->values()
        ->all();
}

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
        ->assertSee(route('services.show', 'jeddah-airport-taxi'), false)
        ->assertSee(route('services.show', 'madinah-airport-transfer'), false)
        ->assertSee(route('services.show', 'taif-airport-to-makkah'), false)
        ->assertSee(route('services.show', 'jeddah-to-makkah'), false)
        ->assertSee(route('services.show', 'makkah-to-madinah'), false)
        ->assertSee(route('services.show', 'hourly-private-driver'), false)
        ->assertSee('assets/images/services/car-with-driver.webp', false)
        ->assertSee('assets/images/services/jeddah-airport-transfer.webp', false)
        ->assertSee('assets/images/services/jeddah-airport-taxi.webp', false)
        ->assertSee('assets/images/services/madinah-airport-transfer.webp', false)
        ->assertSee('assets/images/services/taif-airport-to-makkah.webp', false)
        ->assertSee('assets/images/services/jeddah-to-makkah.webp', false)
        ->assertSee('مكة المكرمة')
        ->assertDontSee('مصادر الصور وتراخيصها');
});

test('the homepage displays the popular services carousel before the fleet', function () {
    $response = $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('data-service-carousel', false)
        ->assertSee('data-service-carousel-card', false)
        ->assertSee('data-carousel-position="0"', false)
        ->assertSee('data-carousel-position="-1"', false)
        ->assertSee('data-autoplay-ms="10000"', false)
        ->assertSee(route('services.show', 'car-with-driver'), false)
        ->assertSee(route('services.show', 'jeddah-airport-to-makkah'), false)
        ->assertSee(route('services.show', 'jeddah-airport-taxi'), false)
        ->assertSee(route('services.show', 'madinah-airport-transfer'), false)
        ->assertSee(route('services.show', 'taif-airport-to-makkah'), false)
        ->assertSee(route('services.show', 'jeddah-to-makkah'), false)
        ->assertSee('assets/images/services/car-with-driver.webp', false)
        ->assertDontSee('<div class="absolute inset-0 bg-black/15"></div>', false)
        ->assertDontSee('bg-gradient-to-t from-black via-black/55 to-transparent', false)
        ->assertSee('الخدمات الأكثر طلباً');

    $content = $response->getContent();

    expect(strpos($content, 'data-service-carousel'))
        ->toBeLessThan(strpos($content, 'أسطولنا المختار'));
});

test('the homepage keeps the fleet button visible and hides the booking button on phone screens', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('px-5 pb-28 pt-16 text-center sm:pb-8 lg:px-8', false)
        ->assertSee(
            'href="'.route('booking').'" class="hidden min-h-14 items-center justify-center gap-3 rounded-2xl border border-gold/60 bg-black/30 px-8 font-bold text-white backdrop-blur-md transition hover:bg-gold hover:text-ink sm:inline-flex"',
            false,
        );
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
    'Jeddah airport taxi' => ['jeddah-airport-taxi', 'تاكسي مطار جدة وسيارة خاصة من وإلى المطار'],
    'Madinah airport transfer' => ['madinah-airport-transfer', 'توصيل من مطار المدينة المنورة إلى الفندق'],
    'Taif airport transfer' => ['taif-airport-to-makkah', 'توصيل من مطار الطائف إلى مكة'],
    'Jeddah to Makkah' => ['jeddah-to-makkah', 'سيارة من جدة إلى مكة مع سائق'],
    'intercity trip' => ['makkah-to-madinah', 'رحلات من مكة إلى المدينة المنورة'],
    'hourly driver' => ['hourly-private-driver', 'خدمة سائق خاص بالساعة'],
]);

test('public pages have canonical URLs on the configured production domain', function (string $path, string $canonical) {
    config()->set('app.url', 'https://fakhamahmosafer.com');

    $separator = str_contains($path, '?') ? '&' : '?';

    $this->withHeader('Host', 'www.fakhamahmosafer.com')
        ->get($path.$separator.'utm_source=google')
        ->assertSuccessful()
        ->assertSee('<link rel="canonical" href="'.$canonical.'">', false)
        ->assertDontSee('utm_source=google', false)
        ->assertDontSee('href="'.$canonical.'?"', false);
})->with([
    'home' => ['/', 'https://fakhamahmosafer.com/'],
    'fleet' => ['/cars?category=suv&page=2', 'https://fakhamahmosafer.com/cars'],
    'services' => ['/services', 'https://fakhamahmosafer.com/services'],
    'about with trailing slash' => ['/about/', 'https://fakhamahmosafer.com/about'],
    'faq' => ['/faq', 'https://fakhamahmosafer.com/faq'],
    'contact' => ['/contact', 'https://fakhamahmosafer.com/contact'],
    'booking' => ['/booking?car=bmw-7-series', 'https://fakhamahmosafer.com/booking'],
]);

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

test('the public layout publishes organization and website structured data', function () {
    config()->set('app.url', 'https://fakhamahmosafer.com');

    Setting::query()->upsert([
        ['key' => 'company.name', 'value' => 'فخامة مسافر', 'type' => 'string', 'settings_group' => 'company'],
        ['key' => 'contact.phone_primary', 'value' => '+966500000000', 'type' => 'string', 'settings_group' => 'contact'],
        ['key' => 'contact.email', 'value' => 'booking@example.com', 'type' => 'string', 'settings_group' => 'contact'],
        ['key' => 'social.instagram_url', 'value' => 'https://instagram.com/fakhamahmosafer', 'type' => 'string', 'settings_group' => 'social'],
    ], ['key'], ['value', 'type', 'settings_group']);

    app(SettingService::class)->clearCache();

    $nodes = structuredDataNodes($this->get(route('home'))->assertSuccessful()->getContent());
    $organization = collect($nodes)->firstWhere('@type', 'Organization');
    $website = collect($nodes)->firstWhere('@type', 'WebSite');

    expect($organization)
        ->not->toBeNull()
        ->and($organization['@id'])->toBe('https://fakhamahmosafer.com/#organization')
        ->and($organization['name'])->toBe('فخامة مسافر')
        ->and($organization['url'])->toBe('https://fakhamahmosafer.com/')
        ->and($organization['contactPoint']['telephone'])->toBe('+966500000000')
        ->and($organization['contactPoint']['email'])->toBe('booking@example.com')
        ->and($organization['sameAs'])->toBe(['https://instagram.com/fakhamahmosafer'])
        ->and($website)
        ->not->toBeNull()
        ->and($website['publisher']['@id'])->toBe('https://fakhamahmosafer.com/#organization')
        ->and($website['inLanguage'])->toBe('ar');
});

test('dedicated service pages publish service and breadcrumb structured data', function () {
    config()->set('app.url', 'https://fakhamahmosafer.com');

    $nodes = structuredDataNodes(
        $this->get(route('services.show', 'jeddah-airport-to-makkah'))
            ->assertSuccessful()
            ->getContent(),
    );
    $service = collect($nodes)->firstWhere('@type', 'Service');
    $breadcrumbs = collect($nodes)->firstWhere('@type', 'BreadcrumbList');

    expect($service)
        ->not->toBeNull()
        ->and($service['@id'])->toBe('https://fakhamahmosafer.com/services/jeddah-airport-to-makkah#service')
        ->and($service['name'])->toBe('توصيل من مطار جدة إلى مكة')
        ->and($service['provider']['@id'])->toBe('https://fakhamahmosafer.com/#organization')
        ->and($service['areaServed']['name'])->toBe('المملكة العربية السعودية')
        ->and($breadcrumbs)
        ->not->toBeNull()
        ->and($breadcrumbs['itemListElement'])->toHaveCount(3)
        ->and($breadcrumbs['itemListElement'][1]['item'])->toBe('https://fakhamahmosafer.com/services')
        ->and($breadcrumbs['itemListElement'][2]['name'])->toBe('مطار جدة إلى مكة');
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

test('the booking form applies the required and optional field constraints', function () {
    $this->get(route('booking'))
        ->assertSuccessful()
        ->assertSee('data-letters-only', false)
        ->assertSee('data-digits-only', false)
        ->assertSee('pattern="[\p{L}\p{M} ]+"', false)
        ->assertSee('pattern="[0-9]{9}"', false)
        ->assertSee('minlength="9" maxlength="9"', false)
        ->assertSee('<option value="" selected>غير محدد</option>', false)
        ->assertDontSee('name="phone" type="tel" required', false)
        ->assertDontSee('name="time" type="time" required', false);
});

test('WhatsApp booking paths are connected to the trip number service', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('name="trip-number-url"', false)
        ->assertSee(route('trip-numbers.store'), false)
        ->assertSee('data-home-quote-form', false);

    $this->get(route('services'))
        ->assertSuccessful()
        ->assertSee('data-whatsapp-number=', false)
        ->assertSee('data-whatsapp-message=', false);

    $this->get(route('services.show', 'car-with-driver'))
        ->assertSuccessful()
        ->assertSee('data-whatsapp-number=', false)
        ->assertSee('data-whatsapp-message=', false);
});

test('the booking page escapes query input before rendering it', function () {
    $unsafePickup = '\"><script>alert(1)</script>';

    $this->get(route('booking', ['pickup' => $unsafePickup]))
        ->assertSuccessful()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
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
