<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_editor', function (Blueprint $table) {
            $table->unsignedBigInteger('competency_id')->nullable()->after('ndgd');
            $table->unsignedBigInteger('competency_component_id')->nullable()->after('competency_id');
            $table->text('pcnl_detail')->nullable()->after('competency_component_id');
            $table->unsignedBigInteger('educational_content_id')->nullable()->after('pcnl_detail');
            $table->text('ndgd_requirement')->nullable()->after('educational_content_id');
        });
    }

    public function down(): void
    {
        Schema::table('question_editor', function (Blueprint $table) {
            $table->dropColumn(['competency_id', 'competency_component_id', 'pcnl_detail', 'educational_content_id', 'ndgd_requirement']);
        });
    }
};
