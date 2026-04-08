<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the role enum constraint to include 'client'
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT role_enum");
            DB::statement("ALTER TABLE users ADD CONSTRAINT role_enum CHECK (role IN ('admin', 'user', 'client'))");
        }
        
        // Now we can update the roles
        // Delete housekeeper users
        DB::table('users')->where('role', 'housekeeper')->delete();
        
        // Change user role to client
        DB::table('users')->where('role', 'user')->update(['role' => 'client']);
        
        // Change student role to client if any exist
        DB::table('users')->where('role', 'student')->update(['role' => 'client']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert client back to user
        DB::table('users')->where('role', 'client')->update(['role' => 'user']);
        
        // Fix constraint back
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT role_enum");
            DB::statement("ALTER TABLE users ADD CONSTRAINT role_enum CHECK (role IN ('admin', 'housekeeper', 'user', 'client'))");
        }
    }
};
