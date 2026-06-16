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
        Schema::table('assignment_students', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate assignments of same exercise to same student
            $table->unique(['exercise_assignment_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_students', function (Blueprint $table) {
            $table->dropUnique(['exercise_assignment_id', 'student_id']);
        });
    }
};
