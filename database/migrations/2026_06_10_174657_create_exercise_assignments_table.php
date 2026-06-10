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
        Schema::create('exercise_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_item_id')->constrained('exercise_items')->onDelete('cascade');
            $table->string('class_code'); // 2a, 3b, etc
            $table->date('due_date');
            $table->time('due_time')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('pending'); // pending, done
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_assignments');
    }
};
