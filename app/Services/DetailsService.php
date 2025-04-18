<?php

namespace App\Services;

use App\Models\Config;
use App\Models\Game;
use App\Models\Ranking;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Str;

class DetailsService
{
    public function Games($player)
    {
        $currentRound = Round::where('processed', '=', null)->orderBy('date')->first();
        if ($currentRound == null) {
            $currentRound = Round::where('processed', '=', 1)->latest('updated_at')->first();
        }

        $white_Games = Game::where('white', '=', $player)->where('round_id', '<=', $currentRound->round)->get();
        $black_Games = Game::where('black', '=', $player)->where('round_id', '<=', $currentRound->round)->get();

        $white_Games->map(function ($item) {
            $item->score = $this->CurrentScore($item->white, $item->round_id, $item->id);

            return $item;
        });
        $white_Games->map(function ($item) {
            $item->white = $this->PlayerName($item->white);
            $item->black = $this->PlayerName($item->black);

            return $item;
        });

        $black_Games->map(function ($item) {
            $item->score = $this->CurrentScore($item->black, $item->round_id, $item->id);

            return $item;
        });

        $black_Games->map(function ($item) {
            $item->white = $this->PlayerName($item->white);
            $item->black = $this->PlayerName($item->black);

            return $item;
        });

        $Games = $white_Games->merge($black_Games);
        $Games = $Games->sortByDesc('round_id');

        return $Games;
    }

    public function PlayerName($player)
    {
        if ($player == 'Bye') {
            return 'Bye';
        } elseif ($player == 'Other' || $player == 'Club' || $player == 'Personal') {
            return '-';
        } else {
            $player = intval($player);
            $PlayerName = User::select('name')->where('id', '=', $player)->first();

            return $PlayerName->name;
        }
    }

    public function LastRound()
    {

        $lastRound = Round::where('processed', '=', 1)->latest('updated_at')->first();
        if ($lastRound == null) {
            $round = 1;
        } else {
            $round = $lastRound->round;
        }

        return $round;
    }

    public static function CurrentRound()
    {

        $currentRound = Round::where('ranking', '=', 1)->orderBy('updated_at', 'DESC')->first();
        if ($currentRound == null) {
            return 'Niet';
        }

        $round = $currentRound->round;

        return $round;
    }

