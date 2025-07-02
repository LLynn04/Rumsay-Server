<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create or update admin
        User::updateOrCreate(
            ['email' => 'admin@service.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+1234567890',
                'email_verified_at' => now(),
            ]
        );

        // Create or update test user
        User::updateOrCreate(
            ['email' => 'user@service.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone' => '+1234567891',
            ]
        );
    }
}
