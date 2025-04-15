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
        'roundsbetween_bye', 'roundsbetween', 'club', 'personal', 'bye', 'other', 'presence', 'start', 'step', 'name', 'season', 'admin', 'absencemax', 'announcement', 'seasonpart', 'maximale_aanmeldtijd',
    ];

    public static function RoundsBetween($bye)
    {
        if ($bye == 1) {
            $value = Config::select('roundsbetween_bye')->first();

            // Maybe you want to add more rounds between Bye-games.
            return $value->roundsbetween_bye;
        } else {
            $value = Config::select('roundsbetween')->first();

            return $value->roundsbetween;
        }
    }

    public static function Scoring($result)
    {
        // Absence club (afwezig club)
        if ($result == 'Club') {
            $value = Config::select('club')->first();

            return $value->club;
        }
        // Absence due to personal reasons/sickness/force majeure (0.25)
        elseif ($result == 'Personal') {
            $value = Config::select('personal')->first();

            return $value->personal;
        } elseif ($result == 'Bye') {
            $value = Config::select('bye')->first();

            return $value->bye;
        } elseif ($result == 'Presence') {
            $value = Config::select('presence')->first();

            return $value->presence;
        }
        // Absence with message (afwezig met bericht) (0.3333) --> max 5 times per season part
        else {
            $value = Config::select('other')->first();

            return $value->other;
        }
    }

    public static function SeasonPart()
    {

        $value = Config::select('seasonpart')->first();

        return $value->seasonpart;
    }

    public static function AbsenceMax()
    {

        $value = Config::select('absencemax')->first();

        return $value->absencemax;
    }

    public static function InitRanking($key)
    {
        if ($key == 'start') {
            $value = Config::select('start')->first();

            return $value->start;
        } else {
            $value = Config::select('step')->first();

            return $value->step;
        }
    }

    public static function CompetitionName()
    {
        $value = Config::select('name')->first();
        if ($value == null) {
            return 'Keizersysteem voor een club';
        }

        return $value->name;
    }

    public static function CompetitionSeason()
    {
        $value = Config::select('season')->first();

        return $value->season;
    }

    public static function MaxAanmeldTijd()
    {
        $value = Config::select('maximale_aanmeldtijd')->first();

        return $value->maximale_aanmeldtijd;
    }

    public static function Summer()
    {
        $value = Config::select('summer')->first();
        if ($value->summer == 1) {
            return true;
        } else {
            return false;
        }

    }
}