    public function CurrentScore($player, $selectedRound, $gameID)
    {

        // Get the Game;
        $game = Game::find($gameID);
        if ($game->round_id > $selectedRound) {
            return 'Niet gespeeld';
        }
        // Get the current Round to determine if round game round is earlier.
        $round = $this->LastRound();

        // Set score to 0;
        $score = 0; // Presence

        // Check for Absence Game;

        if ($game->white == $player && $game->result == 'Afwezigheid') {
            if ($player == 4) {
                dd($selectedRound, $gameID);
            }
            // Set White Score to 0
            $white_score = 0;
            // Get the ranking for the values.
            $white_ranking = Ranking::where('user_id', $game->white)->first();

            // Club
            if ($game->black == 'Club') {
                if ($game->round_id < $round) {
                    $white_score = Config::Scoring('Club') * $white_ranking->lastvalue2;
                } else {
                    $white_score = Config::Scoring('Club') * $white_ranking->lastvalue;
                }
            } elseif ($game->black == 'Personal') {
                if ($game->round_id < $round) {
                    $white_score = Config::Scoring('Personal') * $white_ranking->lastvalue2;
                } else {
                    $white_score = Config::Scoring('Personal') * $white_ranking->lastvalue;
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
                                $white_score = Config::Scoring('Other') * $white_ranking->lastvalue2;
                            } else {
                                $white_score = Config::Scoring('Other') * $white_ranking->lastvalue;
                            }
                        }
                    }
                } else {
                    if ($game->round_id < $round) {
                        $white_score = Config::Scoring('Other') * $white_ranking->lastvalue2;
                    } else {
                        $white_score = Config::Scoring('Other') * $white_ranking->lastvalue;
                    }
                }
            }
            $score += $white_score;

            return $score;
        } else {

            if (($game->white == $player && $game->result != 'Afwezigheid') || ($game->black == $player && $game->result != 'Afwezigheid')) {

                if (Str::contains($game->result, 'R')) {
                    $result = explode('-', $game->result);
                    $white_result = $result[0];
                    if ($white_result == '1') {
                        $white_result = '1R';
                    }
                    $black_result = $result[1];
                } else {
                    $result = explode('-', $game->result);
                    $white_result = $result[0];
                    $black_result = $result[1];
                }

                // Find white and black in the ranking
                $white_ranking = Ranking::where('user_id', $game->white)->first();
                if ($game->black != 'Bye') {
                    $black_ranking = Ranking::where('user_id', $game->black)->first();
                }

                $white_absence = User::where('id', $game->white)->first();
                $black_absence = User::where('id', $game->black)->first();

                // Defaults; //69.05
                $white_score = 0;
                if ($game->black != 'Bye') {
                    $black_score = 0;
                }
                // Calculate the new score for white and black for this game or all games?
                if ($game->black == 'Bye') {
                    if ($game->round_id < $round) {
                        $white_score = Config::Scoring('Bye') * $white_ranking->lastvalue2;
                    } else {
                        $white_score = Config::Scoring('Bye') * $white_ranking->lastvalue;
                    }
                } elseif ($white_result == '1') {

                    if ($game->round_id < $round) {

                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5 || $game->round_id < 6)) {
                            $white_score += 1 * $black_ranking->firstvalue;
                        } else {
                            $white_score += 1 * $black_ranking->lastvalue2;
                        }
                        //

                    } else {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {
                            $white_score += 1 * $black_ranking->firstvalue;
                        } else {
                            $white_score += 1 * $black_ranking->lastvalue;
                        } // 58+60 = 118.05 + 59 = 178.1 + 28.5 = 205.65
                    }

                    $black_score += Config::Scoring('Presence');
                } elseif ($white_result == '1R') {
                    if ($game->round_id < $round) {

                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5 || $game->round_id < 6)) {
                            $white_score += 1 * $black_ranking->firstvalue;
                        } else {
                            $white_score += 1 * $black_ranking->lastvalue2;
                        }
                        //

                    } else {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {
                            $white_score += 1 * $black_ranking->firstvalue;
                        } else {
                            $white_score += 1 * $black_ranking->lastvalue;
                        } // 58+60 = 118.05 + 59 = 178.1 + 28.5 = 205.65
                    }

                } elseif ($white_result == 0.5) {   // 69.05 += 0.5 * 69 = 69.05 + 34.5 = 103.60

                    if ($game->round_id < $round) {

                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5 || $game->round_id < 6)) {
                            $white_score += $white_result * $black_ranking->firstvalue;
                        } else {
                            $white_score += $white_result * $black_ranking->lastvalue2;
                        }
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {
                            $black_score += $black_result * $white_ranking->firstvalue;
                        } else {
                            $black_score += $black_result * $white_ranking->lastvalue2;
                        }
                    } else {
                        if ($black_absence->beschikbaar == 0 && ($black_ranking->amount == 0 || $black_ranking->amount < 5)) {
                            $white_score += $white_result * $black_ranking->firstvalue;
                        } else {
                            $white_score += $white_result * $black_ranking->lastvalue;
                        }
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                            $black_score += $black_result * $white_ranking->firstvalue;
                        } else {
                            $black_score += $black_result * $white_ranking->lastvalue;
                        }
                    }

                } elseif ($black_result == '1') {

                    if ($game->round_id < $round) {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {

                            $black_score += 1 * $white_ranking->firstvalue;
                        } else {
                            $black_score += 1 * $white_ranking->lastvalue2;
                        }
                    } else {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {

                            $black_score += 1 * $white_ranking->firstvalue;
                        } else {
                            $black_score += 1 * $white_ranking->lastvalue;
                        }
                    }
                    $white_score += 5;
                } elseif ($black_result == '1R') {

                    if ($game->round_id < $round) {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5 || $game->round_id < 6)) {

                            $black_score += 1 * $white_ranking->firstvalue;
                        } else {
                            $black_score += 1 * $white_ranking->lastvalue2;
                        }
                    } elseif ($game->round_id <= $round) {
                        if ($white_absence->beschikbaar == 0 && ($white_ranking->amount == 0 || $white_ranking->amount < 5)) {

                            $black_score += 1 * $white_ranking->firstvalue;
                        } else {
                            $black_score += 1 * $white_ranking->lastvalue;
                        }
                    }
                } else { // No result yet?
                    return 'Fout bij berekenen, nog niet gespeeld';
                }

                if ($game->white == $player) {
                    $score += $white_score;

                    return $score;
                } elseif ($game->black == $player) {
                    $score += $black_score;

                    return $score;
                }
            }

        }
        return $score;
    }
}
