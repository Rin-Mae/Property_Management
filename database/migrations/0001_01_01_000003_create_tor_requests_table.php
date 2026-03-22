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
        Schema::create('tor_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('birthplace');
            $table->date('birthdate');
            $table->string('student_id')->unique();
            $table->string('course');
            $table->string('degree')->nullable();
            $table->year('year_of_graduation')->nullable();
            $table->string('purpose')->nullable();
            $table->integer('number_of_copies')->default(1);
            $table->enum('status', ['pending', 'processing', 'approved', 'rejected', 'ready_for_pickup'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tor_requests');
    }
};
