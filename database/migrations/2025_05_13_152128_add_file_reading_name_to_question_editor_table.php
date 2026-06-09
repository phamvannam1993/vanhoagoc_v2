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
            $table->string('file_reading_name')->nullable()->after('reading_val');
            $table->string('file_background_name')->nullable()->after('file_reading_name');
            $table->text('reading_doc')->nullable()->after('file_background_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_editor', function (Blueprint $table) {
            $table->dropColumn('reading_val');
            $table->dropColumn('file_background_name');
            $table->dropColumn('reading_doc');
        });
    }
};
