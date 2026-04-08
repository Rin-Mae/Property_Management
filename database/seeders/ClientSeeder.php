<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ClientSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Create some test clients
        $clients = [
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'middle_name' => 'Michael',
                'email' => 'john.smith@email.com',
                'contact_number' => '555-0101',
                'address' => '123 Main Street, New York, NY 10001',
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'middle_name' => 'Elizabeth',
                'email' => 'sarah.johnson@email.com',
                'contact_number' => '555-0102',
                'address' => '456 Oak Avenue, Los Angeles, CA 90001',
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Williams',
                'middle_name' => 'James',
                'email' => 'robert.williams@email.com',
                'contact_number' => '555-0103',
                'address' => '789 Pine Road, Chicago, IL 60601',
            ],
            [
                'first_name' => 'Emma',
                'last_name' => 'Brown',
                'middle_name' => 'Grace',
                'email' => 'emma.brown@email.com',
                'contact_number' => '555-0104',
                'address' => '321 Elm Street, Houston, TX 77001',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Miller',
                'middle_name' => 'Christopher',
                'email' => 'david.miller@email.com',
                'contact_number' => '555-0105',
                'address' => '654 Maple Drive, Phoenix, AZ 85001',
            ],
            [
                'first_name' => 'Jessica',
                'last_name' => 'Davis',
                'middle_name' => 'Marie',
                'email' => 'jessica.davis@email.com',
                'contact_number' => '555-0106',
                'address' => '987 Cedar Lane, Philadelphia, PA 19101',
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Garcia',
                'middle_name' => 'Antonio',
                'email' => 'michael.garcia@email.com',
                'contact_number' => '555-0107',
                'address' => '147 Birch Street, San Antonio, TX 78201',
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Martinez',
                'middle_name' => 'Rosa',
                'email' => 'lisa.martinez@email.com',
                'contact_number' => '555-0108',
                'address' => '258 Spruce Avenue, San Diego, CA 92101',
            ],
        ];

        foreach ($clients as $clientData) {
            Client::firstOrCreate(
                ['email' => $clientData['email']],
                $clientData
            );
        }

        // Generate additional random clients
        for ($i = 0; $i < 5; $i++) {
            Client::firstOrCreate(
                ['email' => $faker->unique()->email()],
                [
                    'first_name' => $faker->firstName(),
                    'last_name' => $faker->lastName(),
                    'middle_name' => $faker->firstName(),
                    'contact_number' => $faker->phoneNumber(),
                    'address' => $faker->address(),
                ]
            );
        }
    }
}
