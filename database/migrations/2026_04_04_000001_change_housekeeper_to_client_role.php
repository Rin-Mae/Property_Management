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
    }
};