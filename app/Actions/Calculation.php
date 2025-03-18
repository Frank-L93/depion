<?php

namespace App\Actions;

use App\Models\Ranking;
use App\Models\Game;
use App\Models\Config;
use App\Models\User;
use App\Models\Round;
use App\Actions\TPRHelper;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;

class Calculation
{
    /* The Calculcation Class has a couple of functions:
    ** Calculate, to initiate the calculation of new scores
    ** $rankings, $round, $games, $configs
    ** tpr Calculation (which will be helped by the tpr-Helper)
    ** Updating, which updates the RankingList after the scores have been calculated.
    */

    // We will give the function Calculate an inputvalue of a round, so we know which round is the latest round to be calculated.

    public function Calculate($round) // 3
    {

        // Make round an int (just in case it is passed on as a string)
        $round = $round * 1;

        // Get all rankings, as we need to work with these.
        $rankings = Ranking::all();

        Log::info('Starting calculation for round: '.$round);
        // Reset score as we do recalculating the score of previous rounds based on value in this round
        // Also reset amount, gamescore & ratop as we loop through all games again. So if we keep it the value it already has, it will duplicate itself!

        foreach ($rankings as $ranking) {
            $ranking->round = $round;
            $ranking->score = 0;
            $ranking->amount = 0;
            $ranking->gamescore = 0;
            $ranking->ratop = 0;
            Log::info('Resetting ranking for user: '.$ranking->user_id);
            $ranking->save();
        }

        // Get all games.
        $games = Game::where('round_id', '<=', $round)->get();
         // Round 2 // Win of Joshua Round 1 & Afwezig in ronde 2
         Log::info('Found '.count($games).' games to process.');
        foreach ($games as $game) {

            // decide the result for white and for black
            if ($game->result == "Afwezigheid") {
                $white_ranking = Ranking::where('user_id', $game->white)->first();

                // Check if player exist in Ranking, if not, add person to ranking.
                if ($white_ranking == NULL) {
                    $white_ranking = new Ranking;
                    $white_ranking->user_id = $game->white;
                    $white_ranking->score = 0;
                    $lowest_value = Ranking::select('value')->orderBy('value', 'asc')->limit(1)->first();
                    $white_ranking->value = $lowest_value->value - 1;
                    $white_ranking->firstvalue = $white_ranking->value;
                    $white_ranking->save();
                    Log::info('Created a new ranking for user: '.$white_ranking->user_id.' with value: '.$white_ranking->value);
                    // Set the new created Ranking as white_ranking again.
                    $white_ranking = Ranking::where('user_id', $game->white)->first();
                }
                $white_absence = User::where('id', $game->white)->first();
                // Defaults; //69.05
                if ($white_ranking->score == 0) {

                    if($white_absence->beschikbaar == 0){
                        $white_score = $white_ranking->firstvalue;
                    }else{
                    $white_score = $white_ranking->value;} // 39
                } else {
                    $white_score = $white_ranking->score;
                }
                Log::info('Processing absence for user: '.$game->white.' in round: '.$game->round_id.' with reason: '.$game->black);
                // We have multiple options for the Afwezigheid-results --> Black = Club, Black = Other or Black = Personal
                if ($game->black == "Club") {

                    if ($game->round_id < $round) {
                        $white_score += Config::Scoring("Club") * $white_ranking->lastvalue; //53*2/3
                    } elseif ($game->round_id > $round) {
                        // Do not consider games that are in future rounds (i.e. games due to absence)
                    } else {
                        $white_score += Config::Scoring("Club") * $white_ranking->value;
                    }
                } elseif ($game->black == "Personal") {
                    if ($game->round_id < $round) {
                        $white_score += Config::Scoring("Personal") * $white_ranking->lastvalue;
                    } elseif ($game->round_id > $round) {
                    } else {
                        $white_score += Config::Scoring("Personal") * $white_ranking->value;
                    }
                } else {
                    // Check if Absence Max is hit, otherwise let the player score.
                    $absence_max = Config::AbsenceMax();
                    $amount_absence = Game::where([['white', '=', $game->white], ['result', '=', 'Afwezigheid'], ['black', '=', 'Other']])->count();

                    if ($amount_absence > $absence_max) {
                        $absentGames = Game::where([['white', '=', $game->white], ['result', '=', 'Afwezigheid'], ['black', '=', 'Other']])->get();

                        for ($i = 0; $i < $absence_max; $i++) {
                            if ($game->id == $absentGames[$i]->id) {
                                if ($game->round_id < $round) {
                                    $white_score += Config::Scoring("Other") * $white_ranking->lastvalue;
                                } else {
                                    $white_score += Config::Scoring("Other") * $white_ranking->Value;
                                }
                            } else {
                            }
                        }
                    } else {
                        if ($game->round_id < $round) {
                            $white_score += Config::Scoring("Other") * $white_ranking->lastvalue;
                        } elseif ($game->round_id > $round) {
                        } else {
                            $white_score += Config::Scoring("Other") * $white_ranking->value;
                        }
                    }
                }

                $white_ranking->score = $white_score;
                Log::info('Updated score for user: '.$game->white.' to: '.$white_score);
                $white_ranking->save();
            } // Result is not Afwezigheid.
            else {
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

                // Check if player exist in Ranking, if not, add person to ranking.
                if ($white_ranking == NULL) {
                    $white_ranking = new Ranking;
                    $white_ranking->user_id = $game->white;
                    $white_ranking->score = 0;
                    $lowest_value = Ranking::select('value')->orderBy('value', 'asc')->limit(1)->first();
                    $white_ranking->value = $lowest_value->value - 1;
                    $white_ranking->firstvalue = $white_ranking->value;
                    $white_ranking->save();
                    Log::info('Created a new ranking for user: '.$white_ranking->user_id.' with value: '.$white_ranking->value);

                    // Set the new created Ranking as white_ranking again.
                    $white_ranking = Ranking::where('user_id', $game->white)->first();
                }
                $white_rating = User::where('id', $game->white)->first();
                $white_absence = User::where('id', $game->white)->first();
                // Defaults; //69.05
                if ($white_ranking->score == 0) {

                    if($white_absence->beschikbaar == 0){
                        $white_score = $white_ranking->firstvalue;
                    }else{
                    $white_score = $white_ranking->value;} // 39
                } else {
                    $white_score = $white_ranking->score;
                }
                if ($game->black == "Bye") {
                } else {
                    $black_absence = User::where('id', $game->black)->first();
                    $black_ranking = Ranking::where('user_id', $game->black)->first();

                    // Check if player exist in Ranking, if not, add person to ranking.
                    if ($black_ranking == NULL) {
                        $black_ranking = new Ranking;
                        $black_ranking->user_id = $game->black;
                        $black_ranking->score = 0;
                        $lowest_value = Ranking::select('value')->orderBy('value', 'asc')->limit(1)->first();
                        $black_ranking->value = $lowest_value->value - 1;
                        $black_ranking->firstvalue = $black_ranking->value;
                        $black_ranking->save();
                        Log::info('Created a new ranking for user: '.$black_ranking->user_id.' with value: '.$black_ranking->value);

                        // Set the new created Ranking as white_ranking again.
                        $black_ranking = Ranking::where('user_id', $game->black)->first();
                    }
                    $black_rating = User::where('id', $game->black)->first();
                    if ($black_ranking->score == 0) {
                        if($black_absence->beschikbaar == 0){
                            $black_score = $black_ranking->firstvalue;
                        }else{
                        $black_score = $black_ranking->value;
                        }
                    } else {
                        $black_score = $black_ranking->score;
                    }
                }

                Log::info('Processing game: '.$game->white.' vs '.$game->black.' in round: '.$game->round_id.' with result: '.$game->result);

                // Calculate the new score for white and black for this game or all games?
                if ($game->black == "Bye") {
                    if ($game->round_id < $round) {
                        $white_score += Config::Scoring("Bye") * $white_ranking->lastvalue;
                    } elseif ($game->round_id > $round) {
                    } else {
                        $white_score += Config::Scoring("Bye") * $white_ranking->value;
                    }
                } elseif ($white_result == "1") {
                    if ($game->round_id < $round) {

                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {
                            $white_score += 1 * $black_ranking->firstvalue;
                        } else {
                            $white_score += 1 * $black_ranking->lastvalue;
                        }
                        //
                    } elseif ($game->round_id > $round) {
                    } else {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {    $white_score += 1 * $black_ranking->firstvalue;
                        } else {
                            $white_score += 1 * $black_ranking->value;
                        } //58+60 = 118.05 + 59 = 178.1 + 28.5 = 205.65
                    }
                    $white_ranking->amount = $white_ranking->amount + 1;
                    $black_ranking->amount = $black_ranking->amount + 1;
                    $white_ranking->gamescore = $white_ranking->gamescore + 1;
                    Log::info('Updated gamescore for user: '.$game->white.' to: '.$white_ranking->gamescore);

                    $black_score += Config::Scoring("Presence");
                } elseif ($white_result == "1R") {

                    if ($game->round_id < $round) {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {
                            $white_score += 1 * $black_ranking->firstvalue;
                        } else {
                            $white_score += 1 * $black_ranking->lastvalue;
                        }  //
                    } elseif ($game->round_id > $round) {
                    } else {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {
                            $white_score += 1 * $black_ranking->firstvalue;
                        } else {
                            $white_score += 1 * $black_ranking->value;
                        } //58+60 = 118.05 + 59 = 178.1 + 28.5 = 205.65
                    }
                    $white_ranking->amount = $white_ranking->amount + 1;

                    $white_ranking->gamescore = $white_ranking->gamescore + 1;
                    Log::info('Updated gamescore for user: '.$game->white.' to: '.$white_ranking->gamescore);
                } elseif ($white_result == 0.5) {   //69.05 += 0.5 * 69 = 69.05 + 34.5 = 103.60
                    if ($game->round_id < $round) {
                            if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {
                            $white_score += $white_result * $black_ranking->firstvalue;
                        } else {
                            $white_score += $white_result * $black_ranking->lastvalue;
                        }
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {
                            $black_score += $black_result * $white_ranking->firstvalue;
                        }
                        else{
                        $black_score += $black_result * $white_ranking->lastvalue;
                        }
                    } elseif ($game->round_id > $round) {
                    } else {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {
                            $white_score += $white_result * $black_ranking->firstvalue;
                        } else {
                            $white_score += $white_result * $black_ranking->value;
                        }
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                            $black_score += $black_result * $white_ranking->firstvalue;
                       }else{
                        $black_score += $black_result * $white_ranking->value;
                       }
                    }
                    $white_ranking->amount = $white_ranking->amount + 1;
                    $black_ranking->amount = $black_ranking->amount + 1;
                    $white_ranking->gamescore = $white_ranking->gamescore + 0.5;
                    $black_ranking->gamescore = $black_ranking->gamescore + 0.5;
                    Log::info('Updated gamescore for user: '.$game->white.' to: '.$white_ranking->gamescore);
                    Log::info('Updated gamescore for user: '.$game->black.' to: '.$black_ranking->gamescore);
                } elseif ($black_result == "1") {
                    if ($game->round_id < $round) {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                            $black_score += 1 * $white_ranking->firstvalue;
                        } else {
                            $black_score += 1 * $white_ranking->lastvalue;
                        }
                    } elseif ($game->round_id > $round) {
                    } else {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                            $black_score += 1 * $white_ranking->firstvalue;
                        } else {
                            $black_score += 1 * $white_ranking->value;
                        }
                    }
                    $white_ranking->amount = $white_ranking->amount + 1;
                    $black_ranking->amount = $black_ranking->amount + 1;
                    $black_ranking->gamescore = $black_ranking->gamescore + 1;
                    Log::info('Updated gamescore for user: '.$game->black.' to: '.$black_ranking->gamescore);

                    $white_score += Config::Scoring("Presence");
                } elseif ($black_result == "1R") {
                    if ($game->round_id < $round) {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                            $black_score += 1 * $white_ranking->firstvalue;
                        } else {
                            $black_score += 1 * $white_ranking->lastvalue;
                        }
                    } elseif ($game->round_id > $round) {
                    } else {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                            $black_score += 1 * $white_ranking->firstvalue;
                        } else {
                            $black_score += 1 * $white_ranking->value;
                        }
                    }

                    $black_ranking->amount = $black_ranking->amount + 1;
                    $black_ranking->gamescore = $black_ranking->gamescore + 1;
                    Log::info('Updated gamescore for user: '.$game->black.' to: '.$black_ranking->gamescore);

                } else // No result yet?
                {
                    continue;
                }
                Log::info('Updated score for user: '.$game->white.' to: '.$white_score);

                $white_ranking->score = $white_score;
                $white_ranking->save();
                if ($game->black == 0) {
                } else {

                    if ($black_result == "1R" or $game->black == "Bye") { // White didn't play
                    } else {
                        if ($black_rating->rating == 0) {
                            $white_ranking->ratop = $white_ranking->ratop + 1000;
                        } else {
                            $white_ranking->ratop = $white_ranking->ratop + $black_rating->rating;
                        }

                        $white_ranking->save();
                    }
                    $white_ranking->tpr = $this->calculateTPR($game->white);
                    $white_ranking->save();
                    if ($black_result == "1R" or $game->black == "Bye") { // White didn't play
                    } else {
                        $black_ranking->score = $black_score;
                        if ($white_result == "1R") {
                        } else {
                            if ($white_rating->rating  == 0) {
                                $black_ranking->ratop = $black_ranking->ratop + 1000;
                            } else {
                                $black_ranking->ratop = $black_ranking->ratop + $white_rating->rating;
                            }
                            $black_ranking->save();
                        }
                        $black_ranking->tpr = $this->calculateTPR($game->black);
                        $black_ranking->save();
                    }
                }

            }
        }
        $round_processed = Round::find($round);
        $round_processed->processed = 1;
        $round_processed->save();
        Log::info('Finished calculation for round: '.$round);
        return $this->UpdateRanking();
    }


    // tpr
    public function calculateTPR($player)
    {
        $user = Ranking::where('user_id', $player)->first();
        if ($user->amount == 0) {
            return 0;
        }

        $divide = $user->gamescore / $user->amount;
        $average_rating = $user->ratop / $user->amount;
        $based_on_divide = $this->GetValueForTPR($divide);
        $tpr = $average_rating + $based_on_divide;
        return $tpr;
    }

    public function GetValueForTPR($amount)
    {
        $amount = round($amount, 2);
        $value = TPRHelper::where('p', $amount)->first();
        if ($value == null) {
            return 0;
        }
        return $value->dp;
    }

    // Update the ranking as now the scores are processed.
    public function UpdateRanking(): void
    {
        $Ranking = Ranking::orderBy('score', 'desc')->get();
        Log::info('Updating ranking values.');
        $i = Config::InitRanking("start");
        foreach ($Ranking as $rank) {
            $rank->lastvalue2 = $rank->lastvalue;
            $rank->lastvalue = $rank->value;
            $rank->value = $i;
            $rank->save();
            Log::info('Updated ranking value for user: '.$rank->user_id.' to: '.$rank->value);
            $i = $i - Config::InitRanking("step");
        }
    }
}
