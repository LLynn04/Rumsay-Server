<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Service;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user (email verified by default)
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@service.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1234567890',
            'email_verified_at' => now(),
        ]);

        // Create test user (not verified)
        User::create([
            'name' => 'Test User',
            'email' => 'user@service.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'phone' => '+1234567891',
        ]);

        // Create 5 mock services
        $services = [
            [
                'name' => 'House Cleaning',
                'description' => 'Professional house cleaning service including all rooms, kitchen, and bathrooms.',
                'price' => 89.99,
                'duration' => 120, // 2 hours
                'category' => 'Cleaning',
                'is_active' => true,
            ],
            [
                'name' => 'Plumbing Repair',
                'description' => 'Expert plumbing services for leaks, installations, and repairs.',
                'price' => 125.00,
                'duration' => 90, // 1.5 hours
                'category' => 'Maintenance',
                'is_active' => true,
            ],
            [
                'name' => 'Electrical Installation',
                'description' => 'Safe and certified electrical installation and repair services.',
                'price' => 150.00,
                'duration' => 180, // 3 hours
                'category' => 'Maintenance',
                'is_active' => true,
            ],
            [
                'name' => 'Garden Landscaping',
                'description' => 'Complete garden design and landscaping services for your outdoor space.',
                'price' => 299.99,
                'duration' => 480, // 8 hours
                'category' => 'Landscaping',
                'is_active' => true,
            ],
            [
                'name' => 'AC Maintenance',
                'description' => 'Air conditioning cleaning, maintenance, and repair services.',
                'price' => 75.00,
                'duration' => 60, // 1 hour
                'category' => 'Maintenance',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
