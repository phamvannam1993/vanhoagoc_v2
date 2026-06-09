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
            $table->dropColumn([
                'answer_1_type',
                'answer_1_val',
                'answer_2_type',
                'answer_2_val',
                'answer_3_type',
                'answer_3_val',
                'answer_4_type',
                'answer_4_val',
                'answer_5_type',
                'answer_5_val',
                'answer_6_type',
                'answer_6_val',
                'answer_7_type',
                'answer_7_val',
                'answer_8_type',
                'answer_8_val',
                'answer_9_type',
                'answer_9_val',
                'answer_10_type',
                'answer_10_val',
            ]);

            $table->text('answers')->nullable();

            $table->dropColumn([
                'answer_connect_1_type',
                'answer_connect_1_val',
                'answer_connect_2_type',
                'answer_connect_2_val',
                'answer_connect_3_type',
                'answer_connect_3_val',
                'answer_connect_4_type',
                'answer_connect_4_val',
                'answer_connect_5_type',
                'answer_connect_5_val',
                'answer_connect_6_type',
                'answer_connect_6_val',
                'answer_connect_7_type',
                'answer_connect_7_val',
                'answer_connect_8_type',
                'answer_connect_8_val',
                'answer_connect_9_type',
                'answer_connect_9_val',
                'answer_connect_10_type',
                'answer_connect_10_val',
            ]);

            $table->text('answer_connects')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
