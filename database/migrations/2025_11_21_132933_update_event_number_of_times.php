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
        Schema::table('events', function (Blueprint $table) {
            $table->integer('number_of_times')->nullable()->after('img_rule');
            $table->integer('duration')->nullable()->after('number_of_times');
        });

        Schema::table('event_points', function (Blueprint $table) {
            $table->integer('time_number')->default(1)->after('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('number_of_times');
            $table->dropColumn('duration');
        });

        Schema::table('event_points', function (Blueprint $table) {
            $table->dropColumn('time_number');
        });
    }
};
