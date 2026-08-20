<?php

use App\Filament\Pages\ManageSettings;
use App\Models\Setting;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('the settings page loads and saves the documented keys', function () {
    $user = User::factory()->create();
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
