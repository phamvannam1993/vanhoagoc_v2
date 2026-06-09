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
        Schema::create('realtime_events', function (Blueprint $table) {
            $table->id();
            $table->string('source_id', 256);
            $table->string('name', 256)->nullable();
            $table->timestamps();
        });

        Schema::create('realtime_event_user', function (Blueprint $table) {
            $table->id();
            $table->integer('realtime_event_id');
            $table->integer('user_id');
            $table->integer('status')->comment('1: inviting | 2: accept');
            $table->timestamps();
        });

        Schema::create('realtime_event_points', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('realtime_event_id');
            $table->integer('star_count');
            $table->integer('star_total');
            $table->integer('time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realtime_events');
        Schema::dropIfExists('realtime_event_user');
        Schema::dropIfExists('realtime_event_points');
    }
};
