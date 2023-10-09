<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        DB::table('users')->insert([
            "name" => "Frank Lambregts",
            "password" => Hash::make('MagiStraal93!'),
            "api_token" => "z8TdkiFKGz",
            "email" => "frank@franklambregts",
            "knsb_id" => 8090687,
            "rechten" => 2,
            "remember_token" => "Ebkmu3Rm1Ka2QNYTyPzryIcDbbL0jgb3WL8uh98P36zOkcdJz8DfdtfB8mJ2",
            "rating" => 1876,
            "beschikbaar" => 1,
            "firsttimelogin" => 0,
            "settings" => "{\"rss\": \"1\", \"games\": \"0\", \"layout\": \"app\", \"ranking\": \"1\", \"language\": \"nl\", \"notifications\": \"2\"}"
        ]);
    }
}
