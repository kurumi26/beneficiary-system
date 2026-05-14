<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('DEFAULT_SUPER_ADMIN_EMAIL', 'superadmin@gov.local')],
            [
                'name' => 'Super Admin',
                'role' => User::ROLE_SUPER_ADMIN,
                'email_verified_at' => now(),
                'password' => Hash::make(env('DEFAULT_SUPER_ADMIN_PASSWORD', 'Admin@12345')),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => env('DEFAULT_STAFF_ADMIN_EMAIL', 'staffadmin@gov.local')],
            [
                'name' => 'Staff Admin',
                'role' => User::ROLE_STAFF_ADMIN,
                'email_verified_at' => now(),
                'password' => Hash::make(env('DEFAULT_STAFF_ADMIN_PASSWORD', 'Admin@12345')),
            ],
        );
    }
}
