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
        Schema::table('question_editor', function (Blueprint $table) {
            $table->foreignId('exercise_item_id')->nullable()->constrained('exercise_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_editor', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['exercise_item_id']);
            $table->dropColumn('exercise_item_id');
        });
    }
};
