<?php

use App\Models\Setting;
use App\Services\SettingService;

test('settings are grouped with prefixes removed and values cast', function () {
    Setting::query()->create([
        'key' => 'company.name',
        'value' => 'Cars Rental',
        'type' => 'string',
        'settings_group' => 'company',
    ]);
    Setting::query()->create([
        'key' => 'system.maintenance_mode',
        'value' => '1',
        'type' => 'boolean',
        'settings_group' => 'system',
    ]);

    $settings = app(SettingService::class)->all();

    expect($settings)
        ->toHaveKey('company.name', 'Cars Rental')
        ->toHaveKey('system.maintenance_mode', true);
});

test('bulk updates use the fixed dot notation keys', function () {
    $setting = Setting::query()->create([
        'key' => 'contact.whatsapp_number',
        'value' => null,
        'type' => 'string',
        'settings_group' => 'contact',
    ]);

    app(SettingService::class)->setBulk([
        'contact.whatsapp_number' => '967733333333',
    ]);

    expect($setting->refresh()->value)->toBe('967733333333');
});
