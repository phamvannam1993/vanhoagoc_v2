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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 512);
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->integer('class_id');
            $table->timestamps();
        });

        Schema::create('event_practice', function (Blueprint $table) {
            $table->id();
            $table->integer('practice_id');
            $table->integer('event_id');
            $table->timestamps();
        });

        Schema::create('event_points', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('event_id');
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
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_practice');
        Schema::dropIfExists('event_points');
    }
};
