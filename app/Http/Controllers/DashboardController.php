<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\Round;
use App\Models\Presence;

class DashboardController extends Controller
{
    //
    public function GameDashBoard()
    {
        $rounds_to_search = $this->RoundDashBoard();
        if ($rounds_to_search == "Geen rondes meer!") {
            $game_data = "Geen";
        } else {
            foreach ($rounds_to_search as $round) {

                if ($round->published == 1) {

                    $game_data = Game::where([['round_id', '=', $round->round], ['result', '!=', 'Afwezigheid']])->get();
                } else {

                    $game_data = "Publicatie";
                }
            }
        }
        return $game_data;
    }

    public function AbsenceDashBoard()
    {
        $rounds_to_search = $this->RoundDashBoard();
        if ($rounds_to_search == "Geen rondes meer!") {
            $absence_data = "Geen";
        } else {
            foreach ($rounds_to_search as $round) {
                $absence_data = Game::where([['round_id', '=', $round->round], ['result', '=', 'Afwezigheid']])->get();
            }
        }
        return $absence_data;
    }
    public function RoundDashBoard()
    {
        $round_data = Round::where('date', '=', date('Y-m-d'))->orWhere('date', '>', date('Y-m-d'))->sortBy('date')->limit(1)->get();
        if ($round_data->isEmpty()) {
            $round_data = "Geen rondes meer!";
        }
        return $round_data;
    }

    public function PresenceDashBoard()
    {
        $rounds_to_search = $this->RoundDashBoard();
        if ($rounds_to_search == "Geen rondes meer!") {
            $presence_data = "Geen";
        } else {
            foreach ($rounds_to_search as $round) {
                $presence_data = Presence::where([['round', '=', $round->round], ['presence', '=', '1']])->get();
            }
        }
        return $presence_data;
    }

    public function GameStats()
    {
        return Game::where('result', '=', '0-1')->Where('black', '<>', 'Bye')->count();
    }
}
