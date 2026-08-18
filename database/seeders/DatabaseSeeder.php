<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Admin user ─────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('cars1234'),
            ]
        );

        // ── Domain seeders ─────────────────────────────────────────────────────
        $this->call([
            SettingSeeder::class,
            CategorySeeder::class,
            BannerSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
