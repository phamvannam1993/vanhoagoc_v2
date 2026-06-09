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
        Schema::create('user_rank_practices', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('class_id');
            $table->integer('practice_id');
            $table->integer('total_point');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rank_practices');
    }
};
