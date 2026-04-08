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

        // Get available images from standard and deluxe folders
        $standardImages = [
            'images/standard/23bc1436-2b36-4a73-b776-7d658ee984bf.jpg',
            'images/standard/24fede45-dfb0-4372-8c4e-c68edc231bef.jpg',
            'images/standard/6d7cb00f-186c-4dbb-b42a-fe043a6f84f5.jpg',
            'images/standard/72197283-9663-423c-98c9-56a6961d418a.jpg',
            'images/standard/82c4c987-6e96-44a9-bd8b-679b5f83f9fb.jpg',
            'images/standard/b3f9b1f7-93aa-4b47-9435-5326bfb47c2b.jpg',
            'images/standard/bfc2355a-1658-44f6-a08d-1ded6c582330.jpg',
            'images/standard/c50593e7-6fc5-41e5-8bb1-a5491c56ff2c.jpg',
            'images/standard/d0efb4ce-6d49-4564-b117-63eedab8673c.jpg',
            'images/standard/dd15b991-c45c-47e8-817a-dbce403c68b6.jpg',
        ];

        $deluxeImages = [
            'images/deluxe/11505e5b-1a25-4835-b82b-d261b363f509.jpg',
            'images/deluxe/6498e6e3-0208-467c-b4de-5452971a4ea4.jpg',
            'images/deluxe/7a735998-0e28-46c7-b81d-de934f8b120b.jpg',
            'images/deluxe/a345c818-7668-4e1f-92a3-73f040af59a4.jpg',
            'images/deluxe/a8713f50-17c3-4153-9a25-0f4e9573a9ff.jpg',
            'images/deluxe/aaf41fd7-aef7-40ea-890f-2441487ac106.jpg',
            'images/deluxe/c0375fa0-bc66-4d6c-8c67-2c907d3a446d.jpg',
            'images/deluxe/d757526f-71f6-46a8-b206-1d18763ff432.jpg',
            'images/deluxe/f82a9955-34fe-49eb-a6b1-f6de5a028fbc.jpg',
            'images/deluxe/fd2317bf-0ffc-4650-85a1-64b985eddb0f.jpg',
        ];

        // Create Standard Rooms (101-110) with images
        for ($i = 1; $i <= 10; $i++) {
            Room::create([
                'name' => 'Standard Room ' . (100 + $i),
                'room_number' => (string)(100 + $i),
                'type_id' => $standardType->id,
                'capacity' => 2,
                'price' => 2500.00,
                'description' => 'Comfortable room with essential amenities including a queen bed, ensuite bathroom, and modern furnishings.',
                'status' => 'available',
                'image_url' => $standardImages[($i - 1) % count($standardImages)],
            ]);
        }

        // Create Deluxe Rooms (201-215) with images
        for ($i = 1; $i <= 15; $i++) {
            Room::create([
                'name' => 'Deluxe Room ' . (200 + $i),
                'room_number' => (string)(200 + $i),
                'type_id' => $deluxeType->id,
                'capacity' => 2,
                'price' => 3500.00,
                'description' => 'Spacious room with premium furnishings, modern conveniences, and elegant decor. Features a king bed and luxury bathroom.',
                'status' => 'available',
                'image_url' => $deluxeImages[($i - 1) % count($deluxeImages)],
            ]);
        }
    }
}
