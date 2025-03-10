<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Config extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'roundsBetween_Bye', 'roundsBetween', 'club', 'personal', 'bye', 'other', 'presence', 'start', 'step', 'name', 'season', 'admin', 'absencemax', 'announcement', 'seasonpart', 'maximale_aanmeldtijd',
    ];
    public static function RoundsBetween($bye)
    {
        if ($bye == 1) {
            $value = Config::select('roundsbetween_bye')->first();

            // Maybe you want to add more rounds between Bye-games.
            return $value->Roundsbetween_bye;
        } else {
            $value = Config::select('roundsbetween')->first();

            return $value->Roundsbetween;
        }
    }

    public static function Scoring($result)
    {
        // Absence club (afwezig club)
        if ($result == "Club") {
            $value = Config::select('club')->first();

            return $value->Club;
        }
        // Absence due to personal reasons/sickness/force majeure (0.25)
        elseif ($result == "Personal") {
            $value = Config::select('personal')->first();

            return $value->Personal;
        } elseif ($result == "Bye") {
            $value = Config::select("bye")->first();

            return $value->Bye;
        } elseif ($result == "Presence") {
            $value = Config::select('presence')->first();

            return $value->Presence;
        }
        // Absence with message (afwezig met bericht) (0.3333) --> max 5 times per season part
        else {
            $value = Config::select('other')->first();

            return $value->Other;
        }
    }
    public static function SeasonPart()
    {
        if(App::isLocal()){
            return Config::select('SeasonPart')->first()->SeasonPart;
        }
        $value = Config::select('seasonpart')->first();

        return $value->seasonpart;
    }
    public static function AbsenceMax()
    {
        if(App::isLocal()){
            return Config::select('AbsenceMax')->first()->AbsenceMax;
        }
        $value = Config::select('absencemax')->first();

        return $value->absencemax;
    }
    public static function InitRanking($key)
    {
        if ($key == "start") {
            $value = Config::select('start')->first();

            return $value->Start;
        } else {
            $value = Config::select('step')->first();

            return $value->Step;
        }
    }
    public static function CompetitionName()
    {
        $value = Config::select('name')->first();
        if ($value == NULL) {
            return "Keizersysteem voor een club";
        }
        return $value->Name;
    }

    public static function CompetitionSeason()
    {
        $value = Config::select('season')->first();

        return $value->Season;
    }

    public static function MaxAanmeldTijd()
    {
        $value = Config::select('maximale_aanmeldtijd')->first();

        return $value->maximale_aanmeldtijd;
    }
}
