<?php

namespace Database\Seeders;

use App\Models\TORRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TORRequestSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with sample TOR requests.
     */
    public function run(): void
    {
        // Get a student user to associate with requests
        $student = User::where('role', 'student')->first();
        
        if (!$student) {
            $student = User::create([
                'name' => 'Sample Student',
                'email' => 'sample.student@example.com',
                'password' => bcrypt('password'),
                'role' => 'student',
                'student_id' => 'STU003',
            ]);
        }

        // Create 5 sample TOR requests with different statuses
        TORRequest::create([
            'user_id' => $student->id,
            'full_name' => 'John Michael Santos',
            'birthplace' => 'Manila, Philippines',
            'birthdate' => '2000-05-15',
            'student_id' => 'STU001',
            'course' => 'Bachelor of Science in Computer Science',
            'degree' => 'Bachelor',
            'purpose' => 'Employment',
            'status' => 'pending',
            'remarks' => 'Urgent request for job application',
        ]);

        TORRequest::create([
            'user_id' => $student->id,
            'full_name' => 'Maria Cruz Reyes',
            'birthplace' => 'Cebu, Philippines',
            'birthdate' => '2001-08-22',
            'student_id' => 'STU004',
            'course' => 'Bachelor of Science in Information Technology',
            'degree' => 'Bachelor',
            'purpose' => 'Graduate School Application',
            'status' => 'pending',
            'remarks' => 'For MA program enrollment',
        ]);

        TORRequest::create([
            'user_id' => $student->id,
            'full_name' => 'Antonio Dela Cruz',
            'birthplace' => 'Davao, Philippines',
            'birthdate' => '1999-12-10',
            'student_id' => 'STU005',
            'course' => 'Bachelor of Science in Information Systems',
            'degree' => 'Bachelor',
            'purpose' => 'Professional License Exam',
            'status' => 'approved',
            'remarks' => 'Ready for pickup - Available Monday-Friday',
        ]);

        TORRequest::create([
            'user_id' => $student->id,
            'full_name' => 'Rosa Fernandez Garcia',
            'birthplace' => 'Quezon City, Philippines',
            'birthdate' => '2000-03-18',
            'student_id' => 'STU006',
            'course' => 'Bachelor of Science in Civil Engineering',
            'degree' => 'Bachelor',
            'purpose' => 'Job Application',
            'status' => 'approved',
            'remarks' => 'Document ready for release',
        ]);

        TORRequest::create([
            'user_id' => $student->id,
            'full_name' => 'Pedro Villegas Mendoza',
            'birthplace' => 'Iloilo, Philippines',
            'birthdate' => '2002-07-25',
            'student_id' => 'STU007',
            'course' => 'Bachelor of Science in Education',
            'degree' => 'Bachelor',
            'purpose' => 'Employment',
            'status' => 'rejected',
            'remarks' => 'Request rejected - missing documents',
        ]);
    }
}