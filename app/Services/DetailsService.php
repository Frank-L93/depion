<?php

namespace App\Services;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Game;
use App\Models\Ranking;
use App\Models\Round;
use App\Models\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DetailsService
{
    public function Games($player)
    {
        $currentRound = Round::where('processed', '=', NULL)->first();
        if ($currentRound == null) {
            $currentRound = Round::where('processed', '=', 1)->latest('updated_at')->first();
        }
        $Games = Game::where([['white', '=', $player], ['round_id', '<=', $currentRound->round]])->orWhere([['black', '=', $player], ['round_id', '<=', $currentRound->round]])->get();
        return $Games;
    }

    public function PlayerName($player)
    {
        if ($player == "Bye") {
            return "Bye";
        } elseif ($player == "Other" || $player == "Club" || $player == "Personal") {
            return "-";
        } else {
            $player = intval($player);
            $PlayerName = User::select('name')->where('id', '=', $player)->first();
            return $PlayerName->name;
        }
    }
    public function LastRound()
    {

        $lastRound = Round::where('processed', '=', 1)->latest('updated_at')->first();
        if ($lastRound == NULL) {
            $round = 1;
        } else {
            $round = $lastRound->round;
        }
        return $round;
    }

    public function CurrentRound()
    {

        $currentRound = Round::where('ranking', '=', 1)->orderBy('updated_at', 'DESC')->first();
        if ($currentRound == null) {
            return "Niet";
        }

        $round = $currentRound->round;
        return $round;
    }

    public function SummerScore($player)
    {

        // Get the Game;
        $games = Game::where([['white', '=', $player], ['round_id', '<=', Config::SeasonPart()]])->orWhere([['black', '=', $player], ['round_id', '<=', Config::SeasonPart()]])->get();

        // Get the current Round to determine if round game round is earlier.
        $round = $this->LastRound();

        // Set score to 0;
        $score = 0;
        foreach ($games as $game) {
            // Check for Absence Game;
            if ($game->white == $player && $game->result == "Afwezigheid") {
                // Set White Score to 0
                $white_score = 0;
                // Get the ranking for the values.
                $white_ranking = Ranking::where('user_id', $game->white)->first();

                // Club
                if ($game->black == "Club") {
                    if ($game->round_id < $round) {
                        $white_score = Config::Scoring("Club") * $white_ranking->LastValue2;

                    } else {
                        $white_score = Config::Scoring("Club") * $white_ranking->LastValue;
                    }
                } elseif ($game->black == "Personal") {
                    if ($game->round_id < $round) {
                        $white_score = Config::Scoring("Personal") * $white_ranking->LastValue2;
                    } else {
                        $white_score = Config::Scoring("Personal") * $white_ranking->LastValue;
                    }
                } else {
                    $absence_max = Config::AbsenceMax();
                    $amount_absence = Game::where([['white', '=', $game->white], ['result', '=', 'Afwezigheid'], ['black', '=', 'Other']])->count();

                    if ($amount_absence > $absence_max) {
                        $absentGames = Game::where([['white', '=', $game->white], ['result', '=', 'Afwezigheid'], ['black', '=', 'Other']])->get();

                        for ($i = 0; $i < $absence_max; $i++) {
                            if ($game->id == $absentGames[$i]->id) {
                                if ($game->round_id < $round) {
                                    $white_score += Config::Scoring("Other") * $white_ranking->LastValue;
                                } else {
                                    $white_score += Config::Scoring("Other") * $white_ranking->Value;
                                }
                            } else {
                            }
                        }
                    } else {
                        if ($game->round_id < $round) {
                            $white_score += Config::Scoring("Other") * $white_ranking->LastValue;

                        } else {
                            $white_score += Config::Scoring("Other") * $white_ranking->value;
                        }
                    }
                }

                $score = $score + $white_score;
            } else {
                if (($game->white == $player && $game->result != "Afwezigheid") || ($game->black == $player && $game->result != "Afwezigheid")) {
                    if(Str::contains($game->result, 'R')){
                        $result = explode("-", $game->result);
                        $white_result = $result[0];
                        if($white_result == "1"){
                            $white_result = "1R";
                        }
                        $black_result = $result[1];
                    }
                    else{
                    $result = explode("-", $game->result);
                    $white_result = $result[0];
                    $black_result = $result[1];
                    }

                    // Find white and black in the ranking
                    $white_ranking = Ranking::where('user_id', $game->white)->first();
                    if ($white_ranking == NULL) {

                        $white_ranking = new Ranking();

                        $white_ranking->LastValue = 0;

                        $white_ranking->LastValue2 = 0;
                    }
                    $black_ranking = Ranking::where('user_id', $game->black)->first();

                    // Defaults; //69.05
                    $white_score = 0;

                    if ($game->black == "Bye") {
                    } else {
                        $black_score = 0;
                    }
                    // Calculate the new score for white and black for this game or all games?
                    if ($game->black == "Bye") {
                        if ($game->round_id < $round) {

                            $white_score = Config::Scoring("Bye") * $white_ranking->LastValue2;
                        } else {
                            $white_score = Config::Scoring("Bye") * $white_ranking->LastValue;
                        }
                    } elseif ($white_result == "1") {
                        $black_absence = User::where('id', $game->black)->first();
                        if ($game->round_id < $round) {

                            if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5 || $game->round_id < 6)) {
                                $white_score += 1 * $black_ranking->FirstValue;
                            } else {
                                $white_score += 1 * $black_ranking->LastValue2;
                            }
                            //
                        } else {
                            if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {    $white_score += 1 * $black_ranking->FirstValue;
                            } else {
                                $white_score += 1 * $black_ranking->LastValue2;
                            } //58+60 = 118.05 + 59 = 178.1 + 28.5 = 205.65
                        }
                        $black_score += Config::Scoring("Presence");
                    } elseif ($white_result == "1R") {
                        $black_absence = User::where('id', $game->black)->first();
                        if ($game->round_id < $round) {
                            if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5 || $game->round_id < 6)) {
                                $white_score += 1 * $black_ranking->FirstValue;
                            } else {
                                $white_score += 1 * $black_ranking->LastValue2;
                            }  //

                        } else {
                            if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {
                                $white_score += 1 * $black_ranking->FirstValue;
                            } else {
                                $white_score += 1 * $black_ranking->LastValue;
                            } //58+60 = 118.05 + 59 = 178.1 + 28.5 = 205.65
                        }

                    } elseif ($white_result == 0.5) {   //69.05 += 0.5 * 69 = 69.05 + 34.5 = 103.60
                        $black_absence = User::where('id', $game->black)->first();
                        $white_absence = User::where('id', $game->white)->first();
                        if ($game->round_id < $round) {
                            if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5 || $game->round_id < 6)) {
                            $white_score += $white_result * $black_ranking->FirstValue;
                        } else {
                            $white_score += $white_result * $black_ranking->LastValue2;
                        }
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {
                            $black_score += $black_result * $white_ranking->FirstValue;
                        }
                        else{
                        $black_score += $black_result * $white_ranking->LastValue2;
                        }

                    } else {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5 )) {
                            $white_score += $white_result * $black_ranking->FirstValue;
                        } else {
                            $white_score += $white_result * $black_ranking->LastValue;
                        }
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                            $black_score += $black_result * $white_ranking->FirstValue;
                       }else{
                        $black_score += $black_result * $white_ranking->LastValue;
                       }
                    }

                    } elseif ($black_result == "1") {
                        $white_absence = User::where('id', $game->white)->first();
                        if ($game->round_id < $round) {
                            if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {

                                $black_score += 1 * $white_ranking->FirstValue;
                            } else {
                                $black_score += 1 * $white_ranking->LastValue2;
                            }

                        } else {
                            if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 )) {

                                $black_score += 1 * $white_ranking->FirstValue;
                            } else {
                                $black_score += 1 * $white_ranking->LastValue;
                            }
                        }

                        $white_score += Config::Scoring('Presence');
                    } elseif ($black_result == "1R") {
                        $white_absence = User::where('id', $game->white)->first();
                        if ($game->round_id < $round) {
                            if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {

                                $black_score += 1 * $white_ranking->FirstValue;
                            } else {
                                $black_score += 1 * $white_ranking->LastValue2;
                            }

                        } else {
                            if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                                $black_score += 1 * $white_ranking->FirstValue;
                            } else {
                                $black_score += 1 * $white_ranking->LastValue;
                            }
                        }


                    } else // No result yet?
                    {
                        break;
                    }

                    if ($game->white == $player) {
                        $score = $score + $white_score;
                    } elseif ($game->black == $player) {
                        $score = $score + $black_score;
                    }

                }
            }

        }

        return $score;
    }

    public function CurrentScore($player, $selectedRound, $gameID)
    {

        // Get the Game;
        $game = Game::where([['white', '=', $player], ['round_id', '=', $selectedRound], ['id', '=', $gameID]])->orWhere([['black', '=', $player], ['round_id', '=', $selectedRound], ['id', '=', $gameID]])->first();

        // Get the current Round to determine if round game round is earlier.
        $round = $this->LastRound();


        // Set score to 0;
        $score = 0;

        // Check for Absence Game;

        if ($game->white == $player && $game->result == "Afwezigheid") {
            // Set White Score to 0
            $white_score = 0;
            // Get the ranking for the values.
            $white_ranking = Ranking::where('user_id', $game->white)->first();

            // Club
            if ($game->black == "Club") {
                if ($game->round_id < $round) {
                    $white_score = Config::Scoring("Club") * $white_ranking->LastValue2;
                } else {
                    $white_score = Config::Scoring("Club") * $white_ranking->LastValue;
                }
            } elseif ($game->black == "Personal") {
                if ($game->round_id < $round) {
                    $white_score = Config::Scoring("Personal") * $white_ranking->LastValue2;
                } else {
                    $white_score = Config::Scoring("Personal") * $white_ranking->LastValue;
                }
            } else {
                $absence_max = Config::AbsenceMax();
                // Season parts
                $season_part = Config::SeasonPart();
                // filter this for the first X rounds.

                $amount_absence = Game::where([['white', '=', $game->white], ['result', '=', 'Afwezigheid'], ['black', '=', 'Other']])->count();


                if ($amount_absence > $absence_max) {
                    $absentGames = Game::where([['white', '=', $game->white], ['result', '=', 'Afwezigheid'], ['black', '=', 'Other']])->get();

                    for ($i = 0; $i < $absence_max; $i++) {
                        if ($game->id == $absentGames[$i]->id) {
                            if ($game->round_id < $round) {
                                $white_score = Config::Scoring("Other") * $white_ranking->LastValue2;
                            } else {
                                $white_score = Config::Scoring("Other") * $white_ranking->LastValue;
                            }
                        } else {
                        }
                    }
                } else {
                    if ($game->round_id < $round) {
                        $white_score = Config::Scoring("Other") * $white_ranking->LastValue2;
                    } else {
                        $white_score = Config::Scoring("Other") * $white_ranking->LastValue;
                    }
                }
            }
            $score = $white_score;
            return $score;
        } else {
            if (($game->white == $player && $game->result != "Afwezigheid") || ($game->black == $player && $game->result != "Afwezigheid")) {
                if(Str::contains($game->result, 'R')){
                    $result = explode("-", $game->result);
                    $white_result = $result[0];
                    if($white_result == "1"){
                        $white_result = "1R";
                    }
                    $black_result = $result[1];
                }
                else{
                $result = explode("-", $game->result);
                $white_result = $result[0];
                $black_result = $result[1];
                }

                // Find white and black in the ranking
                $white_ranking = Ranking::where('user_id', $game->white)->first();
                if($game->black != "Bye"){
                $black_ranking = Ranking::where('user_id', $game->black)->first();
                }

                $white_absence = User::where('id', $game->white)->first();
                $black_absence = User::where('id', $game->black)->first();

                // Defaults; //69.05
                $white_score = 0;
                if ($game->black == "Bye") {
                } else {
                    $black_score = 0;
                }
                // Calculate the new score for white and black for this game or all games?
                if ($game->black == "Bye") {
                    if ($game->round_id < $round) {
                        $white_score = Config::Scoring("Bye") * $white_ranking->LastValue2;
                    } else {
                        $white_score = Config::Scoring("Bye") * $white_ranking->LastValue;
                    }
                } elseif ($white_result == "1") {
                    if ($game->round_id < $round) {

                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5 || $game->round_id < 6)) {
                            $white_score += 1 * $black_ranking->FirstValue;
                        } else {
                            $white_score += 1 * $black_ranking->LastValue2;
                        }
                        //

                    } else {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {    $white_score += 1 * $black_ranking->FirstValue;
                        } else {
                            $white_score += 1 * $black_ranking->LastValue;
                        } //58+60 = 118.05 + 59 = 178.1 + 28.5 = 205.65
                    }


                    $black_score += Config::Scoring('Presence');
                } elseif ($white_result == "1R") {
                    if ($game->round_id < $round) {

                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5 || $game->round_id < 6)) {
                            $white_score += 1 * $black_ranking->FirstValue;
                        } else {
                            $white_score += 1 * $black_ranking->LastValue2;
                        }
                        //

                    } else {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {    $white_score += 1 * $black_ranking->FirstValue;
                        } else {
                            $white_score += 1 * $black_ranking->LastValue;
                        } //58+60 = 118.05 + 59 = 178.1 + 28.5 = 205.65
                    }

                } elseif ($white_result == 0.5) {   //69.05 += 0.5 * 69 = 69.05 + 34.5 = 103.60

                    if ($game->round_id < $round) {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5 || $game->round_id < 6)) {
                        $white_score += $white_result * $black_ranking->FirstValue;
                    } else {
                        $white_score += $white_result * $black_ranking->LastValue2;
                    }
                    if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {
                        $black_score += $black_result * $white_ranking->FirstValue;
                    }
                    else{
                    $black_score += $black_result * $white_ranking->LastValue2;
                    }
                } else {
                    if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {
                        $white_score += $white_result * $black_ranking->FirstValue;
                    } else {
                        $white_score += $white_result * $black_ranking->LastValue;
                    }
                    if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                        $black_score += $black_result * $white_ranking->FirstValue;
                   }else{
                    $black_score += $black_result * $white_ranking->LastValue;
                   }
                }


                } elseif ($black_result == "1") {

                    if ($game->round_id < $round) {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {

                            $black_score += 1 * $white_ranking->FirstValue;
                        } else {
                            $black_score += 1 * $white_ranking->LastValue2;
                        }
                    } else {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {

                            $black_score += 1 * $white_ranking->FirstValue;
                        } else {
                            $black_score += 1 * $white_ranking->LastValue;
                        }
                    }
                    $white_score += 5;
                } elseif ($black_result == "1R") {

                    if ($game->round_id < $round) {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {

                            $black_score += 1 * $white_ranking->FirstValue;
                        } else {
                            $black_score += 1 * $white_ranking->LastValue2;
                        }
                    } elseif ($game->round_id > $round) {
                    } else {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                            $black_score += 1 * $white_ranking->FirstValue;
                        } else {
                            $black_score += 1 * $white_ranking->LastValue;
                        }
                    }
                } else // No result yet?
                {
                    return "Fout bij berekenen, nog niet gespeeld";
                }

                if ($game->white == $player) {
                    $score = $white_score;
                    return $score;
                } elseif ($game->black == $player) {
                    $score = $black_score;
                    return $score;
                }
            }
        }
    }
}
