<?php

namespace App\Actions;

use App\Models\Round;
use App\Models\Game;
use App\Models\Ranking;
use App\Models\Config;
use App\Http\Controllers\PushController;
use App\Http\Controllers\iOSNotificationsController;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MatchGames
{

    public function InitPairing($players, $round)
    {

        $playerstopair = $players;
        $players_to_pair_with_rating = array();
        $round = $round;
        $init = 0;

        // Check for player being an outliner in the bottom of the group
        foreach ($players as $player) {

            $player_rating = User::where('id', $player["id"])->first();
            array_push($players_to_pair_with_rating, ["player" => $player_rating->id, "rating" => $player_rating->rating]);
        }

        $last_player_to_pair = end($players_to_pair_with_rating);
        Log::info('Last Player to pair: '.$last_player_to_pair["player"]);
        $count_players_rated = count($players_to_pair_with_rating);
        Log::info('Count Player to pair: '.$count_players_rated);
        if($count_players_rated < 2){
            $count_players_rated = 2;
        }
        $non_last_player_to_pair = $players_to_pair_with_rating[$count_players_rated - 2];

        Log::info('Non Player to pair: '.$non_last_player_to_pair["player"]);
        if ($this->CheckIfOkToPairThisWay($non_last_player_to_pair["rating"], $last_player_to_pair["rating"]) == true) {
            Log::info('CheckIfOk: true');
            Log::info('Moving player:'.$players[$count_players_rated - 1]["id"]);
            $playerstopair = $this->moveElement($playerstopair, $count_players_rated - 1, $count_players_rated - rand(2, $count_players_rated - 1));
        } else {
        }



        //Check even or odd amount of players
        if (count($playerstopair) % 2 == 0) {
            $bye_necessary = 0;
        } else {
            $bye_necessary = 1;
        }
        $this->MatchGame($playerstopair, $round, $bye_necessary);
    }
    public function CheckIfOkToPairThisWay($b, $a)
    {
        $c = 0;
        $math = rand(0, 6);
        if ($a - $b > 500) {
            $c = ($a / 100 - $b / 100);
        }

        if ($math + $c > 10) {
            return false;
        } else {
            return true;
        }
    }
    public function moveElement(&$array, $a, $b)
    {
        $out = array_splice($array, $a, 1);
        array_splice($array, $b, 0, $out);

        return $array;
    }
    public function MatchGame($playerstopair, $round, $bye_necessary) // matches players
    {
        $matches = array();
        $matched = array();

        if ($bye_necessary == 1) {
            Bye:
            $bye = rand(0, count($playerstopair));
            if ($bye == count($playerstopair))
            {
                $bye = $bye - 1;
            }
            if (count($playerstopair) == 1) {
                $bye = 0;
            }
            if ($this->validBye($playerstopair[$bye], $round) == true) {
                $this->createGame($playerstopair[$bye]["id"], "Bye", $round);
                array_push($matched, $playerstopair[$bye]["id"]);
            } else {
                goto Bye;
            }
        }

        foreach ($playerstopair as $playertopair) {
            $player_being_paired = $playertopair["id"];
            if (in_array($player_being_paired, $matched) == false) {
                // Player is not yet paired, so find an available opponent:
                // Preferred opponent is next in $playerstopair, or the most closest one that is a valid opponent.
                // Valid opponent is a player that is not yet paired and a player who has not played against our player being paired in the last X rounds.
                // Also the color should be checked (if both are on -2 or +2 they can't play against eachother, if one is on -2 or + 2, and the other is on -1 or +1, it is possible, giving the player with the -2 / +2 the correct color)
                foreach ($playerstopair as $opponent) {
                    $opponent_being_paired = $opponent["id"];
                    if ($opponent == $playertopair || (in_array($opponent_being_paired, $matched) == true)) {
                        // Just skip this one as the opponent is the player being paired or the opponent being paired is already paired.
                    } else {
                        // Opponent is not paired yet, and opponent is not or player being paired
                        // Check if Opponent is valid for this Player.
                        if ($this->validOpponent($player_being_paired, $opponent_being_paired, $round, count($matched), count($playerstopair)) == true) {
                            // if Valid, create Game.
                            if ($this->createGame($player_being_paired, $opponent_being_paired, $round) == true) {
                                array_push($matched, $opponent_being_paired); // Add opponent to Matched Players
                                array_push($matched, $player_being_paired); // Add Player to Matched Players
                                break;
                            } else {
                                return redirect('/Admin')->with('Error', 'Fout bij aanmaken van partij!');
                            }
                        }
                    }
                }
            } else {
                // Not necessary to pair
            }
        }
        if (count($matched) == count($playerstopair)) {
            // Create notification

            return redirect('/Admin')->with('success', 'Partijen voor ronde ' . $round . ' aangemaakt');
        } else {
            return redirect('/Admin')->with('error', 'Partijen aangemaakt voor ronde ' . $round . ' maar foutief');
        }
    }

    public function validBye($player_one, $round)
    {
        $round_minimum = $round - Config::RoundsBetween(1);
        if ($round_minimum < 0) {
            $round_minimum = 1;
        }
        $game_exist = Game::where([['white', '=', $player_one], ['black', '=', 'Bye']])->whereBetween('round_id', [$round_minimum, $round])->get();
        if (($game_exist->isNotEmpty())) {
            return false;
        }
        // If valid return True;
        return true;
    }
    public function validOpponent($player_one, $player_two, $round, $amount_matched, $amount_to_match)
    {
        Log::info('We are trying to pair to: '.$player_one." against ".$player_two." with the following settings: ".$amount_matched."&".$amount_to_match);
        if ($amount_to_match - $amount_matched < 3) {
            return true;
        }
        // Not valid when:
        // Both players have -2 or +2
        $color_value = Ranking::where('user_id', $player_one)->first();
        $color = $color_value->color;

        $color_value_black = Ranking::where('user_id', $player_two)->first();
        $color_black = $color_value_black->color;
        if (($color == "-2" && $color_black == "-2") || ($color == "2" && $color_black == "2")) {
            return false;
        }
        // Not valid when:
        // Players have played against eachother in last X rounds.
        $round_minimum = $round - Config::RoundsBetween(2);

        if ($round_minimum < 0) {
            $round_minimum = 1;
        }
        $game_exist = Game::where([['white', '=', $player_one], ['black', '=', $player_two]])->whereBetween('round_id', [$round_minimum, $round])->get();
        $game_two_exist = Game::where([['white', '=', $player_two], ['black', '=', $player_one]])->whereBetween('round_id', [$round_minimum, $round])->get();
        if (($game_exist->isNotEmpty()) || ($game_two_exist->isNotEmpty())) {
            // Game exists, so return false.
            return false;
        }
        // Players have played in the current season part

        $season_part = Config::SeasonPart();

        // returns 10
        // The current paired round is our round variable
        // If the round variable <= 10 we are in the first part of the season
        // Else we are in the second part of the season
        if ($round <= $season_part) {
            // Check for games in the first part of the season
            $game_exist = Game::where([['white', '=', $player_one], ['black', '=', $player_two]])->where('round_id', '<=', $season_part)->get();

            $game_two_exist = Game::where([['white', '=', $player_two], ['black', '=', $player_one]])->where('round_id', '<=', $season_part)->get();

            if (($game_exist->isNotEmpty()) || ($game_two_exist->isNotEmpty())) {
                // Game exists, so return false.
                return false;
            }
        } else {
            // Check for games in the second part of the season
            $game_exist = Game::where([['white', '=', $player_one], ['black', '=', $player_two]])->where('round_id', '>', $season_part)->get();
            $game_two_exist = Game::where([['white', '=', $player_two], ['black', '=', $player_one]])->where('round_id', '>', $season_part)->get();

            if (($game_exist->isNotEmpty()) || ($game_two_exist->isNotEmpty())) {
                // Game exists, so return false.
                return false;
            }
        }

        // If valid return True;
        return true;
    }
    public function createGame($player_one, $player_two, $round)
    {
        // Check color preference.
        // if Player_One has -2 he needs white
        // if Player_One has +2 he needs black
        // if equal, random
        // if Player_Two has -2 he needs white
        // if Player_Two has +2 he needs black
        // if -1 +1, opposite colors
        if ($player_two == "Bye") {
            $game = new Game;
            $game->white = $player_one;
            $game->black = "Bye";
            $game->result = "1-0";
            $game->round_id = $round;
            $game->save();
            return true;
        }
        $color_value = Ranking::where('user_id', $player_one)->first();
        $color = $color_value->color;
        Log::info('We are trying to pair to: '.$player_one." against ".$player_two);


        $color_value_black = Ranking::where('user_id', $player_two)->first();
        $color_black = $color_value_black->color;
        if ($color == "-2") {
            $white = $player_one;
            $black = $player_two;
        } elseif ($color_black == "-2") {
            $white = $player_two;
            $black = $player_one;
        } elseif ($color == "2") {
            $white = $player_two;
            $black = $player_one;
        } elseif ($color_black == "2") {
            $white = $player_one;
            $black = $player_two;
        } elseif ($color == "-1" && $color_black == "1") {
            $white = $player_one;
            $black = $player_two;
        } elseif ($color == "1" && $color_black == "-1") {
            $white = $player_two;
            $black = $player_one;
        } else {
            $color_random = rand(0, 1);
            if ($color_random = 0) {
                $white = $player_one;
                $black = $player_two;
            } else {
                $white = $player_two;
                $black = $player_one;
            }
        }


        $game = new Game;
        $game->white = $white;
        $game->black = $black;
        $game->result = "0-0";
        $game->round_id = $round;
        $game->save();

        // Update ranking of player
        $white_ranking = Ranking::where('user_id', $white)->first();
        $white_ranking->color = $white_ranking->color + 1;
        $white_ranking->save();
        $black_ranking = Ranking::where('user_id', $black)->first();
        $black_ranking->color = $black_ranking->color - 1;
        $black_ranking->save();
        return true;
    }
}
