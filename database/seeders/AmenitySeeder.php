<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Amenity::create([
            'name' => 'Free WiFi',
            'icon' => 'fa-wifi',
            'description' => 'High-speed wireless internet connection available throughout the room.',
        ]);

        Amenity::create([
            'name' => 'Air Conditioning',
            'icon' => 'fa-snowflake',
            'description' => 'Climate-controlled room with adjustable temperature.',
        ]);

        Amenity::create([
            'name' => 'Television',
            'icon' => 'fa-tv',
            'description' => 'Flat-screen TV with cable and satellite channels.',
        ]);

        Amenity::create([
            'name' => 'Mini Bar',
            'icon' => 'fa-wine-glass-alt',
            'description' => 'Complimentary refreshments and beverages available.',
        ]);

        Amenity::create([
            'name' => 'Private Bathroom',
            'icon' => 'fa-bath',
            'description' => 'Ensuite bathroom with shower, bathtub, and premium toiletries.',
        ]);

        Amenity::create([
            'name' => 'Work Desk',
            'icon' => 'fa-laptop',
            'description' => 'Dedicated workspace for business travelers.',
        ]);

        Amenity::create([
            'name' => 'Room Service',
            'icon' => 'fa-bell',
            'description' => '24/7 room service with dining options.',
        ]);

        Amenity::create([
            'name' => 'Safe',
            'icon' => 'fa-lock',
            'description' => 'In-room safe for secure storage of valuables.',
        ]);

        Amenity::create([
            'name' => 'Housekeeping',
            'icon' => 'fa-broom',
            'description' => 'Daily housekeeping and cleaning services.',
        ]);

        Amenity::create([
            'name' => 'Hair Dryer',
            'icon' => 'fa-wind',
            'description' => 'Premium hair dryer available in the bathroom.',
        ]);

        Amenity::create([
            'name' => 'Coffee Maker',
            'icon' => 'fa-mug-hot',
            'description' => 'In-room coffee and tea making facilities.',
        ]);

        Amenity::create([
            'name' => 'Gym Access',
            'icon' => 'fa-dumbbell',
            'description' => 'Access to our fully-equipped fitness center.',
        ]);

        Amenity::create([
            'name' => 'Swimming Pool',
            'icon' => 'fa-swimming-pool',
            'description' => 'Access to our heated swimming pool.',
        ]);

        Amenity::create([
            'name' => 'Spa Services',
            'icon' => 'fa-spa',
            'description' => 'Professional spa and wellness services available.',
        ]);

        Amenity::create([
            'name' => 'Parking',
            'icon' => 'fa-parking',
            'description' => 'Free valet and self-parking options available.',
        ]);
    }
}
