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
            $table->integer('is_cached_rank')->default(0)->after('end_datetime')->comment('0: not cached | 1: cached');
        });

        Schema::table('event_points', function (Blueprint $table) {
            $table->integer('rank')->nullable()->after('time')->comment('save only when the event is over');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_cached_rank');
        });
        Schema::table('event_points', function (Blueprint $table) {
            $table->dropColumn('rank');
        });
    }
};
