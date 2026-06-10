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
        Schema::create('exercise_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_item_id')->constrained('exercise_items')->onDelete('cascade');
            $table->string('kind'); // chon, sx, noi
            $table->text('tieu_de'); // question title
            $table->json('options')->nullable(); // for chon, sx types
            $table->json('cot_a')->nullable(); // for noi type
            $table->json('cot_b')->nullable(); // for noi type
            $table->string('dap_an_dung')->nullable(); // correct answer (A, B, C, D, E for chon)
            $table->string('muc_do')->nullable(); // Dễ, Trung bình, Khó
            $table->text('trich_dan_dap_an')->nullable(); // explanation
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_questions');
    }
};
