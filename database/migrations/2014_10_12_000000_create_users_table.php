<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('password');
            $table->string('api_token')->nullable();
            $table->string('email')->unique();
            $table->integer('knsb_id');
            $table->integer('rechten')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->double('rating');
            $table->boolean('beschikbaar')->default(false);
            $table->boolean('firsttimelogin')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
