<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        if ($email && $password) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
                    'password' => Hash::make($password),
                    'role' => 'super_admin',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
            );
        }

        Provider::firstOrCreate(
            ['code' => 'smsbower'],
            [
                'name' => 'SMSBower',
                'base_url' => env('SMSBOWER_BASE_URL', 'https://smsbower.app/stubs/handler_api.php'),
                'is_active' => true,
            ],
        );
    }
}
