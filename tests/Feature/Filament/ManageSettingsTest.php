<?php

use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('the settings page loads and saves the documented keys', function () {
    $user = User::factory()->admin()->create();
    $settings = [
        ['key' => 'company.name', 'value' => 'Original Name', 'type' => 'string', 'settings_group' => 'company'],
        ['key' => 'contact.whatsapp_number', 'value' => '967700000000', 'type' => 'string', 'settings_group' => 'contact'],
        ['key' => 'social.facebook_url', 'value' => 'https://facebook.com/original', 'type' => 'string', 'settings_group' => 'social'],
    ];

    foreach ($settings as $setting) {
        Setting::query()->create($setting);
    }

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($user);

    Livewire::test(ManageSettings::class)
        ->assertOk()
        ->assertSchemaStateSet([
            'company_name' => 'Original Name',
            'contact_whatsapp_number' => '967700000000',
            'social_facebook_url' => 'https://facebook.com/original',
        ])
        ->fillForm([
            'company_name' => 'Updated Name',
            'contact_whatsapp_number' => '967711111111',
            'social_facebook_url' => 'https://facebook.com/updated',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified('Settings saved successfully');

    expect(Setting::query()->where('key', 'company.name')->value('value'))->toBe('Updated Name')
        ->and(Setting::query()->where('key', 'contact.whatsapp_number')->value('value'))->toBe('967711111111')
        ->and(Setting::query()->where('key', 'social.facebook_url')->value('value'))->toBe('https://facebook.com/updated');
});

test('the settings page creates missing contact and favicon settings', function () {
    Storage::fake('public');
    $user = User::factory()->admin()->create();
    $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($user);

    Livewire::test(ManageSettings::class)
        ->fillForm([
            'contact_phone_primary' => '+9671234567',
            'contact_email' => 'booking@example.com',
            'contact_whatsapp_number' => '967700000000',
            'appearance_favicon' => UploadedFile::fake()->createWithContent('favicon.png', $imageContent),
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified('Settings saved successfully');

    $faviconPath = Setting::query()->where('key', 'appearance.favicon')->value('value');

    expect(Setting::query()->where('key', 'contact.phone_primary')->value('value'))->toBe('+9671234567')
        ->and(Setting::query()->where('key', 'contact.email')->value('value'))->toBe('booking@example.com')
        ->and(Setting::query()->where('key', 'contact.whatsapp_number')->value('value'))->toBe('967700000000')
        ->and($faviconPath)->toBeString();

    Storage::disk('public')->assertExists($faviconPath);
});
