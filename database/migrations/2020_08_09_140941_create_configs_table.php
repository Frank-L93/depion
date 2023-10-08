<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->float('RoundsBetween_Bye');
            $table->float('RoundsBetween');
            $table->float('Club');
            $table->float('Personal');
            $table->float('Bye');
            $table->float('Presence');
            $table->float('Other');
            $table->float('Start');
            $table->float('Step');
            $table->string('Name');
            $table->string('Season');
            $table->timestamps();
            $table->integer('Admin');
            $table->integer('EndSeason')->nullable();
            $table->text('announcement')->nullable();
            $table->integer('AbsenceMax');
            $table->integer('SeasonPart');
            $table->integer('presenceOrLoss')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configs');
    }
}
