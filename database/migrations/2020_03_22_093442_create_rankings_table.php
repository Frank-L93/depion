<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRankingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->double('score');
            $table->integer('value');
            $table->integer('LastValue')->nullable();
            $table->integer('LastValue2')->nullable();
            $table->integer('color')->nullable();
            $table->integer('amount')->nullable();
            $table->integer('ratop')->nullable();
            $table->double('TPR')->nullable();
            $table->double('gamescore')->default(0);
            $table->integer('FirstValue')->nullable();
            $table->json('SeasonParts')->nullable();
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
        Schema::dropIfExists('ranking');
    }
}
