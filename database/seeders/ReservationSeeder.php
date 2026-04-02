<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all rooms
        $rooms = Room::all();

        if ($rooms->isEmpty()) {
            $this->command->info('No rooms found. Please seed rooms first.');
            return;
        }

        // Guest names
        $guestNames = [
            ['name' => 'Juan Dela Cruz', 'email' => 'juan@example.com', 'phone' => '09171234567'],
            ['name' => 'Maria Santos', 'email' => 'maria@example.com', 'phone' => '09172345678'],
            ['name' => 'Roberto Torres', 'email' => 'roberto@example.com', 'phone' => '09173456789'],
            ['name' => 'Annie Reyes', 'email' => 'annie@example.com', 'phone' => '09174567890'],
            ['name' => 'Michael Lim', 'email' => 'michael@example.com', 'phone' => '09175678901'],
            ['name' => 'Jessica Wong', 'email' => 'jessica@example.com', 'phone' => '09176789012'],
            ['name' => 'Carlos Martinez', 'email' => 'carlos@example.com', 'phone' => '09177890123'],
            ['name' => 'Sophia Lee', 'email' => 'sophia@example.com', 'phone' => '09178901234'],
            ['name' => 'Daniel Garcia', 'email' => 'daniel@example.com', 'phone' => '09179012345'],
            ['name' => 'Maria Isabella', 'email' => 'isabella@example.com', 'phone' => '09170123456'],
            ['name' => 'Juan Manuel', 'email' => 'juanmanuel@example.com', 'phone' => '09171112223'],
            ['name' => 'Rosa Elena', 'email' => 'rosa@example.com', 'phone' => '09172223334'],
        ];

        // Create reservations
        $statuses = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];
        $reservationIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $bookingId = 123;

        foreach ($reservationIds as $i => $resId) {
            // Create or get guest
            $guestData = $guestNames[$i % count($guestNames)];
            $guest = Guest::firstOrCreate(
                ['email' => $guestData['email']],
                [
                    'name' => $guestData['name'],
                    'phone' => $guestData['phone'],
                ]
            );

            // Get random room
            $room = $rooms->random();

            // Generate check-in and check-out dates
            $daysOffset = rand(-15, 30);
            $nights = rand(1, 7);
            $checkIn = Carbon::now()->addDays($daysOffset);
            $checkOut = (clone $checkIn)->addDays($nights);

            // Calculate total price
            $totalPrice = $room->price * $nights;

            // Create reservation
            Reservation::create([
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_price' => $totalPrice,
                'status' => $statuses[array_rand($statuses)],
                'notes' => 'Booking ID: PMS-' . str_pad($bookingId + $i, 5, '0', STR_PAD_LEFT),
            ]);
        }

        $this->command->info('Reservations seeded successfully!');
    }
}
