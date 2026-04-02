<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get room types
        $standardType = Type::where('name', 'Standard Room')->first();
        $deluxeType = Type::where('name', 'Deluxe Room')->first();

        // Standard Rooms (101-110)
        for ($i = 1; $i <= 10; $i++) {
            Room::create([
                'name' => 'Standard Room ' . (100 + $i),
                'room_number' => '10' . $i,
                'type_id' => $standardType->id,
                'capacity' => 2,
                'price' => 2500.00,
                'description' => 'Comfortable room with essential amenities including a queen bed, ensuite bathroom, and modern furnishings.',
                'status' => 'available',
                'image_url' => 'https://via.placeholder.com/400x300?text=Standard+Room',
            ]);
        }

        // Deluxe Rooms (201-215)
        for ($i = 1; $i <= 15; $i++) {
            Room::create([
                'name' => 'Deluxe Room ' . (200 + $i),
                'room_number' => '20' . $i,
                'type_id' => $deluxeType->id,
                'capacity' => 2,
                'price' => 3500.00,
                'description' => 'Spacious room with premium furnishings, modern conveniences, and elegant decor. Features a king bed and luxury bathroom.',
                'status' => 'available',
                'image_url' => 'https://via.placeholder.com/400x300?text=Deluxe+Room',
            ]);
        }
    }
}
