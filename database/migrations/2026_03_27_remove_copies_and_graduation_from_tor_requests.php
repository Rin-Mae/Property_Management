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
        Schema::table('tor_requests', function (Blueprint $table) {
            $table->dropColumn(['number_of_copies', 'year_of_graduation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tor_requests', function (Blueprint $table) {
            $table->integer('number_of_copies')->default(1)->after('purpose');
            $table->year('year_of_graduation')->nullable()->after('degree');
        });
    }
};
