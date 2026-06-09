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
        Schema::create('lesson_drafts', function (Blueprint $table) {
            $table->id();
            $table->integer('practice_id')->index();
            $table->integer('app_id')->nullable();
            $table->integer('book_id')->nullable();
            $table->integer('week_id')->nullable();
            $table->longText('lesson_doc')->nullable();
            $table->longText('lesson_noi')->nullable();
            $table->longText('lesson_video')->nullable();
            $table->enum('type', ['text', 'audio', 'video', 'text_audio', 'text_video', 'audio_video', 'all'])->default('all');
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('practice_id')->references('id')->on('practice')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_drafts');
    }
};
