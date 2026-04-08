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
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->after('room_id')->comment('The user who made the reservation');
            $table->integer('number_of_guests')->nullable()->after('check_out')->comment('Number of guests for this reservation');
            $table->dateTime('payment_date')->nullable()->after('payment_method')->comment('Date when payment was submitted');
            $table->string('payment_proof')->nullable()->after('payment_date')->comment('Path to payment proof file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeignKey(['user_id']);
            $table->dropColumn(['user_id', 'number_of_guests', 'payment_date', 'payment_proof']);
        });
    }
};
