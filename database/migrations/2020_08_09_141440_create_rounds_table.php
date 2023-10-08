<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->id();
            $table->integer('round');
            $table->datetime('date');
            $table->integer('processed')->nullable();
            $table->enum('paired', [0, 1])->default(0);
            $table->integer('published')->default(0);
            $table->integer('ranking')->default(0)
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
        Schema::dropIfExists('rounds');
    }
}
