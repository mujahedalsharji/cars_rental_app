<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // ── Company ──────────────────────────────────────────────────────
            ['key' => 'company.name',         'value' => 'فخامة مسافر',      'type' => 'string', 'settings_group' => 'company',     'description' => 'Company display name'],
            ['key' => 'company.tagline',       'value' => 'رحلة فاخرة تليق بضيوف المملكة',   'type' => 'string', 'settings_group' => 'company',     'description' => 'Short marketing tagline'],
            ['key' => 'company.description',   'value' => null,               'type' => 'text',   'settings_group' => 'company',     'description' => 'About us text'],
            ['key' => 'company.logo',          'value' => null,               'type' => 'string', 'settings_group' => 'company',     'description' => 'File path to company logo'],
            ['key' => 'company.about_text',    'value' => null,               'type' => 'text',   'settings_group' => 'company',     'description' => 'Longer about us content'],

            // ── Contact ───────────────────────────────────────────────────────
            ['key' => 'contact.phone_primary',    'value' => null, 'type' => 'string', 'settings_group' => 'contact', 'description' => 'Primary phone number'],
            ['key' => 'contact.phone_secondary',  'value' => null, 'type' => 'string', 'settings_group' => 'contact', 'description' => 'Secondary phone number'],
            ['key' => 'contact.email',            'value' => null, 'type' => 'string', 'settings_group' => 'contact', 'description' => 'Contact email address'],
            ['key' => 'contact.address',          'value' => null, 'type' => 'text',   'settings_group' => 'contact', 'description' => 'Physical address'],
            ['key' => 'contact.whatsapp_number',  'value' => null, 'type' => 'string', 'settings_group' => 'contact', 'description' => 'WhatsApp number used by the booking form'],

            // ── Social ────────────────────────────────────────────────────────
            ['key' => 'social.facebook_url',   'value' => null, 'type' => 'string', 'settings_group' => 'social', 'description' => 'Facebook page URL'],
            ['key' => 'social.instagram_url',  'value' => null, 'type' => 'string', 'settings_group' => 'social', 'description' => 'Instagram profile URL'],
            ['key' => 'social.twitter_url',    'value' => null, 'type' => 'string', 'settings_group' => 'social', 'description' => 'Twitter/X profile URL'],
            ['key' => 'social.youtube_url',    'value' => null, 'type' => 'string', 'settings_group' => 'social', 'description' => 'YouTube channel URL'],
            ['key' => 'social.tiktok_url',     'value' => null, 'type' => 'string', 'settings_group' => 'social', 'description' => 'TikTok profile URL'],
            ['key' => 'social.linkedin_url',   'value' => null, 'type' => 'string', 'settings_group' => 'social', 'description' => 'LinkedIn page URL'],

            // ── SEO ───────────────────────────────────────────────────────────
            ['key' => 'seo.site_title',           'value' => 'فخامة مسافر', 'type' => 'string', 'settings_group' => 'seo', 'description' => 'Default browser tab title'],
            ['key' => 'seo.meta_description',     'value' => null,          'type' => 'string', 'settings_group' => 'seo', 'description' => 'Default meta description'],
            ['key' => 'seo.meta_keywords',        'value' => null,          'type' => 'string', 'settings_group' => 'seo', 'description' => 'Default meta keywords'],
            ['key' => 'seo.google_analytics_id',  'value' => null,          'type' => 'string', 'settings_group' => 'seo', 'description' => 'Google Analytics tracking ID'],

            // ── Appearance ────────────────────────────────────────────────────
            ['key' => 'appearance.favicon',           'value' => null,      'type' => 'string', 'settings_group' => 'appearance', 'description' => 'File path to favicon'],
            ['key' => 'appearance.primary_color',     'value' => '#1a73e8', 'type' => 'string', 'settings_group' => 'appearance', 'description' => 'Brand hex color'],
            ['key' => 'appearance.secondary_color',   'value' => '#fbbc04', 'type' => 'string', 'settings_group' => 'appearance', 'description' => 'Secondary hex color'],

            // ── System ────────────────────────────────────────────────────────
            ['key' => 'system.maintenance_mode', 'value' => '0',  'type' => 'boolean', 'settings_group' => 'system', 'description' => '1 = site shows maintenance page'],
            ['key' => 'system.app_locale',        'value' => 'en', 'type' => 'string',  'settings_group' => 'system', 'description' => 'Default language (ar or en)'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
