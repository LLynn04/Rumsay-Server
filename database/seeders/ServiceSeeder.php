<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Spa & Massage',
                'description' => 'Relaxing full-body massage and spa treatments to rejuvenate your body and mind.',
                'price' => 89.99,
                'duration' => 120,
                'category' => 'Wellness & Spa',
                'is_active' => true,
                'image' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSFBvH16_vro6urmYQZab4yPTOfKYFvbJZ-iQ&s',
            ],
            [
                'name' => 'Room Service Dining',
                'description' => 'Delicious meals delivered directly to your room 24/7.',
                'price' => 45.00,
                'duration' => 30,
                'category' => 'Dining & Food',
                'is_active' => true,
                'image' => 'https://media.istockphoto.com/id/1209739507/photo/all-that-you-need-waitress-in-uniform-delivering-tray-with-food-in-a-room-of-hotel-room.jpg?s=612x612&w=0&k=20&c=z90y1f283lp57wFmFX6iRp3yiQ1iLEY8qPPhV6J6pYg=',
            ],
            [
                'name' => 'Airport Shuttle',
                'description' => 'Convenient and comfortable shuttle service to and from the airport.',
                'price' => 30.00,
                'duration' => 60,
                'category' => 'Transportation',
                'is_active' => true,
                'image' => 'https://c8.alamy.com/comp/BBWYEG/shuttle-bus-on-the-tarmac-of-the-airport-berlin-tegel-germany-europe-BBWYEG.jpg',
            ],
            [
                'name' => 'Laundry & Dry Cleaning',
                'description' => 'Professional laundry and dry cleaning service with quick turnaround.',
                'price' => 25.00,
                'duration' => 90,
                'category' => 'Housekeeping & Laundry',
                'is_active' => true,
                'image' => 'https://media.istockphoto.com/id/542303516/photo/worker-laundry-ironed-clothes-iron-dry.jpg?s=612x612&w=0&k=20&c=lcI-9Caxcqd-ZI9vwAPmHAl76cB_T205hB8tFr2Iclg=',
            ],
            [
                'name' => 'Gym Access',
                'description' => 'Full access to our modern fitness center equipped with cardio and weight machines.',
                'price' => 15.00,
                'duration' => 120,
                'category' => 'Wellness & Spa',
                'is_active' => true,
                'image' => 'https://media.istockphoto.com/id/2075354173/photo/fitness-couple-is-doing-kettlebell-twist-in-a-gym-togehter.jpg?s=612x612&w=0&k=20&c=lfs1V1d0YB33tn72myi6FElJnylPJYYM9lW5ZhlnYqY=',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['name' => $service['name']], $service);
        }
    }
}
