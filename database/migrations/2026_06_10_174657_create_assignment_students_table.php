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
        Schema::create('assignment_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_assignment_id')->constrained('exercise_assignments')->onDelete('cascade');
            $table->integer('student_id'); // user_id from students
            $table->string('status')->default('pending'); // pending, submitted, graded
            $table->text('submission_file')->nullable();
            $table->integer('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_students');
    }
};
