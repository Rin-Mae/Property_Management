<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Type::create([
            'name' => 'Standard Room',
            'description' => 'Comfortable room with essential amenities. Perfect for single travelers or couples.',
        ]);

        Type::create([
            'name' => 'Deluxe Room',
            'description' => 'Spacious room with premium furnishings and modern conveniences for added comfort.',
        ]);
    }
}