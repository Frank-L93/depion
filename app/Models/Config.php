<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'RoundsBetween_Bye', 'RoundsBetween', 'Club', 'Personal', 'Bye', 'Other', 'Presence', 'Start', 'Step', 'Name', 'Season', 'Admin', 'AbsenceMax', 'announcement', 'SeasonPart', 'maximale_aanmeldtijd',
    ];
    public static function RoundsBetween($bye)
    {
        if ($bye == 1) {
            $value = Config::select('RoundsBetween_Bye')->first();

            // Maybe you want to add more rounds between Bye-games.
            return $value->RoundsBetween_Bye;
        } else {
            $value = Config::select('RoundsBetween')->first();

            return $value->RoundsBetween;
        }
    }

    public static function Scoring($result)
    {
        // Absence club (afwezig club)
        if ($result == "Club") {
            $value = Config::select('Club')->first();

            return $value->Club;
        }
        // Absence due to personal reasons/sickness/force majeure (0.25)
        elseif ($result == "Personal") {
            $value = Config::select('Personal')->first();

            return $value->Personal;
        } elseif ($result == "Bye") {
            $value = Config::select("Bye")->first();

            return $value->Bye;
        } elseif ($result == "Presence") {
            $value = Config::select('Presence')->first();

            return $value->Presence;
        }
        // Absence with message (afwezig met bericht) (0.3333) --> max 5 times per season part
        else {
            $value = Config::select('Other')->first();

            return $value->Other;
        }
    }
    public static function SeasonPart()
    {
        $value = Config::select('SeasonPart')->first();

        return $value->SeasonPart;
    }
    public static function AbsenceMax()
    {
        $value = Config::select('AbsenceMax')->first();

        return $value->AbsenceMax;
    }
    public static function InitRanking($key)
    {
        if ($key == "start") {
            $value = Config::select('Start')->first();

            return $value->Start;
        } else {
            $value = Config::select('Step')->first();

            return $value->Step;
        }
    }
    public static function CompetitionName()
    {
        $value = Config::select('Name')->first();
        if ($value == NULL) {
            return "Keizersysteem voor een club";
        }
        return $value->Name;
    }

    public static function CompetitionSeason()
    {
        $value = Config::select('Season')->first();

        return $value->Season;
    }

    public static function MaxAanmeldTijd()
    {
        $value = Config::select('maximale_aanmeldtijd')->first();

        return $value->maximale_aanmeldtijd;
    }
}
