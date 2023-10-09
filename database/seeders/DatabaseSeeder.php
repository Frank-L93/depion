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
        DB::table('configs')->insert([
            'RoundsBetween_Bye' => 5,
            'RoundsBetween' => 5,
            'Club' => 5,
            'Personal' => 5,
            'Bye' => 5,
            'Presence' => 5,
            'Other' => 5,
            'Start' => 50,
            'Step' => 1,
            'Name' => 'De Pion',
            'Season' => '2023-2024',
            'Admin' => 1,
            'EndSeason' => 0,
            'announcement' => 'Nieuw seizoen wordt opgestart',
            'AbsenceMax' => 3,
            'SeasonPart' => 13,
            'presenceOrLoss' => 1,
        ]);

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
