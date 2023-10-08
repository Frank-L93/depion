<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->boolean('notifications')->default(false);
            $table->boolean('NotifyByMail')->default(false);
            $table->boolean('NotifyByDB')->default(false);
            $table->boolean('NotifyByRSS')->default(false);
            $table->integer('games')->default('0');
            $table->integer('ranking')->default('0');
            $table->string('layout')->default('app');
            $table->string('language')->default('nl');
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
        Schema::dropIfExists('settings');
    }
}
