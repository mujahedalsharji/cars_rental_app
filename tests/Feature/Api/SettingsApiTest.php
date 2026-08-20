<?php

use App\Models\Setting;

test('public settings exclude internal groups keys and raw file paths', function () {
    $settings = [
        ['key' => 'company.name', 'value' => 'Cars Rental', 'type' => 'string', 'settings_group' => 'company'],
        ['key' => 'company.logo', 'value' => 'settings/logo.webp', 'type' => 'string', 'settings_group' => 'company'],
        ['key' => 'contact.whatsapp_number', 'value' => '967700000000', 'type' => 'string', 'settings_group' => 'contact'],
        ['key' => 'seo.google_analytics_id', 'value' => 'G-SECRET', 'type' => 'string', 'settings_group' => 'seo'],
        ['key' => 'system.maintenance_mode', 'value' => '0', 'type' => 'boolean', 'settings_group' => 'system'],
    ];

    foreach ($settings as $setting) {
        Setting::query()->create($setting);
    }

    $this->getJson('/api/settings')
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.company.name', 'Cars Rental')
        ->assertJsonPath('data.contact.whatsapp_number', '967700000000')
        ->assertJsonMissingPath('data.company.logo')
        ->assertJsonMissingPath('data.seo.google_analytics_id')
        ->assertJsonMissingPath('data.system');
});

test('the contact endpoint uses the documented whatsapp key', function () {
    Setting::query()->create([
        'key' => 'contact.whatsapp_number',
        'value' => '967711111111',
        'type' => 'string',
        'settings_group' => 'contact',
    ]);

    $this->getJson('/api/settings/contact')
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.whatsapp_number', '967711111111');
});
