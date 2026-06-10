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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_draft_id')->constrained('lesson_drafts')->onDelete('cascade');
            $table->string('title')->default('Bài tập luyện tập');
            $table->text('description')->nullable();
            $table->integer('total_questions')->default(0);
            $table->json('question_mix')->nullable(); // { Dễ: 0, Trung bình: 0, Khó: 0 }
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
