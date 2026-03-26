<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with admin and student users.
     */
    public function run(): void
    {
        // Create Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'middle_name' => '',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'student_id' => null,
            ]
        );

        // Create Student User
        User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'first_name' => 'Student',
                'middle_name' => '',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'role' => 'student',
                'student_id' => 'STU001',
            ]
        );

        // Create Test User (Student)
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'Test',
                'middle_name' => '',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'role' => 'student',
                'student_id' => 'STU002',
            ]
        );
    }
}