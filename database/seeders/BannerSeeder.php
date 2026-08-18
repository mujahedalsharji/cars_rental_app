<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Drive Your Dream Car',
                'subtitle' => 'Explore our wide selection of premium vehicles available for rent.',
                'cta_text' => 'Browse Cars',
                'cta_url' => '/cars',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Luxury at Your Fingertips',
                'subtitle' => 'From sleek sedans to powerful SUVs — we have the right car for you.',
                'cta_text' => 'View Fleet',
                'cta_url' => '/cars',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Fast & Easy Inquiry',
                'subtitle' => 'Contact us directly on WhatsApp and get on the road today.',
                'cta_text' => 'Inquire Now',
                'cta_url' => '/contact',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($banners as $data) {
            Banner::firstOrCreate(['title' => $data['title']], $data);
        }
    }
}
