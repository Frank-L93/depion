<?php

namespace App\Actions;

use App\Models\Config;
use App\Models\Game;
use App\Models\Ranking;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MatchGames
{
    public function InitPairing($players, $round): bool
    {

        $playerstopair = $players;
        $players_to_pair_with_rating = [];
        $init = 0;

        // Check for player being an outliner in the bottom of the group
        foreach ($players as $player) {

            $player_rating = User::where('id', $player['id'])->first();
            array_push($players_to_pair_with_rating, ['player' => $player_rating->id, 'rating' => $player_rating->rating]);
        }

        $last_player_to_pair = end($players_to_pair_with_rating);
        Log::info('Last Player to pair: '.$last_player_to_pair['player']);
        $count_players_rated = count($players_to_pair_with_rating);
        Log::info('Count Player to pair: '.$count_players_rated);
        if ($count_players_rated < 2) {
            $count_players_rated = 2;
        }
        $non_last_player_to_pair = $players_to_pair_with_rating[$count_players_rated - 2];

        Log::info('Non Player to pair: '.$non_last_player_to_pair['player']);
        Log::info('Checking if it is ok to pair this way');
        if ($this->CheckIfOkToPairThisWay($non_last_player_to_pair['rating'], $last_player_to_pair['rating']) == true) {
            Log::info('CheckIfOk: true');
            Log::info('Moving player:'.$players[$count_players_rated - 1]['id']);
            $playerstopair = $this->moveElement($playerstopair, $count_players_rated - 1, $count_players_rated - rand(2, $count_players_rated - 1));
        }

        // Check even or odd amount of players
        Log::info('Checking if bye is necessary');
        if (count($playerstopair) % 2 == 0) {
            $bye_necessary = 0;
        } else {
            $bye_necessary = 1;
        }

        return $this->MatchGame($playerstopair, $round, $bye_necessary);
    }

    public function CheckIfOkToPairThisWay($b, $a)
    {
        $c = 0;
        Log::info('Checking if it is ok to pair this way with rating: '.$a.' and '.$b);
        $math = rand(0, 6);
        if ($a - $b > 500) {
            $c = ($a / 100 - $b / 100);
        }

        if ($math + $c > 10) {
            Log::info('CheckIfOk: false');

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
        $matches = [];
        $matched = [];
        Log::info('Starting to match players');

        if ($bye_necessary == 1) {
            Bye:
            $bye = rand(0, count($playerstopair));
            if ($bye == count($playerstopair)) {
                $bye -= 1;
            }
            if (count($playerstopair) == 1) {
                $bye = 0;
            }
            Log::info('Checking if bye is valid for player: '.$playerstopair[$bye]['id']);
            if ($this->validBye($playerstopair[$bye], $round) == true) {
                $this->createGame($playerstopair[$bye]['id'], 'Bye', $round);
                array_push($matched, $playerstopair[$bye]['id']);
            } else {
                Log::info('Bye is not valid for player: '.$playerstopair[$bye]['id']);
                goto Bye;
            }
        }

        foreach ($playerstopair as $playertopair) {
            $player_being_paired = $playertopair['id'];
            if (in_array($player_being_paired, $matched) == false) {
                // Player is not yet paired, so find an available opponent:
                // Preferred opponent is next in $playerstopair, or the most closest one that is a valid opponent.
                // Valid opponent is a player that is not yet paired and a player who has not played against our player being paired in the last X rounds.
                Log::info('Player: '.$player_being_paired.' is not yet paired, finding an opponent');
                // Also the color should be checked (if both are on -2 or +2 they can't play against eachother, if one is on -2 or + 2, and the other is on -1 or +1, it is possible, giving the player with the -2 / +2 the correct color)
                foreach ($playerstopair as $opponent) {
                    $opponent_being_paired = $opponent['id'];
                    if ($opponent == $playertopair || (in_array($opponent_being_paired, $matched) == true)) {
                        // Just skip this one as the opponent is the player being paired or the opponent being paired is already paired.
                        Log::info('Skipping opponent: '.$opponent_being_paired.' as it is the player being paired or the opponent being paired is already paired');
                    } else {
                        // Opponent is not paired yet, and opponent is not or player being paired
                        // Check if Opponent is valid for this Player.
                        Log::info('Checking if opponent: '.$opponent_being_paired.' is valid for player: '.$player_being_paired);
                        if ($this->validOpponent($player_being_paired, $opponent_being_paired, $round, count($matched), count($playerstopair)) == true) {
                            // if Valid, create Game.
                            Log::info('Opponent: '.$opponent_being_paired.' is valid for player: '.$player_being_paired);
                            if ($this->createGame($player_being_paired, $opponent_being_paired, $round) == true) {
                                array_push($matched, $opponent_being_paired); // Add opponent to Matched Players
                                array_push($matched, $player_being_paired); // Add Player to Matched Players
                                break;
                            } else {
                                Log::error('Error creating Game for:'.$player_being_paired.'vs'.$opponent_being_paired);

                                return false;
                            }
                        }
                    }
                }
            }
        }
        if (count($matched) == count($playerstopair)) {
            Log::info('All players are matched');
            // Create notification

            return true;
        } else {
            Log::error('Error, players not matched correctly for round:'.$round);

            return false;
        }
    }

    public function validBye($player_one, $round)
    {
        Log::info('Checking if bye is valid for player: '.$player_one['id'].' in round: '.$round);
        $round_minimum = $round - Config::RoundsBetween(1);
        if ($round_minimum < 0) {
            $round_minimum = 1;
        }
        Log::info('Checking if player: '.$player_one['id'].' has had a bye in the last '.Config::RoundsBetween(1).' rounds');
        $game_exist = Game::where([['white', '=', $player_one], ['black', '=', 'Bye']])->whereBetween('round_id', [$round_minimum, $round])->get();
        if (($game_exist->isNotEmpty())) {
            return false;
        }

        // If valid return True;
        return true;
    }

    public function validOpponent($player_one, $player_two, $round, $amount_matched, $amount_to_match)
    {
        Log::info('We are trying to pair: '.$player_one.' against '.$player_two.' with the following settings: '.$amount_matched.'&'.$amount_to_match);
        if ($amount_to_match - $amount_matched < 3) {
            return true;
        }
        // Not valid when:
        // Both players have -2 or +2
        $color_value = Ranking::where('user_id', $player_one)->first();
        $color = $color_value->color;
        Log::info('Color value for player: '.$player_one.' is: '.$color);

        $color_value_black = Ranking::where('user_id', $player_two)->first();
        $color_black = $color_value_black->color;
        Log::info('Color value for player: '.$player_two.' is: '.$color_black);
        if (($color == '-2' && $color_black == '-2') || ($color == '2' && $color_black == '2')) {
            return false; // Both players have -2 or +2
        }
        // Not valid when:
        // Players have played against eachother in last X rounds.
        $round_minimum = $round - Config::RoundsBetween(2);

        if ($round_minimum < 0) {
            $round_minimum = 1;
        }
        Log::info('Checking if players: '.$player_one.' and '.$player_two.' have played against eachother in the last '.Config::RoundsBetween(2).' rounds');
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
        Log::info('Checking if players: '.$player_one.' and '.$player_two.' have played against eachother in the current season part');
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
        Log::info('Creating game for: '.$player_one.' against '.$player_two.' in round: '.$round);
        // Check color preference.
        // if Player_One has -2 he needs white
        // if Player_One has +2 he needs black
        // if equal, random
        // if Player_Two has -2 he needs white
        // if Player_Two has +2 he needs black
        // if -1 +1, opposite colors
        Log::info('Checking color preference for: '.$player_one.' against '.$player_two);
        if ($player_two == 'Bye') {
            $game = new Game;
            $game->white = $player_one;
            $game->black = 'Bye';
            $game->result = '1-0';
            $game->round_id = $round;
            $game->save();

            return true;
        }
        $color_value = Ranking::where('user_id', $player_one)->first();
        $color = $color_value->color;
        Log::info('We are trying to pair to: '.$player_one.' against '.$player_two);

        $color_value_black = Ranking::where('user_id', $player_two)->first();
        $color_black = $color_value_black->color;
        if ($color == '-2') {
            $white = $player_one;
            $black = $player_two;
        } elseif ($color_black == '-2') {
            $white = $player_two;
            $black = $player_one;
        } elseif ($color == '2') {
            $white = $player_two;
            $black = $player_one;
        } elseif ($color_black == '2') {
            $white = $player_one;
            $black = $player_two;
        } elseif ($color == '-1' && $color_black == '1') {
            $white = $player_one;
            $black = $player_two;
        } elseif ($color == '1' && $color_black == '-1') {
            $white = $player_two;
            $black = $player_one;
        } else {
            $color_random = rand(0, 1);
            if ($color_random == 0) {
                $white = $player_one;
                $black = $player_two;
            } else {
                $white = $player_two;
                $black = $player_one;
            }
        }
        Log::info('Without check: Color preference: '.$white.' is white and '.$black.' is black');
        $extraColorPreference = $this->checkColors($white, $black);

        Log::info('Color preference: '.$extraColorPreference['white'].' is white and '.$extraColorPreference['black'].' is black');

        $game = new Game;
        $game->white = $extraColorPreference['white'];
        $game->black = $extraColorPreference['black'];
        $game->result = '0-0';
        $game->round_id = $round;
        $game->save();

        // Update ranking of player
        Log::info('Updating ranking of player: '.$white);
        $white_ranking = Ranking::where('user_id', $white)->first();
        $white_ranking->color += 1;
        $white_ranking->save();
        Log::info('Updating ranking of player: '.$black);
        $black_ranking = Ranking::where('user_id', $black)->first();
        $black_ranking->color -= 1;
        $black_ranking->save();
        Log::info('Game created for: '.$white.' against '.$black.' in round: '.$round);

        return true;
    }

    private function checkColors($white, $black): array
    {
        $countWhiteGamesForWhite = Game::where('white', $white)->where('result', '<>', 'Afwezigheid')->count();
        $countBlackGamesForWhite = Game::where('black', $white)->count();

        // Calculate the color balance for the white player
        $colorBalanceWhite = $countWhiteGamesForWhite - $countBlackGamesForWhite;

        // Retrieve the game counts for the black player
        $countWhiteGamesForBlack = Game::where('white', $black)->where('result', '<>', 'Afwezigheid')->count();
        $countBlackGamesForBlack = Game::where('black', $black)->count();

        $w = (int) $countWhiteGamesForBlack;
        $b = (int) $countBlackGamesForBlack;

        // Controleer de gecaste waarden en types

        // Calculate the color balance for the black player
        $colorBalanceBlack = $w - $b;

        Log::info("Color balance for white player (ID: $white): $colorBalanceWhite");
        Log::info("Color balance for black player (ID: $black): $colorBalanceBlack");

        $whiteHasImbalance = false;
        $blackHasImbalance = false;

        if ($colorBalanceWhite >= 2) {
            Log::info('White player has played too many games as white. Marking for adjustment...');
            $whiteHasImbalance = true;
        } elseif ($colorBalanceWhite <= -2) {
            Log::info('White player has played too many games as black. Marking for adjustment...');
            $blackHasImbalance = true; // White has been black too often, so black will take white
        }

        if ($colorBalanceBlack >= 2) {
            Log::info('Black player has played too many games as white. Marking for adjustment...');
            $blackHasImbalance = true;
        } elseif ($colorBalanceBlack <= -2) {
            Log::info('Black player has played too many games as black. Marking for adjustment...');
            $whiteHasImbalance = true; // Black has been black too often, so white will take black
        }

        // Handle cases where both players have imbalances
        if ($whiteHasImbalance && ! $blackHasImbalance) {
            return ['white' => $black, 'black' => $white];
        } elseif ($blackHasImbalance && ! $whiteHasImbalance) {
            return ['white' => $white, 'black' => $black];
        } elseif ($whiteHasImbalance && $blackHasImbalance) {
            Log::info('Both players have imbalances. Randomizing colors...');
            $color_random = rand(0, 1);
            if ($color_random == 0) {
                return ['white' => $white, 'black' => $black];
            } else {
                return ['white' => $black, 'black' => $white];
            }
        }

        // Additional logic to account for recent games
        $recentGamesWhite = Game::where('white', $white)->orWhere('black', $white)->latest()->take(2)->get();
        $recentGamesBlack = Game::where('white', $black)->orWhere('black', $black)->latest()->take(2)->get();

        $recentWhiteAsWhite = $recentGamesWhite->where('white', $white)->count();
        $recentWhiteAsBlack = $recentGamesWhite->where('black', $white)->count();
        $recentBlackAsWhite = $recentGamesBlack->where('white', $black)->count();
        $recentBlackAsBlack = $recentGamesBlack->where('black', $black)->count();

        Log::info("Recent games analysis for white player: $recentWhiteAsWhite as white, $recentWhiteAsBlack as black");
        Log::info("Recent games analysis for black player: $recentBlackAsWhite as white, $recentBlackAsBlack as black");

        // If recent color usage is unbalanced, adjust accordingly
        if ($recentWhiteAsWhite - $recentWhiteAsBlack >= 2) {
            Log::info('White player recently played too many games as white. Adjusting colors...');

            return ['white' => $black, 'black' => $white];
        } elseif ($recentWhiteAsBlack - $recentWhiteAsWhite >= 2) {
            Log::info('White player recently played too many games as black. Adjusting colors...');

            return ['white' => $white, 'black' => $black];
        }

        if ($recentBlackAsWhite - $recentBlackAsBlack >= 2) {
            Log::info('Black player recently played too many games as white. Adjusting colors...');

            return ['white' => $white, 'black' => $black];
        } elseif ($recentBlackAsBlack - $recentBlackAsWhite >= 2) {
            Log::info('Black player recently played too many games as black. Adjusting colors...');

            return ['white' => $black, 'black' => $white];
        }

        return ['white' => $white, 'black' => $black];
    }
}
