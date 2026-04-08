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
        // Update 'student' role to 'user' FIRST before changing the column
        DB::table('users')->where('role', 'student')->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            // Remove the old enum constraint and replace with new values
            // For MySQL, we need to modify the column
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY role VARCHAR(255)");
                DB::statement("ALTER TABLE users ADD CONSTRAINT role_enum CHECK (role IN ('admin', 'housekeeper', 'user', 'client'))");
            } else {
                // For SQLite and PostgreSQL
                $table->string('role')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update 'user' role back to 'student'
        DB::table('users')->where('role', 'user')->update(['role' => 'student']);
        
        Schema::table('users', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE users DROP CONSTRAINT role_enum");
                DB::statement("ALTER TABLE users MODIFY role ENUM('student', 'admin')");
            } else {
                $table->string('role')->change();
            }
        });
    }
};