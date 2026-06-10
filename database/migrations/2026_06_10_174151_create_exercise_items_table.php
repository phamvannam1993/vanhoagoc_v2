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
        Schema::create('exercise_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('exercises')->onDelete('cascade');
            $table->string('name')->default('Bài tập'); // "Bài tập 1", "Bài tập 2"
            $table->integer('order')->default(0);
            $table->string('level')->nullable(); // Dễ, Trung bình, Khó
            $table->json('question_mix')->nullable(); // { Dễ: 8, Trung bình: 0, Khó: 0 }
            $table->integer('total_questions')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_items');
    }
};
