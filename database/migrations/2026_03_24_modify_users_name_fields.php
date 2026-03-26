<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new name fields
            $table->string('first_name')->nullable()->after('id');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
        });

        // Migrate existing data from 'name' to new fields
        $users = \DB::table('users')->get();
        foreach ($users as $user) {
            if ($user->name) {
                $nameParts = explode(' ', $user->name, 3);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[count($nameParts) - 1] ?? '';
                $middleName = isset($nameParts[1]) && count($nameParts) > 2 ? $nameParts[1] : '';

                \DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                    ]);
            }
        }

        // Drop the old name column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the name column
            $table->string('name')->after('id');
        });

        // Migrate data back
        $users = \DB::table('users')->get();
        foreach ($users as $user) {
            $fullName = trim("{$user->first_name} {$user->middle_name} {$user->last_name}");
            \DB::table('users')
                ->where('id', $user->id)
                ->update(['name' => $fullName]);
        }

        // Drop the new columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_name', 'last_name']);
        });
    }
};