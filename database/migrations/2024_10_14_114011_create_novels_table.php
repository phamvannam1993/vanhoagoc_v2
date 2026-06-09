<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNovelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('novels', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id");
            $table->integer("app_id");
            $table->string("title");
            $table->string("is_visible")->nullable();
            $table->string("img")->nullable();
            $table->string("video_url")->nullable();
            $table->string("audio_val")->nullable();
            $table->string("audio_img_val")->nullable();
            $table->string("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('novels');
    }
}
