<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'app_id')) {
                $table->unsignedBigInteger('app_id')->nullable()->after('name');
            }
            if (!Schema::hasColumn('events', 'create_id')) {
                $table->unsignedBigInteger('create_id')->nullable()->after('app_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'create_id')) {
                $table->dropColumn('create_id');
            }
        });
    }
};
