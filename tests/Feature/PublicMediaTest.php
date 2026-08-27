<?php

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Support\Facades\Storage;

test('public images are served through Laravel without a storage symlink', function () {
    Storage::fake('public');
    $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);
    Storage::disk('public')->put('cars/example.png', $image);

    $response = $this->get('/media/cars/example.png')
        ->assertSuccessful();

    expect($response->headers->get('cache-control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable')
        ->and(config('filesystems.disks.public.url'))->toBe('/media');
});

test('the public layout uses saved contact and appearance settings', function () {
    app(SettingService::class)->setBulk([
        'contact.phone_primary' => '+967 1 234 567',
        'contact.email' => 'booking@example.com',
        'appearance.favicon' => 'settings/favicon.png',
    ]);

    $this->get(route('contact'))
        ->assertSuccessful()
        ->assertSee('+967 1 234 567')
        ->assertSee('booking@example.com')
        ->assertSee('href="/media/settings/favicon.png"', false);

    expect(Setting::query()->where('key', 'appearance.favicon')->value('value'))
        ->toBe('settings/favicon.png');
});

test('missing and unsafe public media paths return not found', function () {
    Storage::fake('public');
    Storage::disk('public')->put('documents/private.txt', 'not an image');

    $this->get('/media/missing.png')->assertNotFound();
    $this->get('/media/documents/private.txt')->assertNotFound();
});
