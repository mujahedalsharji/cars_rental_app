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
        $adminEmail = config('car_rental.admin_email');
        $adminPassword = config('car_rental.admin_password');

        if (is_string($adminEmail) && $adminEmail !== '' && is_string($adminPassword) && $adminPassword !== '') {
            User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Admin',
                    'password' => $adminPassword,
                    'is_admin' => true,
                ]
            );
        } else {
            $this->command?->warn('Admin user was not seeded because ADMIN_EMAIL or ADMIN_PASSWORD is missing.');
        }

        $this->call([
            SettingSeeder::class,
            CategorySeeder::class,
            BannerSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
